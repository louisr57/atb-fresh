#!/usr/bin/env python3
"""Off-droplet rollback checker for the ATB activity journal.

Runs on a SEPARATE host (not the production droplet). It only reads:
  * the append-only journal in Backblaze B2 (through rclone, list/read only)
  * GET /monitoring/state on the production app (bearer token)

It compares what the journal remembers with what the live database says and
reports to Healthchecks.io: a plain ping when everything is fine, "/fail" with
a message when something is wrong. Because it also pings on success, silence
(this host down, cron broken) is noticed too.

Quick run (every 15 min): newest manifest vs the live database.
Full run (--full, daily): additionally downloads every data file and verifies
checksums, the hash chain and the id continuity of the whole journal.

Standard library only (Python 3.8+). Never prints the token.
"""

import argparse
import gzip
import hashlib
import json
import os
import re
import subprocess
import sys
import tempfile
import time
import urllib.error
import urllib.request
from datetime import datetime, timezone

GENESIS = '0' * 64
MANIFEST_RE = re.compile(r'^activity_(\d{10})-(\d{10})\.manifest\.json$')
DATA_RE = re.compile(r'^activity_(\d{10})-(\d{10})\.jsonl\.gz$')
REQUIRED_KEYS = (
    'first_id', 'last_id', 'rows', 'content_sha256', 'last_row_sha256',
    'previous_chain_hash', 'chain_hash', 'exported_at', 'data_file',
)


class CheckerError(Exception):
    """The check itself could not be completed (network, rclone, bad data)."""


# --------------------------------------------------------------------------
# Journal sources
# --------------------------------------------------------------------------

class RcloneSource:
    """Read-only access to the journal through rclone (lsf / cat only)."""

    def __init__(self, binary, remote):
        self.binary = binary
        self.remote = remote if remote.endswith(('/', ':')) else remote + '/'

    def _run(self, *args):
        try:
            done = subprocess.run(
                [self.binary, *args], capture_output=True, timeout=180, check=False
            )
        except (OSError, subprocess.TimeoutExpired) as exc:
            raise CheckerError('rclone could not be run: %s' % exc)
        if done.returncode != 0:
            raise CheckerError('rclone %s failed: %s' % (args[0], done.stderr.decode('utf-8', 'replace').strip()[:300]))
        return done.stdout

    def names(self):
        return self._run('lsf', '--files-only', self.remote).decode('utf-8').split()

    def read(self, name):
        return self._run('cat', self.remote + name)


class LocalDirSource:
    """A plain directory (development and tests)."""

    def __init__(self, directory):
        self.directory = directory

    def names(self):
        return sorted(os.listdir(self.directory))

    def read(self, name):
        with open(os.path.join(self.directory, name), 'rb') as handle:
            return handle.read()


# --------------------------------------------------------------------------
# Pure checks
# --------------------------------------------------------------------------

def sha256_hex(data):
    return hashlib.sha256(data).hexdigest()


def parse_manifest(raw, name):
    try:
        data = json.loads(raw)
    except ValueError:
        raise CheckerError('manifest %s is not valid JSON' % name)
    if not isinstance(data, dict) or any(key not in data for key in REQUIRED_KEYS):
        raise CheckerError('manifest %s is incomplete' % name)
    try:
        data['first_id'] = int(data['first_id'])
        data['last_id'] = int(data['last_id'])
        data['rows'] = int(data['rows'])
    except (TypeError, ValueError):
        raise CheckerError('manifest %s has non-numeric ids' % name)
    data['_name'] = name
    return data


def manifest_names(names):
    """Manifest file names sorted by id range (oldest first)."""
    found = []
    for name in names:
        match = MANIFEST_RE.match(name)
        if match:
            found.append((int(match.group(1)), int(match.group(2)), name))
    found.sort()
    return [name for _first, _last, name in found]


def verify_chain(manifests):
    """Ids continuous and every previous_chain_hash equal to the one before."""
    problems = []
    expected_prev = GENESIS
    expected_first = None

    for manifest in manifests:
        name = manifest['_name']
        match = MANIFEST_RE.match(name)
        if match and (int(match.group(1)) != manifest['first_id'] or int(match.group(2)) != manifest['last_id']):
            problems.append('ALERT: manifest %s contents do not match its file name.' % name)
        if manifest['previous_chain_hash'] != expected_prev:
            problems.append('ALERT: hash chain broken at %s (a journal file before it was removed or altered).' % name)
        if expected_first is not None and manifest['first_id'] != expected_first:
            problems.append('ALERT: journal ids are not continuous at %s (expected to start at #%d, starts at #%d).' % (name, expected_first, manifest['first_id']))
        expected_prev = manifest['chain_hash']
        expected_first = manifest['last_id'] + 1

    return problems


def verify_data(manifest, compressed):
    """Check a downloaded data file against its manifest."""
    name = manifest['data_file']
    if not DATA_RE.match(name):
        return ['ALERT: manifest %s names an unexpected data file.' % manifest['_name']]

    try:
        content = gzip.decompress(compressed)
    except (OSError, EOFError):
        return ['ALERT: %s is not a valid gzip file.' % name]

    problems = []
    content_hash = sha256_hex(content)
    if content_hash != manifest['content_sha256']:
        problems.append('ALERT: %s content checksum differs from its manifest.' % name)

    expected_chain = sha256_hex((manifest['previous_chain_hash'] + content_hash).encode('ascii'))
    if expected_chain != manifest['chain_hash']:
        problems.append('ALERT: %s chain hash cannot be reproduced from its content.' % name)

    lines = content.rstrip(b'\n').split(b'\n')
    if len(lines) != manifest['rows']:
        problems.append('ALERT: %s holds %d rows but its manifest says %d.' % (name, len(lines), manifest['rows']))
    if sha256_hex(lines[-1]) != manifest['last_row_sha256']:
        problems.append('ALERT: %s last row hash differs from its manifest.' % name)

    try:
        first = json.loads(lines[0])['id']
        last = json.loads(lines[-1])['id']
        if int(first) != manifest['first_id'] or int(last) != manifest['last_id']:
            problems.append('ALERT: %s row ids do not match its manifest range.' % name)
    except (ValueError, KeyError, TypeError):
        problems.append('ALERT: %s contains an unreadable row.' % name)

    return problems


def parse_time(value):
    try:
        parsed = datetime.fromisoformat(value)
    except (TypeError, ValueError):
        return None
    if parsed.tzinfo is None:
        parsed = parsed.replace(tzinfo=timezone.utc)
    return parsed


def evaluate(manifest, state, saved, now, max_lag_hours):
    """Compare the newest journal manifest with the live state.

    Returns (problems, new_saved). `saved` holds the highest values seen on
    earlier runs; they may only ever go up.
    """
    problems = []
    last_id = manifest['last_id']

    activity = state.get('activity_log') or {}
    live_max = int(activity.get('max_id') or 0)
    requested = state.get('requested') or {}

    if live_max < last_id:
        problems.append('ALERT: live activity_log highest id is #%d but the journal already holds #%d. The database has gone BACK in time (restore or rollback).' % (live_max, last_id))

    if live_max < int(saved.get('max_live_id', 0)):
        problems.append('ALERT: live activity_log highest id is #%d but this checker saw #%d earlier. The database has gone back in time.' % (live_max, saved['max_live_id']))

    if last_id < int(saved.get('max_journal_id', 0)):
        problems.append('ALERT: the newest journal manifest ends at #%d but this checker saw #%d earlier. Recent journal files were removed.' % (last_id, saved['max_journal_id']))

    if requested.get('id') != last_id:
        problems.append('PROBLEM: the state endpoint did not answer the row check for #%d.' % last_id)
    elif not requested.get('exists'):
        problems.append('ALERT: activity_log row #%d is in the journal but missing from the live database.' % last_id)
    elif requested.get('row_sha256') != manifest['last_row_sha256']:
        problems.append('ALERT: live activity_log row #%d differs from the journal copy (restore with reused ids, or edited row).' % last_id)

    exported_at = parse_time(manifest['exported_at'])
    if live_max > last_id and exported_at is not None:
        age_hours = (now - exported_at).total_seconds() / 3600
        if age_hours > max_lag_hours:
            problems.append('PROBLEM: the live database has rows after #%d but the newest journal export is %.1f hours old (export stalled?).' % (last_id, age_hours))

    new_saved = {
        'max_live_id': max(int(saved.get('max_live_id', 0)), live_max),
        'max_journal_id': max(int(saved.get('max_journal_id', 0)), last_id),
        'updated_at': now.strftime('%Y-%m-%dT%H:%M:%SZ'),
    }
    return problems, new_saved


def verify_journal_full(source, names):
    manifests = [parse_manifest(source.read(name), name) for name in manifest_names(names)]
    problems = verify_chain(manifests)
    present = set(names)

    for manifest in manifests:
        if manifest['data_file'] not in present:
            problems.append('ALERT: data file %s is missing from the journal.' % manifest['data_file'])
            continue
        problems.extend(verify_data(manifest, source.read(manifest['data_file'])))

    return problems, len(manifests)


# --------------------------------------------------------------------------
# Side effects: state file, HTTP, ping
# --------------------------------------------------------------------------

def load_saved(path):
    if not os.path.exists(path):
        return {}
    try:
        with open(path, encoding='utf-8') as handle:
            data = json.load(handle)
        if not isinstance(data, dict):
            raise ValueError('not an object')
        return data
    except (OSError, ValueError) as exc:
        raise CheckerError('state file %s is unreadable (%s); fix it or run with --reset-state' % (path, exc))


def save_saved(path, data):
    directory = os.path.dirname(path) or '.'
    os.makedirs(directory, exist_ok=True)
    handle, temp = tempfile.mkstemp(dir=directory, prefix='.state-')
    try:
        with os.fdopen(handle, 'w', encoding='utf-8') as out:
            json.dump(data, out, indent=2)
            out.write('\n')
        os.replace(temp, path)
    except BaseException:
        if os.path.exists(temp):
            os.unlink(temp)
        raise


def fetch_state(url, token, row_id, attempts=3, pause=5):
    full = url + ('&' if '?' in url else '?') + 'id=%d' % row_id
    request = urllib.request.Request(full, headers={
        'Authorization': 'Bearer ' + token,
        'Accept': 'application/json',
        'User-Agent': 'atb-offsite-checker/1.0',
    })
    last_error = 'unknown error'

    for attempt in range(attempts):
        try:
            with urllib.request.urlopen(request, timeout=20) as response:
                return json.loads(response.read().decode('utf-8'))
        except urllib.error.HTTPError as exc:
            if exc.code in (401, 403):
                raise CheckerError('the state endpoint rejected the token (HTTP %d)' % exc.code)
            if exc.code == 404:
                raise CheckerError('the state endpoint is disabled or gone (HTTP 404)')
            last_error = 'HTTP %d' % exc.code
        except (urllib.error.URLError, OSError, ValueError) as exc:
            last_error = str(exc)[:200]
        if attempt < attempts - 1:
            time.sleep(pause)

    raise CheckerError('the state endpoint is unreachable (%s)' % last_error)


def ping(url, ok, message=''):
    if not url:
        return False
    target = url.rstrip('/') + ('' if ok else '/fail')
    request = urllib.request.Request(target, data=message.encode('utf-8')[:10000], method='POST')
    for _attempt in range(3):
        try:
            with urllib.request.urlopen(request, timeout=15):
                return True
        except (urllib.error.URLError, OSError):
            time.sleep(2)
    print('WARNING: could not reach the ping URL', file=sys.stderr)
    return False


# --------------------------------------------------------------------------
# Orchestration
# --------------------------------------------------------------------------

def run_check(source, state_fetcher, saved, now, max_lag_hours, full):
    """Returns (problems, notes, new_saved)."""
    names = source.names()
    manifests = manifest_names(names)
    if not manifests:
        raise CheckerError('the journal holds no manifests at all')

    newest = parse_manifest(source.read(manifests[-1]), manifests[-1])
    state = state_fetcher(newest['last_id'])
    problems, new_saved = evaluate(newest, state, saved, now, max_lag_hours)
    notes = ['journal ends at #%d, live highest id #%s' % (newest['last_id'], (state.get('activity_log') or {}).get('max_id'))]

    if full:
        extra, count = verify_journal_full(source, names)
        problems.extend(extra)
        notes.append('full verification of %d manifest(s)' % count)

    return problems, notes, new_saved


def load_env_file(path):
    with open(path, encoding='utf-8') as handle:
        for line in handle:
            line = line.strip()
            if not line or line.startswith('#') or '=' not in line:
                continue
            key, _, value = line.partition('=')
            os.environ.setdefault(key.strip(), value.strip().strip('"').strip("'"))


def main(argv=None):
    parser = argparse.ArgumentParser(description='Off-droplet rollback checker for the ATB activity journal.')
    parser.add_argument('--env-file', help='file with CHECKER_* settings (KEY=VALUE lines)')
    parser.add_argument('--full', action='store_true', help='also verify every journal file (checksums, hash chain, ids)')
    parser.add_argument('--no-ping', action='store_true', help='do not contact Healthchecks (for manual runs)')
    parser.add_argument('--reset-state', action='store_true', help='forget the saved high-water marks (after a deliberate restore)')
    args = parser.parse_args(argv)

    if args.env_file:
        load_env_file(args.env_file)

    ping_url = '' if args.no_ping else os.environ.get('CHECKER_PING_URL', '')
    state_path = os.path.expanduser(os.environ.get('CHECKER_STATE_FILE', '~/.local/state/offsite-checker/state.json'))
    now = datetime.now(timezone.utc)

    try:
        url = os.environ.get('CHECKER_STATE_URL', '')
        token = os.environ.get('CHECKER_STATE_TOKEN', '')
        journal_dir = os.environ.get('CHECKER_JOURNAL_DIR', '')
        remote = os.environ.get('CHECKER_RCLONE_REMOTE', '')

        if not url or not token or not (journal_dir or remote):
            raise CheckerError('CHECKER_STATE_URL, CHECKER_STATE_TOKEN and CHECKER_RCLONE_REMOTE (or CHECKER_JOURNAL_DIR) must be set')

        source = LocalDirSource(journal_dir) if journal_dir else RcloneSource(os.environ.get('CHECKER_RCLONE_BINARY', 'rclone'), remote)
        saved = {} if args.reset_state else load_saved(state_path)
        max_lag = float(os.environ.get('CHECKER_MAX_LAG_HOURS', '3'))

        problems, notes, new_saved = run_check(
            source, lambda row_id: fetch_state(url, token, row_id), saved, now, max_lag, args.full
        )
        save_saved(state_path, new_saved)
    except CheckerError as exc:
        problems, notes = ['PROBLEM: check could not be completed: %s' % exc], []
    except Exception as exc:  # never let an unexpected error look like success
        problems, notes = ['PROBLEM: unexpected checker error: %s: %s' % (type(exc).__name__, exc)], []

    stamp = now.strftime('%Y-%m-%d %H:%M:%SZ')
    if problems:
        message = '\n'.join(problems)
        print('%s FAILED\n%s' % (stamp, message))
        ping(ping_url, False, message)
        return 1

    message = '; '.join(notes)
    print('%s OK: %s' % (stamp, message))
    ping(ping_url, True, message)
    return 0


if __name__ == '__main__':
    sys.exit(main())

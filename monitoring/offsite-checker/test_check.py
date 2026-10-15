"""Tests for check.py. Run from this directory: python -m unittest -v test_check"""

import gzip
import hashlib
import json
import os
import tempfile
import unittest
from datetime import datetime, timedelta, timezone
from unittest import mock

import check

NOW = datetime(2026, 10, 12, 12, 0, 0, tzinfo=timezone.utc)


def sha(data):
    return hashlib.sha256(data).hexdigest()


def make_batch(first, last, prev, exported_at=NOW - timedelta(minutes=10)):
    """Builds (manifest, gz bytes) exactly like the PHP exporter does."""
    lines = [json.dumps({'id': i, 'description': 'created'}).encode() for i in range(first, last + 1)]
    content = b'\n'.join(lines) + b'\n'
    manifest = {
        'first_id': first, 'last_id': last, 'rows': len(lines),
        'content_sha256': sha(content), 'last_row_sha256': sha(lines[-1]),
        'previous_chain_hash': prev, 'chain_hash': sha((prev + sha(content)).encode()),
        'exported_at': exported_at.isoformat(), 'data_file': 'activity_%010d-%010d.jsonl.gz' % (first, last),
    }
    return manifest, gzip.compress(content)


class FakeSource:
    def __init__(self):
        self.files = {}

    def add(self, manifest, gz):
        self.files[manifest['data_file']] = gz
        self.files['activity_%010d-%010d.manifest.json' % (manifest['first_id'], manifest['last_id'])] = json.dumps(manifest).encode()

    def names(self):
        return list(self.files)

    def version_names(self):
        return self.names() + list(getattr(self, 'old_versions', []))

    def read(self, name):
        return self.files[name]


def journal(*ranges):
    source, prev, manifests = FakeSource(), check.GENESIS, []
    for first, last in ranges:
        manifest, gz = make_batch(first, last, prev)
        source.add(manifest, gz)
        prev = manifest['chain_hash']
        manifests.append(manifest)
    return source, manifests


def live_state(manifest, max_id=None, exists=True, row_sha=None):
    return {
        'activity_log': {'max_id': manifest['last_id'] if max_id is None else max_id},
        'requested': {
            'id': manifest['last_id'], 'exists': exists,
            'row_sha256': (manifest['last_row_sha256'] if row_sha is None else row_sha) if exists else None,
        },
    }


def parsed(manifest, name='activity_0000000001-0000000003.manifest.json'):
    return check.parse_manifest(json.dumps(manifest), name)


class EvaluateTest(unittest.TestCase):
    def setUp(self):
        _, manifests = journal((1, 3))
        self.manifest = parsed(manifests[0])

    def run_eval(self, state, saved=None, manifest=None):
        return check.evaluate(manifest or self.manifest, state, saved or {}, NOW, 3)

    def test_clean_run(self):
        problems, saved = self.run_eval(live_state(self.manifest))
        self.assertEqual(problems, [])
        self.assertEqual(saved['max_live_id'], 3)
        self.assertEqual(saved['max_journal_id'], 3)

    def test_live_ahead_of_journal_is_fine(self):
        problems, saved = self.run_eval(live_state(self.manifest, max_id=5))
        self.assertEqual(problems, [])
        self.assertEqual(saved['max_live_id'], 5)

    def test_live_behind_journal_alerts(self):
        problems, _ = self.run_eval(live_state(self.manifest, max_id=2))
        self.assertTrue(any('gone BACK' in p for p in problems))

    def test_missing_row_alerts(self):
        problems, _ = self.run_eval(live_state(self.manifest, exists=False))
        self.assertTrue(any('missing from the live database' in p for p in problems))

    def test_different_row_alerts(self):
        problems, _ = self.run_eval(live_state(self.manifest, row_sha='0' * 64))
        self.assertTrue(any('differs from the journal copy' in p for p in problems))

    def test_live_below_saved_high_water_alerts(self):
        problems, saved = self.run_eval(live_state(self.manifest, max_id=3), saved={'max_live_id': 9})
        self.assertTrue(any('saw #9 earlier' in p for p in problems))
        self.assertEqual(saved['max_live_id'], 9)

    def test_journal_going_backwards_alerts(self):
        problems, saved = self.run_eval(live_state(self.manifest), saved={'max_journal_id': 8})
        self.assertTrue(any('files were removed' in p for p in problems))
        self.assertEqual(saved['max_journal_id'], 8)

    def test_endpoint_without_row_answer_is_a_problem(self):
        problems, _ = self.run_eval({'activity_log': {'max_id': 3}})
        self.assertTrue(any('did not answer the row check' in p for p in problems))

    def test_empty_live_table_alerts(self):
        problems, _ = self.run_eval({'activity_log': {'max_id': None}, 'requested': {'id': 3, 'exists': False}})
        self.assertTrue(any('gone BACK' in p for p in problems))

    def pending_saved(self, hours_ago, journal_id=None):
        return {
            'pending_journal_id': self.manifest['last_id'] if journal_id is None else journal_id,
            'pending_since': (NOW - timedelta(hours=hours_ago)).strftime('%Y-%m-%dT%H:%M:%SZ'),
        }

    def test_stalled_export_with_pending_rows(self):
        problems, _ = self.run_eval(live_state(self.manifest, max_id=7), saved=self.pending_saved(5))
        self.assertTrue(any('export stalled' in p for p in problems))

    def test_first_new_rows_after_a_quiet_spell_are_not_a_stall(self):
        # The newest journal file is 38 hours old because nothing was written, not because the
        # export broke. This is the false alarm seen on 2026-10-14.
        old = dict(self.manifest, exported_at=(NOW - timedelta(hours=38)).isoformat())
        problems, saved = self.run_eval(live_state(self.manifest, max_id=7), manifest=old)
        self.assertEqual(problems, [])
        self.assertEqual(saved['pending_journal_id'], 3)
        self.assertEqual(saved['pending_since'], NOW.strftime('%Y-%m-%dT%H:%M:%SZ'))

    def test_pending_rows_within_the_limit_are_fine_and_keep_their_start_time(self):
        before = self.pending_saved(1)
        problems, saved = self.run_eval(live_state(self.manifest, max_id=7), saved=before)
        self.assertEqual(problems, [])
        self.assertEqual(saved['pending_since'], before['pending_since'])

    def test_pending_exactly_at_the_limit_is_fine(self):
        problems, _ = self.run_eval(live_state(self.manifest, max_id=7), saved=self.pending_saved(3))
        self.assertEqual(problems, [])

    def test_pending_timer_restarts_when_the_journal_moves_forward(self):
        # Rows were waiting for 5 hours behind an older journal id, but the journal has since
        # advanced to #3 and new rows have appeared, so the clock starts again now.
        problems, saved = self.run_eval(live_state(self.manifest, max_id=7), saved=self.pending_saved(5, journal_id=2))
        self.assertEqual(problems, [])
        self.assertEqual(saved['pending_since'], NOW.strftime('%Y-%m-%dT%H:%M:%SZ'))

    def test_pending_marker_is_cleared_once_the_journal_catches_up(self):
        problems, saved = self.run_eval(live_state(self.manifest), saved=self.pending_saved(5))
        self.assertEqual(problems, [])
        self.assertNotIn('pending_since', saved)
        self.assertNotIn('pending_journal_id', saved)

    def test_corrupt_or_future_pending_time_restarts_the_clock(self):
        for bad in ('not a time', (NOW + timedelta(hours=2)).strftime('%Y-%m-%dT%H:%M:%SZ')):
            saved = {'pending_journal_id': 3, 'pending_since': bad}
            problems, new_saved = self.run_eval(live_state(self.manifest, max_id=7), saved=saved)
            self.assertEqual(problems, [])
            self.assertEqual(new_saved['pending_since'], NOW.strftime('%Y-%m-%dT%H:%M:%SZ'))

    def test_state_file_from_the_older_checker_still_works(self):
        saved = {'max_live_id': 3, 'max_journal_id': 3, 'updated_at': '2026-10-13T16:20:01Z'}
        problems, new_saved = self.run_eval(live_state(self.manifest, max_id=7), saved=saved)
        self.assertEqual(problems, [])
        self.assertEqual(new_saved['pending_since'], NOW.strftime('%Y-%m-%dT%H:%M:%SZ'))

    def test_old_manifest_without_pending_rows_is_fine(self):
        old = dict(self.manifest, exported_at=(NOW - timedelta(hours=30)).isoformat())
        problems, _ = self.run_eval(live_state(self.manifest), manifest=old)
        self.assertEqual(problems, [])


class ChainAndDataTest(unittest.TestCase):
    def test_valid_journal_passes(self):
        source, _ = journal((1, 3), (4, 4), (5, 9))
        problems, count = check.verify_journal_full(source, source.names())
        self.assertEqual((problems, count), ([], 3))

    def test_removed_middle_file_breaks_chain_and_continuity(self):
        source, _ = journal((1, 3), (4, 4), (5, 9))
        del source.files['activity_0000000004-0000000004.manifest.json']
        del source.files['activity_0000000004-0000000004.jsonl.gz']
        problems, _ = check.verify_journal_full(source, source.names())
        self.assertTrue(any('chain broken' in p for p in problems))
        self.assertTrue(any('not continuous' in p for p in problems))

    def test_altered_data_file_is_detected(self):
        source, manifests = journal((1, 3))
        tampered = b'\n'.join(json.dumps({'id': i, 'description': 'edited'}).encode() for i in (1, 2, 3)) + b'\n'
        source.files[manifests[0]['data_file']] = gzip.compress(tampered)
        problems, _ = check.verify_journal_full(source, source.names())
        self.assertTrue(any('checksum differs' in p for p in problems))
        self.assertTrue(any('last row hash differs' in p for p in problems))

    def test_missing_data_file_is_detected(self):
        source, manifests = journal((1, 3))
        del source.files[manifests[0]['data_file']]
        problems, _ = check.verify_journal_full(source, source.names())
        self.assertTrue(any('missing from the journal' in p for p in problems))

    def test_garbage_gzip_is_detected(self):
        source, manifests = journal((1, 3))
        source.files[manifests[0]['data_file']] = b'not gzip'
        problems, _ = check.verify_journal_full(source, source.names())
        self.assertTrue(any('not a valid gzip' in p for p in problems))

    def test_first_manifest_must_start_at_genesis(self):
        source = FakeSource()
        manifest, gz = make_batch(1, 3, 'f' * 64)
        source.add(manifest, gz)
        problems, _ = check.verify_journal_full(source, source.names())
        self.assertTrue(any('chain broken' in p for p in problems))

    def test_unsafe_data_file_name_is_rejected(self):
        _, manifests = journal((1, 3))
        manifest = dict(manifests[0], data_file='../../etc/passwd')
        self.assertTrue(check.verify_data(parsed(manifest), b''))


class VisibilityTest(unittest.TestCase):
    OLD_MANIFEST = 'activity_0000000004-0000000009.manifest-v2026-10-13-162435-000.json'
    OLD_DATA = 'activity_0000000004-0000000009.jsonl-v2026-10-13-162435-000.gz'

    def test_untouched_journal_is_fine(self):
        source, _ = journal((1, 3), (4, 9))
        self.assertEqual(check.verify_visibility(source.names(), source.version_names()), [])

    def test_hidden_manifest_alerts(self):
        source, _ = journal((1, 3), (4, 9))
        visible = [n for n in source.names() if n != 'activity_0000000004-0000000009.manifest.json']
        problems = check.verify_visibility(visible, visible + [self.OLD_MANIFEST])
        self.assertEqual(len(problems), 1)
        self.assertIn('hidden or deleted', problems[0])
        self.assertIn('activity_0000000004-0000000009.manifest.json', problems[0])

    def test_hidden_data_file_alerts(self):
        source, _ = journal((1, 3), (4, 9))
        visible = [n for n in source.names() if n != 'activity_0000000004-0000000009.jsonl.gz']
        problems = check.verify_visibility(visible, visible + [self.OLD_DATA])
        self.assertTrue(any('hidden or deleted' in p for p in problems))

    def test_hide_marker_listed_under_its_plain_name_alerts(self):
        # With --b2-versions the hide marker keeps the plain name, the upload gets the time stamp.
        source, _ = journal((1, 3))
        problems = check.verify_visibility([], source.names() + [self.OLD_MANIFEST])
        self.assertTrue(any('hidden or deleted' in p for p in problems))

    def test_replaced_file_alerts(self):
        source, _ = journal((1, 3), (4, 9))
        problems = check.verify_visibility(source.names(), source.names() + [self.OLD_MANIFEST])
        self.assertEqual(len(problems), 1)
        self.assertIn('replaced', problems[0])

    def test_unrelated_files_are_ignored(self):
        source, _ = journal((1, 3))
        visible = source.names()
        self.assertEqual(check.verify_visibility(visible, visible + ['other-v2026-10-13-162435-000.txt', 'notes.txt']), [])

    def test_run_check_reports_hidden_file(self):
        source, manifests = journal((1, 3), (4, 6))
        source.old_versions = ['activity_0000000001-0000000003.manifest-v2026-10-13-162435-000.json']
        del source.files['activity_0000000001-0000000003.manifest.json']
        problems, _, _ = check.run_check(source, lambda row_id: live_state(manifests[-1]), {}, NOW, 3, full=False)
        self.assertTrue(any('hidden or deleted' in p for p in problems))


class ManifestParsingTest(unittest.TestCase):
    def test_newest_manifest_is_chosen_by_id_range(self):
        names = [
            'activity_0000000001-0000000003.manifest.json', 'activity_0000000010-0000000020.manifest.json',
            'activity_0000000004-0000000009.manifest.json', 'activity_0000000010-0000000020.jsonl.gz', 'other.txt',
        ]
        self.assertEqual(check.manifest_names(names)[-1], 'activity_0000000010-0000000020.manifest.json')

    def test_incomplete_manifest_raises(self):
        with self.assertRaises(check.CheckerError):
            check.parse_manifest('{"last_id": 3}', 'x')
        with self.assertRaises(check.CheckerError):
            check.parse_manifest('nope', 'x')


class RunCheckTest(unittest.TestCase):
    def test_run_check_quick_and_full(self):
        source, manifests = journal((1, 3), (4, 6))
        problems, notes, saved = check.run_check(source, lambda row_id: live_state(manifests[-1]), {}, NOW, 3, full=True)
        self.assertEqual(problems, [])
        self.assertEqual(saved['max_journal_id'], 6)
        self.assertIn('full verification of 2 manifest(s)', notes)

    def test_empty_journal_is_an_error(self):
        with self.assertRaises(check.CheckerError):
            check.run_check(FakeSource(), lambda i: {}, {}, NOW, 3, full=False)

    def test_local_dir_source_round_trip(self):
        source, manifests = journal((1, 3))
        with tempfile.TemporaryDirectory() as directory:
            for name, content in source.files.items():
                with open(os.path.join(directory, name), 'wb') as handle:
                    handle.write(content)
            problems, _, _ = check.run_check(check.LocalDirSource(directory), lambda i: live_state(manifests[0]), {}, NOW, 3, full=True)
        self.assertEqual(problems, [])


class StateFileTest(unittest.TestCase):
    def test_round_trip_and_missing_file(self):
        with tempfile.TemporaryDirectory() as directory:
            path = os.path.join(directory, 'sub', 'state.json')
            self.assertEqual(check.load_saved(path), {})
            check.save_saved(path, {'max_live_id': 4})
            self.assertEqual(check.load_saved(path), {'max_live_id': 4})

    def test_corrupt_state_file_raises(self):
        with tempfile.TemporaryDirectory() as directory:
            path = os.path.join(directory, 'state.json')
            with open(path, 'w') as handle:
                handle.write('{broken')
            with self.assertRaises(check.CheckerError):
                check.load_saved(path)


class PingTest(unittest.TestCase):
    def test_success_and_fail_urls(self):
        with mock.patch('urllib.request.urlopen') as opened:
            check.ping('https://hc-ping.com/abc/', True, 'fine')
            check.ping('https://hc-ping.com/abc', False, 'broken')
        urls = [call.args[0].full_url for call in opened.call_args_list]
        self.assertEqual(urls, ['https://hc-ping.com/abc', 'https://hc-ping.com/abc/fail'])

    def test_no_url_does_nothing(self):
        with mock.patch('urllib.request.urlopen') as opened:
            self.assertFalse(check.ping('', True))
        opened.assert_not_called()


class MainTest(unittest.TestCase):
    def run_main(self, state, extra_args=()):
        source, manifests = journal((1, 3))
        with tempfile.TemporaryDirectory() as directory:
            for name, content in source.files.items():
                with open(os.path.join(directory, name), 'wb') as handle:
                    handle.write(content)
            env = {
                'CHECKER_JOURNAL_DIR': directory, 'CHECKER_STATE_URL': 'https://example.test/monitoring/state',
                'CHECKER_STATE_TOKEN': 'secret', 'CHECKER_PING_URL': 'https://hc-ping.com/abc',
                'CHECKER_STATE_FILE': os.path.join(directory, 'state', 'state.json'),
            }
            with mock.patch.dict(os.environ, env, clear=True), \
                    mock.patch.object(check, 'fetch_state', return_value=state(manifests[0])), \
                    mock.patch.object(check, 'ping') as pinged:
                code = check.main(list(extra_args))
        return code, pinged

    def test_main_ok_pings_success(self):
        code, pinged = self.run_main(lambda m: live_state(m))
        self.assertEqual(code, 0)
        self.assertTrue(pinged.call_args.args[1])

    def test_main_rollback_pings_fail_and_exits_1(self):
        code, pinged = self.run_main(lambda m: live_state(m, max_id=1))
        self.assertEqual(code, 1)
        self.assertFalse(pinged.call_args.args[1])
        self.assertNotIn('secret', pinged.call_args.args[2])

    def test_main_missing_config_fails_without_crashing(self):
        with mock.patch.dict(os.environ, {}, clear=True), mock.patch.object(check, 'ping'):
            code = check.main(['--no-ping'])
        self.assertEqual(code, 1)


if __name__ == '__main__':
    unittest.main()

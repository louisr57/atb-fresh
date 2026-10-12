# Off-droplet rollback checker

`check.py` runs on a **separate host** (not the production droplet). It reads the append-only activity journal in Backblaze B2 and asks the production app (`GET /monitoring/state`) what the live database looks like. It reports to its own Healthchecks check.

It alerts when: the live highest id is lower than the journal's or than any value seen before; the newest journaled row is missing or different live; journal files are missing, altered or out of order (`--full`); the endpoint is unreachable or rejects the token; or the checker itself stops running (Healthchecks sees no ping).

Standard library only (Python 3.8+) plus `rclone`. Tests: `python -m unittest -v test_check` in this directory.

## Setup on the new Linode (Ubuntu 24.04)

Do these as root once, then work as the `checker` user.

```bash
# 1. Updates, firewall, automatic security updates, UTC
apt update && apt -y upgrade
apt -y install rclone unattended-upgrades ufw python3
ufw allow OpenSSH && ufw --force enable
timedatectl set-timezone UTC

# 2. Normal user with the same SSH key as root
adduser --disabled-password --gecos "" checker
mkdir -p /home/checker/.ssh && cp /root/.ssh/authorized_keys /home/checker/.ssh/
chown -R checker:checker /home/checker/.ssh && chmod 700 /home/checker/.ssh && chmod 600 /home/checker/.ssh/authorized_keys
```

Check `ssh checker@<ip>` works in a SECOND terminal, then (still as root) turn off root and password login:

```bash
cat > /etc/ssh/sshd_config.d/99-hardening.conf <<'EOF'
PermitRootLogin no
PasswordAuthentication no
EOF
systemctl reload ssh
```

Then, as `checker`:

```bash
# 3. rclone remote with a READ-ONLY B2 key (listFiles + readFiles on the journal bucket only)
rclone config            # new remote "b2journal", type b2, paste the key id and key
chmod 600 ~/.config/rclone/rclone.conf
rclone lsf b2journal:atb-fresh-activity-journal   # must list the activity_* files
# Safety check, this MUST fail (the key must not be able to write):
echo test | rclone rcat b2journal:atb-fresh-activity-journal/should_fail.txt

# 4. The script and its settings
mkdir -p ~/offsite-checker ~/.config
# copy check.py to ~/offsite-checker/check.py, env.example to ~/.config/offsite-checker.env
chmod 600 ~/.config/offsite-checker.env     # fill in the token and the NEW Healthchecks ping URL
python3 ~/offsite-checker/check.py --env-file ~/.config/offsite-checker.env --no-ping
python3 ~/offsite-checker/check.py --env-file ~/.config/offsite-checker.env --full --no-ping

# 5. Schedule (crontab -e). Quick check every 15 min at :05 :20 :35 :50, full check daily 04:10 UTC
5,20,35,50 * * * * python3 /home/checker/offsite-checker/check.py --env-file /home/checker/.config/offsite-checker.env >> /home/checker/checker.log 2>&1
10 4 * * * python3 /home/checker/offsite-checker/check.py --env-file /home/checker/.config/offsite-checker.env --full >> /home/checker/checker.log 2>&1
```

Healthchecks: create a NEW check (not the export job's), period 15 minutes, grace 15 minutes, with an alert channel you actually watch. The daily `--full` run uses the same ping URL.

## After a deliberate restore of the production database

The checker will (correctly) alert. Once you have confirmed the restore was intended, run it once with `--reset-state` to forget the saved high-water marks. The journal itself must be reconciled separately.

## Limits

- It cannot see a rollback to a point before the newest journal export if nothing new is journaled afterwards.
- It compares ids and hashes only, never `created_at` (the app writes IST timestamps).
- The state token is the same value as `ACTIVITY_JOURNAL_STATE_TOKEN` on production. The endpoint exposes no activity content.

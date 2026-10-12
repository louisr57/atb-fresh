<?php

namespace App\Console\Commands;

use App\Services\ActivityJournal\ActivityJournalExporter;
use App\Services\ActivityJournal\JournalStore;
use App\Services\ActivityJournal\LocalJournalStore;
use App\Services\ActivityJournal\RcloneJournalStore;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use RuntimeException;
use Throwable;

/**
 * Appends new activity_log rows to an off-server, append-only journal and
 * checks that the journal's history still matches the live database.
 *
 * Reads the database only. Writes only to the journal store (never overwrites).
 */
class ExportActivityJournal extends Command
{
    protected $signature = 'activity:export-journal
        {--dry-run : Report what would be exported and run the integrity check, write nothing}';

    protected $description = 'Append new activity_log rows to the off-server journal and verify it still matches the database';

    public function handle(): int
    {
        $store = $this->resolveStore();

        if ($store === null) {
            $this->info('Activity journal is not configured (ACTIVITY_JOURNAL_STORE is empty). Nothing to do.');

            return self::SUCCESS;
        }

        try {
            $exporter = new ActivityJournalExporter(
                $store,
                max(1, (int) config('activity-journal.batch_size')),
                max(0, (int) config('activity-journal.settle_seconds')),
            );
            $result = $exporter->export((bool) $this->option('dry-run'));
        } catch (Throwable $e) {
            $this->error('Activity journal export failed: '.$e->getMessage());
            $this->ping(false);

            return self::FAILURE;
        }

        foreach ($result['alerts'] as $alert) {
            $this->error($alert);
        }

        if ($this->option('dry-run')) {
            $this->info("Dry run: {$result['pending_rows']} row(s) after #{$result['last_exported_id']} would be exported.");
        } else {
            $this->info("Exported {$result['exported_rows']} row(s) in {$result['files']} file(s). Journal now ends at #{$result['last_exported_id']}.");
        }

        if ($result['alerts'] !== []) {
            $this->ping(false);

            return self::FAILURE;
        }

        if (! $this->option('dry-run')) {
            $this->ping(true);
        }

        return self::SUCCESS;
    }

    private function resolveStore(): ?JournalStore
    {
        $type = config('activity-journal.store');

        if ($type === null || $type === '') {
            return null;
        }

        if ($type === 'local') {
            return new LocalJournalStore((string) config('activity-journal.local_path'));
        }

        if ($type === 'rclone') {
            $remote = (string) config('activity-journal.rclone_remote');

            if ($remote === '') {
                throw new RuntimeException('ACTIVITY_JOURNAL_RCLONE_REMOTE must be set when ACTIVITY_JOURNAL_STORE=rclone.');
            }

            return new RcloneJournalStore((string) config('activity-journal.rclone_binary'), $remote);
        }

        throw new RuntimeException("Unknown ACTIVITY_JOURNAL_STORE '{$type}' (use rclone or local).");
    }

    private function ping(bool $ok): void
    {
        $url = (string) config('activity-journal.ping_url');

        if ($url === '') {
            return;
        }

        try {
            Http::timeout(10)->retry(2, 500)->get(rtrim($url, '/').($ok ? '' : '/fail'));
        } catch (Throwable) {
            // A failed ping must never fail the export itself.
        }
    }
}

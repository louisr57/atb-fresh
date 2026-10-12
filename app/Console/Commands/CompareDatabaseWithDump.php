<?php

namespace App\Console\Commands;

use App\Models\Course;
use App\Models\Event;
use App\Models\Facilitator;
use App\Models\Registration;
use App\Models\Student;
use App\Models\Venue;
use App\Services\MysqlDumpReader;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Throwable;

/**
 * Read-only forensic check: compares the live database with an older mysqldump to look
 * for signs that the database was restored to an earlier point in time.
 *
 * Rollback signatures it looks for:
 *  - activity_log rows that existed in the dump but are now missing or different
 *    (after a restore, AUTO_INCREMENT restarts lower and new entries REUSE old ids);
 *  - business rows from the dump that are gone without a logged deletion;
 *  - unusually long silences in activity_log after the dump (a restore to a point
 *    AFTER the dump can only show up as a gap in time, not as missing ids).
 *
 * Nothing is written. Timestamps are compared as the app reads them (the default
 * MySQL session time zone). Laravel writes app-time (IST) wall-clock strings and
 * production MySQL runs on UTC, so a production dump (TIME_ZONE '+00:00') holds the
 * same IST strings. Run this ON PRODUCTION: a local MySQL with a different system
 * time zone would show every TIMESTAMP shifted.
 */
class CompareDatabaseWithDump extends Command
{
    protected $signature = 'audit:compare-dump
        {dump : Path to a plain-text mysqldump file (absolute, or relative to the app root)}
        {--table=* : Only compare these tables (default: every table in the dump except caches/queues)}
        {--details : Show changed values (sensitive columns are always masked)}
        {--limit=20 : Maximum rows listed per finding}
        {--gaps=10 : How many of the longest activity_log silences after the dump to list}';

    protected $description = 'Read-only: compare the live database with an older mysqldump to detect a database rollback/restore';

    /** Tables that legitimately churn and say nothing about a rollback. */
    private const SKIPPED_TABLES = ['cache', 'cache_locks', 'sessions', 'jobs', 'job_batches', 'failed_jobs', 'password_reset_tokens', 'migrations'];

    /** Never print these values. */
    private const MASKED_COLUMNS = ['password', 'remember_token'];

    /** Hard-delete cascades (FK => parent table) that explain a vanished child row. */
    private const CASCADES = [
        'registrations' => ['student_id' => 'students', 'event_id' => 'events'],
        'event_facilitator' => ['event_id' => 'events', 'facilitator_id' => 'facilitators'],
        'events' => ['venue_id' => 'venues', 'course_id' => 'courses'],
    ];

    private int $limit = 20;

    /** @var array<string, string> table => model class, for models using LogsActivity */
    private array $loggedModels = [];

    /** @var array<string, string> "Model\Class#id" => created_at of the logged deletion */
    private array $loggedDeletions = [];

    /** @var array<string, array<int, string>> overall verdict lines, keyed by severity */
    private array $verdict = ['alert' => [], 'info' => []];

    public function handle(): int
    {
        $path = $this->argument('dump');
        if (! is_file($path)) {
            $path = base_path($path);
        }

        try {
            $dump = MysqlDumpReader::fromFile($path);
        } catch (Throwable $e) {
            $this->error('Could not read the dump: '.$e->getMessage());

            return self::FAILURE;
        }

        // Laravel can reuse a command instance (e.g. Artisan::call twice in one process).
        $this->verdict = ['alert' => [], 'info' => []];
        $this->loggedDeletions = [];
        $this->reported = [];
        $this->existsCache = [];

        $this->limit = max(1, (int) $this->option('limit'));
        $this->loadActivityContext();

        $this->info('Dump: '.$path);
        $this->line('Dump completed on: '.($dump->completedAt() ?? 'unknown').' (server clock, UTC on production)');
        $this->line('Live database: '.DB::connection()->getDatabaseName().' ('.DB::connection()->getDriverName().')');
        $this->line('Read-only comparison. Nothing will be modified.');
        $this->newLine();

        $summary = [];
        foreach ($this->tablesToCompare($dump) as $table) {
            $summary[] = $this->compareTable($dump, $table);
        }

        $this->table(['Table', 'Dump rows', 'Matching', 'Changed', 'Id reused', 'Missing', 'Missing (unexplained)', 'Newer rows'], $summary);

        if ($dump->hasTable('activity_log') && Schema::hasTable('activity_log')) {
            $this->activityLogContinuity($dump);
        }

        $this->printVerdict($dump);

        return self::SUCCESS;
    }

    private function loadActivityContext(): void
    {
        foreach ([Course::class, Event::class, Facilitator::class, Registration::class, Student::class, Venue::class] as $class) {
            $this->loggedModels[(new $class)->getTable()] = $class;
        }

        if (! Schema::hasTable('activity_log')) {
            return;
        }

        DB::table('activity_log')
            ->where('event', 'deleted')
            ->orderBy('id')
            ->get(['subject_type', 'subject_id', 'created_at'])
            ->each(function ($row) {
                $this->loggedDeletions[$row->subject_type.'#'.$row->subject_id] ??= (string) $row->created_at;
            });
    }

    /** @return array<int, string> */
    private function tablesToCompare(MysqlDumpReader $dump): array
    {
        $requested = array_filter((array) $this->option('table'));

        if ($requested !== []) {
            foreach (array_diff($requested, $dump->tableNames()) as $unknown) {
                $this->warn("Table '{$unknown}' is not in the dump and will be skipped.");
            }

            return array_values(array_intersect($requested, $dump->tableNames()));
        }

        return array_values(array_diff($dump->tableNames(), self::SKIPPED_TABLES));
    }

    /** @return array<int, string|int> one summary row for the table */
    private function compareTable(MysqlDumpReader $dump, string $table): array
    {
        $dumpRows = $dump->rows($table);
        $dumpCount = count($dumpRows);

        if (! Schema::hasTable($table)) {
            $this->verdict['alert'][] = "Table '{$table}' exists in the dump but not in the live database.";

            return [$table, $dumpCount, '-', '-', '-', '-', 'table missing', '-'];
        }

        $liveColumns = Schema::getColumnListing($table);
        if (! in_array('id', $dump->columns($table), true) || ! in_array('id', $liveColumns, true)) {
            return [$table, $dumpCount, 'skipped (no id column)', '-', '-', '-', '-', '-'];
        }

        $columns = array_values(array_intersect($dump->columns($table), $liveColumns));
        $dumpIds = array_map(fn (array $row) => (int) $row['id'], $dumpRows);
        $live = $this->liveRows($table, $dumpIds);

        $findings = ['changed' => [], 'reused' => [], 'missing' => [], 'unexplained' => []];
        $isActivityLog = $table === 'activity_log';
        $modelClass = $this->loggedModels[$table] ?? null;

        foreach ($dumpRows as $dumpRow) {
            $id = (int) $dumpRow['id'];

            if (! isset($live[$id])) {
                $findings['missing'][] = $id;
                $reason = $isActivityLog ? null : $this->explainMissing($table, $modelClass, $dumpRow);
                if ($reason === null && ($isActivityLog || $modelClass !== null)) {
                    $findings['unexplained'][] = $id;
                }
                $this->reportRow($table, 'missing', $id, $dumpRow, null, $reason ?? ($modelClass !== null || $isActivityLog ? 'no deletion logged' : 'not tracked by the activity log'));

                continue;
            }

            $differences = $this->differences($columns, $dumpRow, (array) $live[$id]);
            if ($differences === []) {
                continue;
            }

            $kind = ($isActivityLog || in_array('created_at', $differences, true)) ? 'reused' : 'changed';
            $findings[$kind][] = $id;
            $this->reportRow($table, $kind, $id, $dumpRow, (array) $live[$id], 'differs in: '.implode(', ', $differences));
        }

        $newer = DB::table($table)->where('id', '>', $dumpIds === [] ? 0 : max($dumpIds))->count();
        $matching = $dumpCount - count($findings['missing']) - count($findings['changed']) - count($findings['reused']);

        $this->recordTableVerdict($table, $findings, $dump, $isActivityLog, $modelClass !== null);

        return [
            $table,
            $dumpCount,
            $matching,
            count($findings['changed']),
            count($findings['reused']),
            count($findings['missing']),
            ($modelClass !== null || $isActivityLog) ? count($findings['unexplained']) : 'n/a',
            $newer,
        ];
    }

    /**
     * @param  array<int, int>  $ids
     * @return array<int, object>
     */
    private function liveRows(string $table, array $ids): array
    {
        $rows = [];
        foreach (array_chunk($ids, 500) as $chunk) {
            foreach (DB::table($table)->whereIn('id', $chunk)->get() as $row) {
                $rows[(int) $row->id] = $row;
            }
        }

        return $rows;
    }

    /**
     * @param  array<int, string>  $columns
     * @param  array<string, string|null>  $dumpRow
     * @param  array<string, mixed>  $liveRow
     * @return array<int, string> names of columns whose values differ
     */
    private function differences(array $columns, array $dumpRow, array $liveRow): array
    {
        $different = [];

        foreach ($columns as $column) {
            $old = $dumpRow[$column] ?? null;
            $new = $liveRow[$column] ?? null;

            if ($old === null || $new === null) {
                if ($old !== $new) {
                    $different[] = $column;
                }

                continue;
            }

            $new = (string) $new;
            if ($old === $new) {
                continue;
            }
            if (is_numeric($old) && is_numeric($new) && (float) $old === (float) $new) {
                continue;
            }
            $oldJson = json_decode($old, true);
            if ($oldJson !== null && $oldJson == json_decode($new, true)) {
                continue;
            }

            $different[] = $column;
        }

        return $different;
    }

    /**
     * Why might a dump row legitimately be gone? Returns null when nothing explains it.
     *
     * @param  array<string, string|null>  $row
     */
    private function explainMissing(string $table, ?string $modelClass, array $row): ?string
    {
        if ($modelClass !== null && isset($this->loggedDeletions[$modelClass.'#'.$row['id']])) {
            return 'deletion logged at '.$this->loggedDeletions[$modelClass.'#'.$row['id']];
        }

        foreach (self::CASCADES[$table] ?? [] as $foreignKey => $parentTable) {
            $parentId = $row[$foreignKey] ?? null;
            if ($parentId !== null && ! $this->liveRowExists($parentTable, (int) $parentId)) {
                return "parent {$parentTable} #{$parentId} is gone (cascade delete)";
            }
        }

        return null;
    }

    /** @var array<string, bool> */
    private array $existsCache = [];

    private function liveRowExists(string $table, int $id): bool
    {
        return $this->existsCache[$table.'#'.$id] ??= DB::table($table)->where('id', $id)->exists();
    }

    /** @var array<string, int> */
    private array $reported = [];

    /**
     * @param  array<string, string|null>  $dumpRow
     * @param  array<string, mixed>|null  $liveRow
     */
    private function reportRow(string $table, string $kind, int $id, array $dumpRow, ?array $liveRow, string $note): void
    {
        $key = $table.'.'.$kind;
        $this->reported[$key] = ($this->reported[$key] ?? 0) + 1;

        if ($this->reported[$key] > $this->limit) {
            return;
        }

        $labels = ['missing' => 'MISSING', 'reused' => 'ID REUSED', 'changed' => 'CHANGED'];
        $message = "[{$table}] {$labels[$kind]} #{$id}: {$this->describe($table, $dumpRow)} - {$note}";
        $kind === 'changed' ? $this->line($message) : $this->warn($message);

        if ($liveRow !== null && ($kind === 'reused' || $this->option('details'))) {
            if ($kind === 'reused') {
                $this->line('    now holds: '.$this->describe($table, array_map(fn ($v) => $v === null ? null : (string) $v, $liveRow)));
            }
            if ($this->option('details')) {
                foreach ($this->differences(array_keys($dumpRow), $dumpRow, $liveRow) as $column) {
                    $this->line("    {$column}: ".$this->display($column, $dumpRow[$column] ?? null).' => '.$this->display($column, $liveRow[$column] ?? null));
                }
            }
        }
    }

    /** @param  array<string, string|null>  $row */
    private function describe(string $table, array $row): string
    {
        if ($table === 'activity_log') {
            $subject = $row['subject_type'] ? class_basename($row['subject_type']).' #'.$row['subject_id'] : 'no subject';

            return sprintf('[%s] %s: "%s" (%s, causer #%s)', $row['created_at'] ?? '?', $row['log_name'] ?? '-', $row['description'] ?? '', $subject, $row['causer_id'] ?? '-');
        }

        $name = match (true) {
            isset($row['first_name']) => trim($row['first_name'].' '.($row['last_name'] ?? '')),
            isset($row['title']) => $row['title'],
            isset($row['course_code']) => $row['course_code'].' '.($row['course_title'] ?? ''),
            isset($row['venue_name']) => $row['venue_name'],
            isset($row['name']) => $row['name'],
            default => '',
        };

        return trim($name.' (created '.($row['created_at'] ?? '?').')');
    }

    private function display(string $column, mixed $value): string
    {
        if ($value === null) {
            return 'NULL';
        }
        if (in_array($column, self::MASKED_COLUMNS, true)) {
            return '[masked]';
        }

        return '"'.str((string) $value)->limit(120).'"';
    }

    /** @param  array{changed: array<int, int>, reused: array<int, int>, missing: array<int, int>, unexplained: array<int, int>}  $findings */
    private function recordTableVerdict(string $table, array $findings, MysqlDumpReader $dump, bool $isActivityLog, bool $isLogged): void
    {
        if ($isActivityLog) {
            if ($findings['reused'] !== []) {
                $this->verdict['alert'][] = count($findings['reused']).' activity_log id(s) from the dump now hold DIFFERENT entries. The app never edits log rows, so the ids were reissued. This is the classic sign of a database restored to a point before the dump.';
            }
            if ($findings['missing'] !== []) {
                $note = $this->allOlderThanCleanWindow($dump, $findings['missing'])
                    ? ' All of them are older than activitylog.delete_records_older_than_days, so first rule out a manual `php artisan activitylog:clean`.'
                    : '';
                $this->verdict['alert'][] = count($findings['missing']).' activity_log entr(y/ies) from the dump are missing. The app never deletes log rows.'.$note;
            }
            if ($findings['reused'] === [] && $findings['missing'] === []) {
                $this->verdict['info'][] = 'Every activity_log entry in the dump is still present and unchanged.';
            }

            return;
        }

        if ($findings['unexplained'] !== []) {
            $this->verdict['alert'][] = count($findings['unexplained'])." {$table} row(s) from the dump are gone with no logged deletion and no deleted parent row.";
        } elseif ($findings['missing'] !== [] && ! $isLogged) {
            $this->verdict['info'][] = count($findings['missing'])." {$table} row(s) from the dump are gone ({$table} is not tracked by the activity log, so this can't be checked).";
        }

        if ($findings['reused'] !== []) {
            $this->verdict['alert'][] = count($findings['reused'])." {$table} id(s) now hold rows with a different created_at (ids reissued to new records).";
        }
        if ($findings['changed'] !== []) {
            $this->verdict['info'][] = count($findings['changed'])." {$table} row(s) were edited since the dump (normal activity; use --details to see what changed).";
        }
    }

    /** @param  array<int, int>  $ids */
    private function allOlderThanCleanWindow(MysqlDumpReader $dump, array $ids): bool
    {
        $cutoff = now()->subDays((int) config('activitylog.delete_records_older_than_days', 365));
        $wanted = array_flip($ids);

        foreach ($dump->rows('activity_log') as $row) {
            if (isset($wanted[(int) $row['id']]) && ($row['created_at'] === null || Carbon::parse($row['created_at'])->gte($cutoff))) {
                return false;
            }
        }

        return true;
    }

    private function printVerdict(MysqlDumpReader $dump): void
    {
        $this->newLine();
        $this->info('Verdict');

        foreach ($this->verdict['alert'] as $line) {
            $this->error('ALERT: '.$line);
        }
        foreach ($this->verdict['info'] as $line) {
            $this->line('OK/info: '.$line);
        }

        if ($this->verdict['alert'] === []) {
            $this->info('No sign that the database was rolled back to a point BEFORE this dump ('.($dump->completedAt() ?? 'unknown date').' UTC).');
        }

        $this->line('Note: a restore to a point AFTER the dump leaves the dump rows intact. Its only trace here is a silence in the continuity table. Check any suspicious silence against outside evidence.');
    }

    /**
     * A restore to a point AFTER the dump can't remove dump rows; it only leaves a
     * silence in the log. List the longest silences so they can be checked against
     * what users remember doing (or against SMTP, git and exported files).
     */
    private function activityLogContinuity(MysqlDumpReader $dump): void
    {
        $dumpRows = $dump->rows('activity_log');
        if ($dumpRows === []) {
            return;
        }

        $last = end($dumpRows);
        $this->newLine();
        $this->info('activity_log continuity after the dump');
        $this->line("Last entry in the dump: #{$last['id']} at {$last['created_at']} (app time, ".config('app.timezone').')');

        if ($dump->completedAt() !== null) {
            $completed = Carbon::parse($dump->completedAt(), 'UTC')->setTimezone(config('app.timezone'));
            $this->line('Dump completed at:      '.$completed->format('Y-m-d H:i:s').' (same clock)');
        }

        $entries = DB::table('activity_log')
            ->where('id', '>', (int) $last['id'])
            ->orderBy('id')
            ->get(['id', 'created_at']);

        if ($entries->isEmpty()) {
            $this->warn('No activity_log entries were written after the dump.');
            $this->verdict['info'][] = 'activity_log has no entries newer than the dump.';

            return;
        }

        $previousId = (int) $last['id'];
        $previousAt = Carbon::parse($last['created_at']);
        $gaps = [];
        $backwards = [];

        foreach ($entries as $entry) {
            $at = Carbon::parse($entry->created_at);
            if ($at->lt($previousAt)) {
                $backwards[] = "#{$previousId} ({$previousAt}) -> #{$entry->id} ({$at})";
            }
            $gaps[] = [
                'seconds' => (int) $previousAt->diffInSeconds($at, true),
                'row' => [$previousId, $previousAt->format('Y-m-d H:i'), $entry->id, $at->format('Y-m-d H:i'), $previousAt->diffForHumans($at, ['syntax' => CarbonInterface::DIFF_ABSOLUTE, 'parts' => 2])],
            ];
            $previousId = (int) $entry->id;
            $previousAt = $at;
        }

        $first = $entries->first();
        $this->line("First entry after it:   #{$first->id} at {$first->created_at}");
        $this->line('Entries since the dump: '.$entries->count()." (latest #{$previousId} at {$previousAt})");

        usort($gaps, fn (array $a, array $b) => $b['seconds'] <=> $a['seconds']);
        $this->newLine();
        $this->line('Longest silences in the log since the dump (holidays and quiet weeks are normal):');
        $this->table(
            ['From #', 'At', 'To #', 'At', 'Silence'],
            array_map(fn (array $gap) => $gap['row'], array_slice($gaps, 0, max(1, (int) $this->option('gaps'))))
        );

        if ($backwards !== []) {
            $this->verdict['alert'][] = count($backwards).' place(s) where a newer activity_log id has an OLDER timestamp than the one before it (clock change, or rows written to a different copy of the database), e.g. '.$backwards[0];
        }
    }
}

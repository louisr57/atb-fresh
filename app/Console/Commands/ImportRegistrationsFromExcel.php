<?php

namespace App\Console\Commands;

use App\Models\Event;
use App\Models\Registration;
use App\Models\Student;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\ToArray;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;
use Spatie\Activitylog\Models\Activity;

class ImportRegistrationsFromExcel extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'registrations:import {filename} {--user= : User name to attribute the import to} {--dryrun : Run without making database changes}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Register students (matched by email) on events (matched by course title, start date and venue) from an Excel file in storage/app/imports, all with status Completed';

    private const END_STATUS = 'Completed';

    /**
     * Column mappings for flexible header detection (compared lower-cased and trimmed)
     */
    private $columnMappings = [
        'course_title' => ['course title', 'course_title', 'course', 'course name', 'event'],
        'start_date' => ['start date', 'start_date', 'startdate', 'date from', 'datefrom', 'date'],
        'venue' => ['venue', 'venue name', 'venue_name'],
        'first_name' => ['first_name', 'firstname', 'first name', 'first-name', 'fname'],
        'last_name' => ['last_name', 'lastname', 'last name', 'last-name', 'lname', 'surname'],
        'email' => ['email', 'email_address', 'email address', 'email adress', 'e-mail', 'e_mail'],
    ];

    /** Rows that were not registered: each is [row, email, event label, reason]. */
    private array $skipped = [];

    /** Non-blocking observations (e.g. name differs from the database). */
    private array $warnings = [];

    public function handle()
    {
        $filename = $this->argument('filename');
        $isDryRun = (bool) $this->option('dryrun');
        $userName = $this->option('user');

        $user = null;
        if ($userName) {
            $user = User::where('name', $userName)->first();
            if (! $user) {
                $this->error("User '{$userName}' not found.");
                $this->info('Available users: '.User::pluck('name')->implode(', '));

                return 1;
            }
        }

        if ($isDryRun) {
            $this->info('DRY RUN MODE - No changes will be made');
            $this->newLine();
        }

        if ($user) {
            $this->info("Import will be attributed to user: {$user->name}");
            $this->newLine();
        }

        $filePath = storage_path('app/imports/'.$filename);
        if (! file_exists($filePath)) {
            $this->error("File not found: {$filePath}");
            $this->info('Please place your Excel file in the storage/app/imports/ directory');

            return 1;
        }

        try {
            $this->info("Processing {$filename}...");
            $data = Excel::toArray(new class implements ToArray
            {
                public function array(array $array): array
                {
                    return $array;
                }
            }, $filePath);

            if (empty($data) || empty($data[0])) {
                $this->error('Excel file is empty or invalid');

                return 1;
            }

            $rows = $data[0];
            $headers = array_map(fn ($h) => strtolower(trim((string) $h)), $rows[0]);
            $columns = $this->detectColumns($headers);
            if (! $columns) {
                return 1;
            }

            $groups = $this->groupRowsByEvent(array_slice($rows, 1), $columns);
            $results = $this->processGroups($groups, $isDryRun, $user);

            $this->displaySummary($results, $isDryRun);

            return 0;
        } catch (\Throwable $e) {
            $this->error('Error processing file: '.$e->getMessage());

            return 1;
        }
    }

    private function detectColumns(array $headers): ?array
    {
        $indices = [];
        foreach ($this->columnMappings as $field => $variations) {
            $found = false;
            foreach ($variations as $variation) {
                $index = array_search($variation, $headers, true);
                if ($index !== false) {
                    $indices[$field] = $index;
                    $found = true;
                    break;
                }
            }
            if (! $found) {
                $this->error("Required column '{$field}' not found. Looking for variations: ".implode(', ', $variations));

                return null;
            }
        }

        return $indices;
    }

    /**
     * Normalise a spreadsheet date cell (Excel serial number or text) to Y-m-d.
     */
    private function parseDate($value): ?string
    {
        if ($value === null || trim((string) $value) === '') {
            return null;
        }

        try {
            if (is_numeric($value)) {
                return ExcelDate::excelToDateTimeObject((float) $value)->format('Y-m-d');
            }

            return Carbon::parse(trim((string) $value))->format('Y-m-d');
        } catch (\Throwable $e) {
            return null;
        }
    }

    private function norm(?string $value): string
    {
        return Str::lower(trim(preg_replace('/\s+/u', ' ', (string) $value)));
    }

    private function nameKey(?string $first, ?string $last): string
    {
        return Str::lower(Str::ascii(trim(preg_replace('/\s+/u', ' ', trim((string) $first).' '.trim((string) $last)))));
    }

    /**
     * Group data rows by event key (course title + date + venue), keeping the original row numbers.
     */
    private function groupRowsByEvent(array $dataRows, array $columns): array
    {
        $groups = [];

        foreach ($dataRows as $index => $row) {
            $rowNumber = $index + 2; // header is row 1

            if (implode('', array_map(fn ($v) => trim((string) $v), $row)) === '') {
                continue; // completely blank row
            }

            $cell = fn (string $field) => trim((string) ($row[$columns[$field]] ?? ''));

            $course = $cell('course_title');
            $venue = $cell('venue');
            $rawDate = $row[$columns['start_date']] ?? null;
            $date = $this->parseDate($rawDate);

            $key = $this->norm($course).'|'.($date ?? 'invalid:'.trim((string) $rawDate)).'|'.$this->norm($venue);

            $groups[$key]['course'] = $course;
            $groups[$key]['venue'] = $venue;
            $groups[$key]['date'] = $date;
            $groups[$key]['raw_date'] = trim((string) $rawDate);
            $groups[$key]['rows'][] = [
                'row' => $rowNumber,
                'first_name' => $cell('first_name'),
                'last_name' => $cell('last_name'),
                'email' => Str::lower($cell('email')),
            ];
        }

        return $groups;
    }

    /**
     * Resolve a group to exactly one event, or return a skip reason.
     *
     * @return array{0: ?Event, 1: ?string}
     */
    private function resolveEvent(array $group): array
    {
        if ($group['course'] === '' || $group['venue'] === '') {
            return [null, 'course title or venue is missing'];
        }
        if ($group['date'] === null) {
            return [null, 'start date is missing or not understood ("'.$group['raw_date'].'")'];
        }

        $title = $this->norm($group['course']);

        // Soft-deleted events are excluded automatically by the SoftDeletes scope.
        $byCourseAndDate = Event::query()
            ->whereDate('datefrom', $group['date'])
            ->where(function ($q) use ($title) {
                $q->whereHas('course', fn ($c) => $c->whereRaw('LOWER(TRIM(course_title)) = ?', [$title]))
                    ->orWhereRaw('LOWER(TRIM(title)) = ?', [$title]);
            })
            ->with('venue')
            ->get();

        if ($byCourseAndDate->isEmpty()) {
            return [null, 'no event found for this course title and start date'];
        }

        $venue = $this->norm($group['venue']);
        $matches = $byCourseAndDate->filter(fn (Event $e) => $this->norm($e->venue->venue_name) === $venue)->values();

        if ($matches->isEmpty()) {
            $found = $byCourseAndDate->map(fn (Event $e) => '#'.$e->id.' at "'.$e->venue->venue_name.'"')->implode(', ');

            return [null, "venue mismatch: course and date match event {$found}, but not venue \"{$group['venue']}\""];
        }

        if ($matches->count() > 1) {
            return [null, 'ambiguous: '.$matches->count().' events match (ids '.$matches->pluck('id')->implode(', ').')'];
        }

        return [$matches->first(), null];
    }

    private function processGroups(array $groups, bool $isDryRun, ?User $user): array
    {
        $results = ['rows' => 0, 'created' => 0, 'events' => 0, 'events_skipped' => 0];

        foreach ($groups as $group) {
            $label = "{$group['course']} / ".($group['date'] ?? ($group['raw_date'] ?: 'no date'))." / {$group['venue']}";
            $rowCount = count($group['rows']);
            $results['rows'] += $rowCount;

            [$event, $reason] = $this->resolveEvent($group);

            if (! $event) {
                $this->warn("⚠ Event skipped ({$label}): {$reason} - {$rowCount} row(s) not registered");
                $results['events_skipped']++;
                foreach ($group['rows'] as $r) {
                    $this->skipped[] = [$r['row'], $r['email'], $label, "event: {$reason}"];
                }

                continue;
            }

            $this->info("Event #{$event->id}: {$event->title} on ".Carbon::parse($event->datefrom)->format('Y-m-d')." at {$event->venue->venue_name} (current participant_count {$event->participant_count}), {$rowCount} row(s)");
            $results['events']++;

            $plan = $this->planRegistrations($group['rows'], $event, $label);

            $this->commit($plan, $event, $isDryRun, $user);
            $results['created'] += count($plan);
        }

        return $results;
    }

    /**
     * Decide which rows of an event become registrations; record skips and warnings.
     *
     * @return array<int, array{row: int, student: Student}>
     */
    private function planRegistrations(array $rows, Event $event, string $label): array
    {
        $plan = [];
        $seenStudents = [];

        foreach ($rows as $r) {
            $row = $r['row'];
            $email = $r['email'];

            if ($email === '') {
                $this->skipped[] = [$row, '', $label, 'student: email is empty'];

                continue;
            }

            $students = Student::whereRaw('LOWER(email) = ?', [$email])->get();

            if ($students->count() > 1) {
                $this->skipped[] = [$row, $email, $label, 'student: email matches '.$students->count().' active students (ids '.$students->pluck('id')->implode(', ').')'];

                continue;
            }

            if ($students->isEmpty()) {
                $trashed = Student::onlyTrashed()->whereRaw('LOWER(email) = ?', [$email])->first();
                $why = $trashed
                    ? "student: matching record #{$trashed->id} is soft-deleted (not restored)"
                    : 'student: no student with this email';
                $this->skipped[] = [$row, $email, $label, $why];

                continue;
            }

            $student = $students->first();

            if (isset($seenStudents[$student->id])) {
                $this->skipped[] = [$row, $email, $label, "duplicate row: student #{$student->id} already listed for this event at row {$seenStudents[$student->id]}"];

                continue;
            }
            $seenStudents[$student->id] = $row;

            $existing = Registration::withTrashed()
                ->where('student_id', $student->id)
                ->where('event_id', $event->id)
                ->first();

            if ($existing) {
                $state = $existing->trashed() ? 'a soft-deleted' : 'an existing';
                $this->skipped[] = [$row, $email, $label, "registration: student #{$student->id} already has {$state} registration #{$existing->id} for this event (left untouched)"];

                continue;
            }

            if ($this->nameKey($r['first_name'], $r['last_name']) !== $this->nameKey($student->first_name, $student->last_name)) {
                $this->warnings[] = "Row {$row}: sheet name \"".trim($r['first_name'].' '.$r['last_name']).'" differs from database name "'.trim($student->first_name.' '.$student->last_name)."\" for student #{$student->id} ({$email}); matched by email";
            }

            $plan[] = ['row' => $row, 'student' => $student];
        }

        return $plan;
    }

    /**
     * Create the registrations of one event in a single transaction (or only report in a dry run).
     */
    private function commit(array $plan, Event $event, bool $isDryRun, ?User $user): void
    {
        if ($isDryRun) {
            foreach ($plan as $p) {
                $s = $p['student'];
                $this->info("✓ Row {$p['row']}: Would register #{$s->id} {$s->first_name} {$s->last_name} ({$s->email}) as ".self::END_STATUS);
            }

            return;
        }

        DB::transaction(function () use ($plan, $event, $user) {
            foreach ($plan as $p) {
                $s = $p['student'];

                // Eloquent create, so RegistrationObserver updates events.participant_count
                // and the activity log is written.
                $registration = Registration::create([
                    'student_id' => $s->id,
                    'event_id' => $event->id,
                    'end_status' => self::END_STATUS,
                ]);

                // Credit the automatic "created" entry and add an explicit import entry,
                // mirroring students:import.
                if ($user) {
                    $latest = Activity::where('subject_type', Registration::class)
                        ->where('subject_id', $registration->id)
                        ->latest('id')
                        ->first();
                    if ($latest && $latest->causer_id === null) {
                        $latest->update(['causer_type' => User::class, 'causer_id' => $user->id]);
                    }
                }

                $logger = activity('registration_import');
                if ($user) {
                    $logger->causedBy($user);
                }
                $logger->performedOn($registration)->log('Registration imported from Excel file');

                $this->info("✓ Row {$p['row']}: Registered #{$s->id} {$s->first_name} {$s->last_name} ({$s->email}) as ".self::END_STATUS);
            }
        });
    }

    private function displaySummary(array $results, bool $isDryRun): void
    {
        $this->newLine();
        $this->info($isDryRun ? 'Summary (DRY RUN):' : 'Summary:');
        $this->line("- Total rows processed: {$results['rows']}");
        $this->line('- Events matched: '.$results['events']);
        $this->line('- Events skipped: '.$results['events_skipped']);
        $this->line('- Registrations '.($isDryRun ? 'that would be created' : 'created').': '.$results['created']);
        $this->line('- Rows skipped: '.count($this->skipped));
        $this->line('- Warnings: '.count($this->warnings));

        if ($this->skipped) {
            $this->newLine();
            $this->warn('Skipped rows:');
            foreach ($this->skipped as [$row, $email, $label, $reason]) {
                $this->line("  Row {$row} ({$email}) [{$label}]: {$reason}");
            }
        }

        if ($this->warnings) {
            $this->newLine();
            $this->warn('Warnings:');
            foreach ($this->warnings as $w) {
                $this->line("  {$w}");
            }
        }

        if (! $isDryRun && ($this->skipped || $this->warnings)) {
            $filename = 'registrations_report_'.Carbon::now()->format('Ymd_His').'.txt';
            $content = 'Registration import report, generated '.Carbon::now()->format('Y-m-d H:i:s')."\n".str_repeat('=', 60)."\n\nSkipped rows:\n";
            foreach ($this->skipped as [$row, $email, $label, $reason]) {
                $content .= "Row {$row} ({$email}) [{$label}]: {$reason}\n";
            }
            $content .= "\nWarnings:\n".implode("\n", $this->warnings)."\n";
            file_put_contents(storage_path('app/imports/'.$filename), $content);
            $this->line("- Report file: storage/app/imports/{$filename}");
        }
    }
}

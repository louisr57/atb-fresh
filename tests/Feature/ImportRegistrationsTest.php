<?php

use App\Models\Course;
use App\Models\Event;
use App\Models\Registration;
use App\Models\Student;
use App\Models\User;
use App\Models\Venue;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Spatie\Activitylog\Models\Activity;

/**
 * Write a registrations spreadsheet into storage/app/imports and return its file name.
 * Dates given as Y-m-d strings are stored as real Excel date serials.
 *
 * @param  array<int, array<int, string|null>>  $rows  [course, Y-m-d, venue, first, last, email]
 * @param  array<int, string>|null  $headers  Header row; defaults to the first/last name layout
 */
function makeRegistrationSheet(array $rows, ?array $headers = null): string
{
    $dir = storage_path('app/imports');
    if (! is_dir($dir)) {
        mkdir($dir, 0775, true);
    }

    $name = 'test_registrations_'.uniqid().'.xlsx';
    $spreadsheet = new Spreadsheet;
    $sheet = $spreadsheet->getActiveSheet();
    $sheet->fromArray($headers ?? ['Course Title', 'Start Date', 'Venue', 'First name', 'Last Name', 'Email Adress']);

    foreach ($rows as $i => $row) {
        $line = $i + 2;
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', (string) $row[1])) {
            $row[1] = Date::PHPToExcel(new DateTime($row[1]));
        }
        $sheet->fromArray($row, null, 'A'.$line);
    }
    (new Xlsx($spreadsheet))->save($dir.'/'.$name);

    return $name;
}

function makeTestEvent(string $courseTitle = 'ATB 1', string $venueName = 'Auromira', string $date = '2019-06-22'): Event
{
    $course = Course::where('course_title', $courseTitle)->first() ?? Course::create([
        'course_code' => $courseTitle,
        'course_title' => $courseTitle,
        'description' => 'd',
        'prerequisites' => 'none',
        'duration' => 3,
    ]);
    $venue = Venue::where('venue_name', $venueName)->first() ?? Venue::factory()->create(['venue_name' => $venueName]);

    return Event::create([
        'title' => $courseTitle,
        'datefrom' => $date,
        'dateto' => $date,
        'timefrom' => '09:00:00',
        'timeto' => '17:00:00',
        'venue_id' => $venue->id,
        'course_id' => $course->id,
    ]);
}

afterEach(function () {
    foreach (glob(storage_path('app/imports/test_registrations_*.xlsx')) ?: [] as $file) {
        @unlink($file);
    }
    foreach (glob(storage_path('app/imports/registrations_report_*.txt')) ?: [] as $file) {
        @unlink($file);
    }
});

test('registers students by email as Completed and updates the participant count', function () {
    $event = makeTestEvent();
    $ana = Student::factory()->create(['email' => 'ana@example.com', 'first_name' => 'Ana', 'last_name' => 'Lopez']);
    $placeholder = Student::factory()->create(['email' => 'FakeEmail091@example.com', 'first_name' => 'Natalia Valencia', 'last_name' => 'Botero']);

    $file = makeRegistrationSheet([
        ['ATB 1', '2019-06-22', 'Auromira', 'Ana', 'Lopez', 'ana@example.com'],
        ['ATB 1', '2019-06-22', 'Auromira', 'Natalia Valencia', 'Botero', 'fakeemail091@example.com'],
    ]);

    $this->artisan('registrations:import', ['filename' => $file])->assertExitCode(0);

    expect(Registration::where('event_id', $event->id)->pluck('end_status')->unique()->all())->toBe(['Completed'])
        ->and(Registration::where('event_id', $event->id)->pluck('student_id')->sort()->values()->all())->toBe([$ana->id, $placeholder->id])
        ->and($event->fresh()->participant_count)->toBe(2);
});

test('dry run creates nothing', function () {
    $event = makeTestEvent();
    Student::factory()->create(['email' => 'dry@example.com']);
    $file = makeRegistrationSheet([['ATB 1', '2019-06-22', 'Auromira', 'A', 'B', 'dry@example.com']]);

    $this->artisan('registrations:import', ['filename' => $file, '--dryrun' => true])->assertExitCode(0);

    expect(Registration::count())->toBe(0)->and($event->fresh()->participant_count)->toBe(0);
});

test('skips the whole event when it does not exist and carries on with the next event', function () {
    $event = makeTestEvent();
    Student::factory()->create(['email' => 'a@example.com']);
    Student::factory()->create(['email' => 'b@example.com']);

    $file = makeRegistrationSheet([
        ['ATB 9', '2019-06-22', 'Auromira', 'A', 'A', 'a@example.com'],
        ['ATB 1', '2019-06-22', 'Auromira', 'B', 'B', 'b@example.com'],
    ]);

    $this->artisan('registrations:import', ['filename' => $file])->assertExitCode(0);

    expect(Registration::count())->toBe(1)
        ->and(Registration::first()->event_id)->toBe($event->id);
});

test('skips on a venue mismatch', function () {
    makeTestEvent('ATB 1', 'Auromira');
    Student::factory()->create(['email' => 'v@example.com']);
    $file = makeRegistrationSheet([['ATB 1', '2019-06-22', 'Somewhere Else', 'V', 'V', 'v@example.com']]);

    $this->artisan('registrations:import', ['filename' => $file])->assertExitCode(0);

    expect(Registration::count())->toBe(0);
});

test('skips on a date mismatch and on a trashed event', function () {
    $event = makeTestEvent();
    Student::factory()->create(['email' => 'd@example.com']);

    $wrongDate = makeRegistrationSheet([['ATB 1', '2019-06-23', 'Auromira', 'D', 'D', 'd@example.com']]);
    $this->artisan('registrations:import', ['filename' => $wrongDate])->assertExitCode(0);
    expect(Registration::count())->toBe(0);

    $event->delete();
    $file = makeRegistrationSheet([['ATB 1', '2019-06-22', 'Auromira', 'D', 'D', 'd@example.com']]);
    $this->artisan('registrations:import', ['filename' => $file])->assertExitCode(0);
    expect(Registration::count())->toBe(0);
});

test('skips unknown emails, trashed students, duplicate rows and existing registrations', function () {
    $event = makeTestEvent();
    $ok = Student::factory()->create(['email' => 'ok@example.com']);
    $trashed = Student::factory()->create(['email' => 'gone@example.com']);
    $trashed->delete();
    $already = Student::factory()->create(['email' => 'already@example.com']);
    Registration::create(['student_id' => $already->id, 'event_id' => $event->id, 'end_status' => 'Registered']);

    $file = makeRegistrationSheet([
        ['ATB 1', '2019-06-22', 'Auromira', 'O', 'K', 'ok@example.com'],
        ['ATB 1', '2019-06-22', 'Auromira', 'O', 'K', 'OK@example.com'],
        ['ATB 1', '2019-06-22', 'Auromira', 'N', 'N', 'nobody@example.com'],
        ['ATB 1', '2019-06-22', 'Auromira', 'G', 'G', 'gone@example.com'],
        ['ATB 1', '2019-06-22', 'Auromira', 'A', 'A', 'already@example.com'],
    ]);

    $this->artisan('registrations:import', ['filename' => $file])->assertExitCode(0);

    expect(Registration::where('event_id', $event->id)->count())->toBe(2)
        ->and(Registration::where('student_id', $ok->id)->count())->toBe(1)
        ->and(Registration::where('student_id', $already->id)->value('end_status'))->toBe('Registered')
        ->and(Student::onlyTrashed()->count())->toBe(1)
        ->and($event->fresh()->participant_count)->toBe(2);
});

test('a name that differs from the database does not block an email match', function () {
    $event = makeTestEvent();
    $s = Student::factory()->create(['email' => 'x@example.com', 'first_name' => 'Real', 'last_name' => 'Name']);
    $file = makeRegistrationSheet([['ATB 1', '2019-06-22', 'Auromira', 'Other', 'Person', 'x@example.com']]);

    $this->artisan('registrations:import', ['filename' => $file])->assertExitCode(0);

    expect(Registration::where('student_id', $s->id)->where('event_id', $event->id)->exists())->toBeTrue();
});

test('accepts a single Full Name column and matches by email', function () {
    $event = makeTestEvent();
    $s = Student::factory()->create(['email' => 'fn@example.com', 'first_name' => 'Ana', 'last_name' => 'Lopez']);
    $file = makeRegistrationSheet(
        [['ATB 1', '2019-06-22', 'Auromira', 'Totally Different Name', 'fn@example.com']],
        ['Course Title', 'Start Date', 'Venue', 'Full Name', 'Email Adress'],
    );

    $this->artisan('registrations:import', ['filename' => $file])
        ->expectsOutputToContain('Warnings: 1')
        ->assertExitCode(0);

    expect(Registration::where('student_id', $s->id)->where('event_id', $event->id)->exists())->toBeTrue();
});

test('full name comparison ignores word order, accents and the first/last split', function () {
    makeTestEvent();
    Student::factory()->create(['email' => 'a@example.com', 'first_name' => 'Claudia', 'last_name' => 'Medina']);
    Student::factory()->create(['email' => 'b@example.com', 'first_name' => 'Martha Lucia', 'last_name' => 'Velasco Morales']);
    Student::factory()->create(['email' => 'c@example.com', 'first_name' => 'Gretha', 'last_name' => 'Tobón']);
    $file = makeRegistrationSheet(
        [
            ['ATB 1', '2019-06-22', 'Auromira', 'Medina Claudia', 'a@example.com'],
            ['ATB 1', '2019-06-22', 'Auromira', 'Martha Lucia Velasco Morales', 'b@example.com'],
            ['ATB 1', '2019-06-22', 'Auromira', 'Gretha Tobon', 'c@example.com'],
        ],
        ['Course Title', 'Start Date', 'Venue', 'Full Name', 'Email Adress'],
    );

    $this->artisan('registrations:import', ['filename' => $file])
        ->expectsOutputToContain('Warnings: 0')
        ->assertExitCode(0);

    expect(Registration::count())->toBe(3);
});

test('fails when neither a full name nor both first and last name columns exist', function () {
    makeTestEvent();
    $file = makeRegistrationSheet(
        [['ATB 1', '2019-06-22', 'Auromira', 'x@example.com']],
        ['Course Title', 'Start Date', 'Venue', 'Email Adress'],
    );

    $this->artisan('registrations:import', ['filename' => $file])->assertExitCode(1);
});

test('--user attributes the activity entries to that user', function () {
    makeTestEvent();
    $user = User::factory()->create(['name' => 'Importer Person']);
    $s = Student::factory()->create(['email' => 'u@example.com']);
    $file = makeRegistrationSheet([['ATB 1', '2019-06-22', 'Auromira', 'U', 'U', 'u@example.com']]);

    $this->artisan('registrations:import', ['filename' => $file, '--user' => 'Importer Person'])->assertExitCode(0);

    $registration = Registration::where('student_id', $s->id)->first();
    $entries = Activity::where('subject_type', Registration::class)
        ->where('subject_id', $registration->id)->get();

    expect($entries->count())->toBeGreaterThanOrEqual(2)
        ->and($entries->pluck('causer_id')->unique()->all())->toBe([$user->id]);
});

test('fails on an unknown user, a missing file or a missing column', function () {
    $file = makeRegistrationSheet([['ATB 1', '2019-06-22', 'Auromira', 'U', 'U', 'u@example.com']]);
    $this->artisan('registrations:import', ['filename' => $file, '--user' => 'Nobody'])->assertExitCode(1);
    $this->artisan('registrations:import', ['filename' => 'does_not_exist.xlsx'])->assertExitCode(1);

    $name = 'test_registrations_'.uniqid().'.xlsx';
    $spreadsheet = new Spreadsheet;
    $spreadsheet->getActiveSheet()->fromArray([['Course Title', 'Start Date'], ['ATB 1', '2019-06-22']]);
    (new Xlsx($spreadsheet))->save(storage_path('app/imports/'.$name));
    $this->artisan('registrations:import', ['filename' => $name])->assertExitCode(1);
});

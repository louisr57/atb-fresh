<?php

use App\Models\Student;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

/**
 * Write a small spreadsheet into storage/app/imports and return its file name.
 *
 * @param  array<int, array<int, string>>  $rows
 */
function makeImportSheet(array $rows): string
{
    $dir = storage_path('app/imports');
    if (! is_dir($dir)) {
        mkdir($dir, 0775, true);
    }

    $name = 'test_country_'.uniqid().'.xlsx';
    $sheet = new Spreadsheet;
    $sheet->getActiveSheet()->fromArray(
        array_merge([['First Name', 'Last Name', 'Email Address']], $rows)
    );
    (new Xlsx($sheet))->save($dir.'/'.$name);

    return $name;
}

afterEach(function () {
    foreach (glob(storage_path('app/imports/test_country_*.xlsx')) ?: [] as $file) {
        @unlink($file);
    }
});

test('import defaults the country to unknown', function () {
    $file = makeImportSheet([['Ana', 'Lopez', 'ana.country@example.com']]);

    $this->artisan('students:import', ['filename' => $file])->assertExitCode(0);

    expect(Student::where('email', 'ana.country@example.com')->value('country'))->toBe('unknown');
});

test('import --country sets the country on new students', function () {
    $file = makeImportSheet([
        ['Ana', 'Lopez', 'ana.mx@example.com'],
        ['Luis', 'Perez', 'luis.mx@example.com'],
    ]);

    $this->artisan('students:import', ['filename' => $file, '--country' => 'Mexico'])->assertExitCode(0);

    expect(Student::whereIn('email', ['ana.mx@example.com', 'luis.mx@example.com'])->pluck('country')->all())
        ->toBe(['Mexico', 'Mexico']);
});

test('import --country does not change existing students', function () {
    $existing = Student::factory()->create(['email' => 'old.mx@example.com', 'country' => 'Chile']);
    $file = makeImportSheet([['Old', 'Student', 'old.mx@example.com']]);

    $this->artisan('students:import', ['filename' => $file, '--country' => 'Mexico'])->assertExitCode(0);

    expect($existing->fresh()->country)->toBe('Chile');

    foreach (glob(storage_path('app/imports/duplicates_*.txt')) ?: [] as $dup) {
        if (str_contains((string) file_get_contents($dup), 'old.mx@example.com')) {
            @unlink($dup);
        }
    }
});

test('import dry run with --country creates nothing', function () {
    $file = makeImportSheet([['Ana', 'Lopez', 'dry.mx@example.com']]);

    $this->artisan('students:import', ['filename' => $file, '--country' => 'Mexico', '--dryrun' => true])->assertExitCode(0);

    expect(Student::where('email', 'dry.mx@example.com')->exists())->toBeFalse();
});

test('import rejects an empty --country', function () {
    $file = makeImportSheet([['Ana', 'Lopez', 'empty.mx@example.com']]);

    $this->artisan('students:import', ['filename' => $file, '--country' => '  '])->assertExitCode(1);

    expect(Student::where('email', 'empty.mx@example.com')->exists())->toBeFalse();
});

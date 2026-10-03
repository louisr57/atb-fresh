<?php

use App\Models\Student;

test('truncate:table refuses to run outside local/testing environments', function () {
    Student::factory()->create();

    app()['env'] = 'production';

    $this->artisan('truncate:table', ['table' => 'students'])
        ->expectsOutputToContain('Refusing to run')
        ->assertExitCode(1);

    expect(Student::count())->toBe(1);

    app()['env'] = 'testing';
});

test('truncate:table requires confirmation even in allowed environments', function () {
    Student::factory()->create();

    $this->artisan('truncate:table', ['table' => 'students'])
        ->expectsConfirmation(
            "This will permanently truncate the 'students' table. Are you sure?",
            'no'
        )
        ->expectsOutputToContain('Operation cancelled.')
        ->assertExitCode(0);

    expect(Student::count())->toBe(1);
});

test('truncate:table truncates the table when confirmed in an allowed environment', function () {
    Student::factory()->create();

    $this->artisan('truncate:table', ['table' => 'students'])
        ->expectsConfirmation(
            "This will permanently truncate the 'students' table. Are you sure?",
            'yes'
        )
        ->assertExitCode(0);

    expect(Student::count())->toBe(0);
});

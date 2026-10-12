<?php

use App\Models\Student;
use App\Services\MysqlDumpReader;
use Illuminate\Support\Facades\DB;

/**
 * Write a tiny mysqldump-style file containing students + activity_log rows.
 *
 * @param  array<int, string>  $studentTuples
 * @param  array<int, string>  $activityTuples
 */
function writeTestDump(array $studentTuples, array $activityTuples): string
{
    $activityColumns = ['id', 'log_name', 'description', 'subject_type', 'event', 'subject_id', 'causer_type', 'causer_id', 'properties', 'batch_uuid', 'created_at', 'updated_at'];

    $sql = implode("\n", [
        '-- MySQL dump 10.13',
        "/*!40103 SET TIME_ZONE='+00:00' */;",
        'CREATE TABLE `students` (',
        '  `id` bigint unsigned NOT NULL AUTO_INCREMENT,',
        '  `first_name` varchar(255) NOT NULL,',
        '  `last_name` varchar(255) NOT NULL,',
        '  `email` varchar(255) NOT NULL,',
        '  `created_at` timestamp NULL DEFAULT NULL,',
        '  `updated_at` timestamp NULL DEFAULT NULL,',
        '  PRIMARY KEY (`id`)',
        ') ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4;',
        'INSERT INTO `students` VALUES '.implode(',', $studentTuples).';',
        'CREATE TABLE `activity_log` (',
        ...array_map(fn (string $column) => "  `{$column}` text,", $activityColumns),
        '  PRIMARY KEY (`id`)',
        ') ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4;',
        'INSERT INTO `activity_log` VALUES '.implode(',', $activityTuples).';',
        '-- Dump completed on 2025-02-02 10:47:33',
    ]);

    $path = tempnam(sys_get_temp_dir(), 'dump');
    file_put_contents($path, $sql);

    return $path;
}

/** Insert live rows directly (bypassing model events, so no extra activity entries). */
function seedLiveStudent(int $id, string $first, string $createdAt): void
{
    DB::table('students')->insert([
        'id' => $id, 'first_name' => $first, 'last_name' => 'Test', 'email' => strtolower($first).'@x.test',
        'address' => 'a', 'city' => 'c', 'state' => 's', 'country' => 'India', 'post_code' => '605101',
        'created_at' => $createdAt, 'updated_at' => $createdAt,
    ]);
}

function seedLiveActivity(int $id, string $description, ?int $subjectId, string $createdAt, ?string $event = null): void
{
    DB::table('activity_log')->insert([
        'id' => $id, 'log_name' => 'student', 'description' => $description,
        'subject_type' => Student::class, 'event' => $event, 'subject_id' => $subjectId,
        'causer_type' => 'App\Models\User', 'causer_id' => 2, 'properties' => '{"attributes": {"first_name": "Ann"}}',
        'batch_uuid' => null, 'created_at' => $createdAt, 'updated_at' => $createdAt,
    ]);
}

function standardTestDump(): string
{
    $props = '\'{\"attributes\": {\"first_name\": \"Ann\"}}\'';

    return writeTestDump(
        [
            "(1,'Ann','Test','ann@x.test','2025-01-29 10:00:00','2025-01-29 10:00:00')",
            "(2,'Bob','Test','bob@x.test','2025-01-29 11:00:00','2025-01-29 11:00:00')",
        ],
        [
            "(1,'student','Student has been created','App\\\\Models\\\\Student','created',1,'App\\\\Models\\\\User',2,{$props},NULL,'2025-01-29 10:00:00','2025-01-29 10:00:00')",
            "(2,'student','Student has been created','App\\\\Models\\\\Student','created',2,'App\\\\Models\\\\User',2,{$props},NULL,'2025-01-29 11:00:00','2025-01-29 11:00:00')",
        ]
    );
}

test('reports no rollback when every dump row is still present and unchanged', function () {
    seedLiveStudent(1, 'Ann', '2025-01-29 10:00:00');
    seedLiveStudent(2, 'Bob', '2025-01-29 11:00:00');
    seedLiveActivity(1, 'Student has been created', 1, '2025-01-29 10:00:00', 'created');
    seedLiveActivity(2, 'Student has been created', 2, '2025-01-29 11:00:00', 'created');
    seedLiveActivity(3, 'Student profile viewed', 1, '2025-03-01 09:00:00');

    $this->artisan('audit:compare-dump', ['dump' => standardTestDump()])
        ->expectsOutputToContain('Every activity_log entry in the dump is still present and unchanged.')
        ->expectsOutputToContain('No sign that the database was rolled back')
        ->doesntExpectOutputToContain('ALERT:')
        ->assertSuccessful();
});

test('flags reissued activity_log ids as a rollback to before the dump', function () {
    seedLiveStudent(1, 'Ann', '2025-01-29 10:00:00');
    seedLiveStudent(2, 'Bob', '2025-01-29 11:00:00');
    // After a restore to an older point, new entries reuse id 2.
    seedLiveActivity(1, 'Student has been created', 1, '2025-01-29 10:00:00', 'created');
    seedLiveActivity(2, 'Student profile viewed', 1, '2025-02-05 08:00:00');

    $this->artisan('audit:compare-dump', ['dump' => standardTestDump()])
        ->expectsOutputToContain('[activity_log] ID REUSED #2')
        ->expectsOutputToContain('1 activity_log id(s) from the dump now hold DIFFERENT entries')
        ->doesntExpectOutputToContain('No sign that the database was rolled back')
        ->assertSuccessful();
});

test('flags a missing row with no logged deletion, and accepts one whose deletion was logged', function () {
    seedLiveStudent(1, 'Ann', '2025-01-29 10:00:00');
    seedLiveActivity(1, 'Student has been created', 1, '2025-01-29 10:00:00', 'created');
    seedLiveActivity(2, 'Student has been created', 2, '2025-01-29 11:00:00', 'created');

    $this->artisan('audit:compare-dump', ['dump' => standardTestDump(), '--table' => ['students']])
        ->expectsOutputToContain('[students] MISSING #2')
        ->expectsOutputToContain('1 students row(s) from the dump are gone with no logged deletion')
        ->assertSuccessful();

    seedLiveActivity(3, 'Student has been deleted', 2, '2025-03-01 09:00:00', 'deleted');

    $this->artisan('audit:compare-dump', ['dump' => standardTestDump(), '--table' => ['students']])
        ->expectsOutputToContain('deletion logged at 2025-03-01 09:00:00')
        ->doesntExpectOutputToContain('ALERT:')
        ->assertSuccessful();
});

test('fails cleanly when the dump file does not exist', function () {
    $this->artisan('audit:compare-dump', ['dump' => 'does/not/exist.sql'])
        ->expectsOutputToContain('Could not read the dump')
        ->assertFailed();
});

test('dump reader unescapes strings, keeps NULLs and reads metadata', function () {
    $reader = MysqlDumpReader::fromString(implode("\n", [
        "/*!40103 SET TIME_ZONE='+00:00' */;",
        'CREATE TABLE `t` (',
        '  `id` int NOT NULL,',
        '  `note` text,',
        '  `extra` text,',
        ') ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4;',
        "INSERT INTO `t` VALUES (1,'it\\'s, (tricky)\\nline',NULL),(7,'O''Brien','a\\\\b');",
        '-- Dump completed on 2025-02-02 10:47:33',
    ]));

    expect($reader->completedAt())->toBe('2025-02-02 10:47:33')
        ->and($reader->timeZone())->toBe('+00:00')
        ->and($reader->autoIncrement('t'))->toBe(8)
        ->and($reader->rows('t'))->toBe([
            ['id' => '1', 'note' => "it's, (tricky)\nline", 'extra' => null],
            ['id' => '7', 'note' => "O'Brien", 'extra' => 'a\\b'],
        ]);
});

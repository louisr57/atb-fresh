<?php

use App\Services\ActivityJournal\ActivityJournalExporter;
use App\Services\ActivityJournal\LocalJournalStore;
use Illuminate\Support\Facades\DB;

function journalDir(): string
{
    $dir = sys_get_temp_dir().DIRECTORY_SEPARATOR.'journal_'.uniqid();
    mkdir($dir);

    return $dir;
}

function insertJournalActivity(int $id, string $description = 'created', ?string $createdAt = null): void
{
    DB::table('activity_log')->insert([
        'id' => $id, 'log_name' => 'student', 'description' => $description,
        'subject_type' => 'App\Models\Student', 'event' => 'created', 'subject_id' => $id,
        'causer_type' => 'App\Models\User', 'causer_id' => 2,
        'properties' => '{"attributes": {"first_name": "Zoë"}}', 'batch_uuid' => null,
        'created_at' => $createdAt ?? '2025-02-01 10:00:00', 'updated_at' => $createdAt ?? '2025-02-01 10:00:00',
    ]);
}

function journalExporter(string $dir, int $batch = 5000, int $settle = 0): ActivityJournalExporter
{
    return new ActivityJournalExporter(new LocalJournalStore($dir), $batch, $settle);
}

it('exports new rows and then does nothing when there is nothing new', function () {
    $dir = journalDir();
    foreach ([1, 2, 3] as $id) {
        insertJournalActivity($id);
    }

    $first = journalExporter($dir)->export();
    expect($first['exported_rows'])->toBe(3)
        ->and($first['files'])->toBe(1)
        ->and($first['last_exported_id'])->toBe(3)
        ->and($first['alerts'])->toBe([]);

    expect(file_exists($dir.'/activity_0000000001-0000000003.jsonl.gz'))->toBeTrue()
        ->and(file_exists($dir.'/activity_0000000001-0000000003.manifest.json'))->toBeTrue();

    $lines = explode("\n", rtrim(gzdecode(file_get_contents($dir.'/activity_0000000001-0000000003.jsonl.gz')), "\n"));
    expect($lines)->toHaveCount(3)
        ->and(json_decode($lines[0], true)['properties'])->toContain('Zoë');

    $second = journalExporter($dir)->export();
    expect($second['exported_rows'])->toBe(0)->and($second['files'])->toBe(0);
});

it('appends only rows added since the last export and chains the manifests', function () {
    $dir = journalDir();
    insertJournalActivity(1);
    journalExporter($dir)->export();

    insertJournalActivity(2);
    insertJournalActivity(3);
    $result = journalExporter($dir)->export();

    expect($result['exported_rows'])->toBe(2);

    $one = json_decode(file_get_contents($dir.'/activity_0000000001-0000000001.manifest.json'), true);
    $two = json_decode(file_get_contents($dir.'/activity_0000000002-0000000003.manifest.json'), true);
    expect($two['previous_chain_hash'])->toBe($one['chain_hash'])
        ->and($one['previous_chain_hash'])->toBe(ActivityJournalExporter::GENESIS_HASH);
});

it('splits large exports into batches', function () {
    $dir = journalDir();
    foreach (range(1, 5) as $id) {
        insertJournalActivity($id);
    }

    $result = journalExporter($dir, batch: 2)->export();

    expect($result['exported_rows'])->toBe(5)->and($result['files'])->toBe(3);
    expect(file_exists($dir.'/activity_0000000005-0000000005.manifest.json'))->toBeTrue();
});

it('leaves very recent rows for the next run', function () {
    $dir = journalDir();
    insertJournalActivity(1);
    insertJournalActivity(2, 'created', now()->format('Y-m-d H:i:s'));

    $result = journalExporter($dir, settle: 300)->export();

    expect($result['exported_rows'])->toBe(1)->and($result['last_exported_id'])->toBe(1);
});

it('raises an alert and exports nothing when the database has gone back in time', function () {
    $dir = journalDir();
    foreach ([1, 2, 3] as $id) {
        insertJournalActivity($id);
    }
    journalExporter($dir)->export();

    DB::table('activity_log')->where('id', '>', 1)->delete();
    insertJournalActivity(2, 'something else');

    $result = journalExporter($dir)->export();

    expect($result['alerts'])->toHaveCount(1)
        ->and($result['alerts'][0])->toContain('BACK in time')
        ->and($result['exported_rows'])->toBe(0);
});

it('raises an alert when the newest journaled row was replaced by a different one', function () {
    $dir = journalDir();
    foreach ([1, 2, 3] as $id) {
        insertJournalActivity($id);
    }
    journalExporter($dir)->export();

    DB::table('activity_log')->where('id', 3)->update(['description' => 'a different history']);
    insertJournalActivity(4);

    $result = journalExporter($dir)->export();

    expect($result['alerts'])->toHaveCount(1)
        ->and($result['alerts'][0])->toContain('differs from the journal copy')
        ->and(file_exists($dir.'/activity_0000000004-0000000004.jsonl.gz'))->toBeFalse();
});

it('raises an alert when the newest journaled row has disappeared', function () {
    $dir = journalDir();
    foreach ([1, 2] as $id) {
        insertJournalActivity($id);
    }
    journalExporter($dir)->export();

    DB::table('activity_log')->where('id', 2)->delete();
    insertJournalActivity(5);

    $result = journalExporter($dir)->export();

    expect($result['alerts'])->toHaveCount(1)->and($result['alerts'][0])->toContain('missing from the database');
});

it('repairs a missing manifest left by an interrupted run', function () {
    $dir = journalDir();
    insertJournalActivity(1);
    insertJournalActivity(2);
    journalExporter($dir)->export();

    unlink($dir.'/activity_0000000001-0000000002.manifest.json');
    insertJournalActivity(3);

    $result = journalExporter($dir)->export();

    expect($result['alerts'])->toBe([])
        ->and($result['exported_rows'])->toBe(1)
        ->and(file_exists($dir.'/activity_0000000001-0000000002.manifest.json'))->toBeTrue()
        ->and(json_decode(file_get_contents($dir.'/activity_0000000001-0000000002.manifest.json'), true)['recovered'])->toBeTrue();
});

it('refuses to overwrite an existing journal file', function () {
    $dir = journalDir();
    $store = new LocalJournalStore($dir);
    $store->put('a.txt', 'one');

    expect(fn () => $store->put('a.txt', 'two'))->toThrow(RuntimeException::class)
        ->and(file_get_contents($dir.'/a.txt'))->toBe('one');
    expect(fn () => $store->put('../escape.txt', 'x'))->toThrow(RuntimeException::class);
});

it('runs through the artisan command with the local store and supports --dry-run', function () {
    $dir = journalDir();
    config([
        'activity-journal.store' => 'local',
        'activity-journal.local_path' => $dir,
        'activity-journal.settle_seconds' => 0,
    ]);
    insertJournalActivity(1);
    insertJournalActivity(2);

    $this->artisan('activity:export-journal', ['--dry-run' => true])
        ->expectsOutputToContain('2 row(s)')
        ->assertExitCode(0);
    expect(scandir($dir))->toBe(['.', '..']);

    $this->artisan('activity:export-journal')->expectsOutputToContain('Exported 2 row(s)')->assertExitCode(0);
    expect(file_exists($dir.'/activity_0000000001-0000000002.jsonl.gz'))->toBeTrue();

    DB::table('activity_log')->delete();
    $this->artisan('activity:export-journal')->assertExitCode(1);
});

it('does nothing when the journal is not configured', function () {
    config(['activity-journal.store' => null]);

    $this->artisan('activity:export-journal')->expectsOutputToContain('not configured')->assertExitCode(0);
});

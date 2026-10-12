<?php

use App\Services\ActivityJournal\ActivityJournalExporter;
use Illuminate\Support\Facades\DB;

function stateRow(int $id): void
{
    DB::table('activity_log')->insert([
        'id' => $id, 'log_name' => 'student', 'description' => 'created',
        'subject_type' => 'App\Models\Student', 'event' => 'created', 'subject_id' => $id,
        'causer_type' => 'App\Models\User', 'causer_id' => 2,
        'properties' => '{"attributes": {"first_name": "Zoë"}}', 'batch_uuid' => null,
        'created_at' => '2025-02-01 10:00:00', 'updated_at' => '2025-02-01 10:00:00',
    ]);
}

it('is disabled (404) when no token is configured', function () {
    config(['activity-journal.state_token' => null]);

    $this->getJson('/monitoring/state')->assertNotFound();
    $this->getJson('/monitoring/state', ['Authorization' => 'Bearer '])->assertNotFound();
});

it('rejects a missing or wrong token', function () {
    config(['activity-journal.state_token' => 'secret-token']);

    $this->getJson('/monitoring/state')->assertUnauthorized();
    $this->getJson('/monitoring/state', ['Authorization' => 'Bearer wrong'])->assertUnauthorized();
});

it('reports the highest activity id, count and row hash', function () {
    config(['activity-journal.state_token' => 'secret-token']);
    stateRow(1);
    stateRow(2);

    $expected = hash('sha256', ActivityJournalExporter::serializeRow(DB::table('activity_log')->where('id', 2)->first()));

    $this->getJson('/monitoring/state', ['Authorization' => 'Bearer secret-token'])
        ->assertOk()
        ->assertHeader('Cache-Control', 'no-store, private')
        ->assertJsonPath('activity_log.max_id', 2)
        ->assertJsonPath('activity_log.row_count', 2)
        ->assertJsonPath('activity_log.max_id_row_sha256', $expected)
        ->assertJsonMissingPath('requested')
        ->assertJsonMissing(['first_name' => 'Zoë']);
});

it('reports empty tables without error', function () {
    config(['activity-journal.state_token' => 'secret-token']);

    $this->getJson('/monitoring/state', ['Authorization' => 'Bearer secret-token'])
        ->assertOk()
        ->assertJsonPath('activity_log.max_id', null)
        ->assertJsonPath('activity_log.row_count', 0);
});

it('verifies a specific row by id', function () {
    config(['activity-journal.state_token' => 'secret-token']);
    stateRow(5);

    $expected = hash('sha256', ActivityJournalExporter::serializeRow(DB::table('activity_log')->where('id', 5)->first()));
    $headers = ['Authorization' => 'Bearer secret-token'];

    $this->getJson('/monitoring/state?id=5', $headers)
        ->assertJsonPath('requested.exists', true)
        ->assertJsonPath('requested.row_sha256', $expected);

    $this->getJson('/monitoring/state?id=99', $headers)
        ->assertJsonPath('requested.exists', false)
        ->assertJsonPath('requested.row_sha256', null);
});

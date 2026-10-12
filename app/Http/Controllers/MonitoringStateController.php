<?php

namespace App\Http\Controllers;

use App\Services\ActivityJournal\ActivityJournalExporter;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Token-protected, read-only state report for the off-droplet rollback checker.
 *
 * Exposes only ids, counts, timestamps and hashes - never activity_log content.
 * Disabled (404) until ACTIVITY_JOURNAL_STATE_TOKEN is set.
 */
class MonitoringStateController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $token = (string) config('activity-journal.state_token');
        abort_if($token === '', 404);

        $given = (string) $request->bearerToken();
        abort_unless($given !== '' && hash_equals($token, $given), 401);

        $table = DB::connection(config('activitylog.database_connection'))
            ->table(config('activitylog.table_name', 'activity_log'));

        $maxId = $table->max('id');
        $newest = $maxId === null ? null : (clone $table)->where('id', $maxId)->first();

        $payload = [
            'server_time' => gmdate('c'),
            'activity_log' => [
                'max_id' => $maxId === null ? null : (int) $maxId,
                'row_count' => (clone $table)->count(),
                'max_id_row_sha256' => $newest === null ? null : hash('sha256', ActivityJournalExporter::serializeRow($newest)),
                'max_id_created_at' => $newest->created_at ?? null,
            ],
            'commit' => $this->commit(),
        ];

        // ?id=N lets the checker verify the journal's newest row (from its manifest) against the live row.
        if ($request->filled('id') && ctype_digit((string) $request->query('id'))) {
            $id = (int) $request->query('id');
            $row = (clone $table)->where('id', $id)->first();

            $payload['requested'] = [
                'id' => $id,
                'exists' => $row !== null,
                'row_sha256' => $row === null ? null : hash('sha256', ActivityJournalExporter::serializeRow($row)),
            ];
        }

        return response()->json($payload)->header('Cache-Control', 'no-store');
    }

    private function commit(): ?string
    {
        $head = base_path('.git/HEAD');

        if (! is_readable($head)) {
            return null;
        }

        $ref = trim((string) file_get_contents($head));

        if (! str_starts_with($ref, 'ref: ')) {
            return substr($ref, 0, 12);
        }

        $refFile = base_path('.git/'.substr($ref, 5));

        return is_readable($refFile) ? substr(trim((string) file_get_contents($refFile)), 0, 12) : null;
    }
}

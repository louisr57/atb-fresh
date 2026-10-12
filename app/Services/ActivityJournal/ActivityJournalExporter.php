<?php

namespace App\Services\ActivityJournal;

use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
use RuntimeException;

/**
 * Copies new activity_log rows into an append-only journal.
 *
 * Every export writes two files, named after the id range they cover:
 *   activity_<first>-<last>.jsonl.gz       one JSON object per row
 *   activity_<first>-<last>.manifest.json  counts, checksums and a hash chain
 *
 * The manifest chain_hash covers the previous manifest's chain_hash, so a
 * missing or altered file in the middle of the journal is detectable.
 * Nothing in the journal is ever overwritten or deleted by this class.
 */
class ActivityJournalExporter
{
    public const GENESIS_HASH = '0000000000000000000000000000000000000000000000000000000000000000';

    public function __construct(
        private readonly JournalStore $store,
        private readonly int $batchSize = 5000,
        private readonly int $settleSeconds = 60,
    ) {}

    /**
     * @return array{exported_rows: int, files: int, last_exported_id: int, pending_rows: int, alerts: array<int, string>}
     */
    public function export(bool $dryRun = false): array
    {
        $names = $this->store->names();
        $manifests = $this->ranges($names, '/^activity_(\d{10})-(\d{10})\.manifest\.json$/');
        $dataFiles = $this->ranges($names, '/^activity_(\d{10})-(\d{10})\.jsonl\.gz$/');

        if (! $dryRun) {
            $manifests = $this->recoverMissingManifests($manifests, $dataFiles);
        }

        $last = $manifests === [] ? null : $this->readManifest($manifests[array_key_last($manifests)]['name']);
        $lastId = $last['last_id'] ?? 0;
        $chain = $last['chain_hash'] ?? self::GENESIS_HASH;

        $alerts = $last === null ? [] : $this->integrityAlerts($last);

        $result = ['exported_rows' => 0, 'files' => 0, 'last_exported_id' => $lastId, 'pending_rows' => 0, 'alerts' => $alerts];

        if ($dryRun) {
            $result['pending_rows'] = $this->pendingQuery($lastId)->count();

            return $result;
        }

        if ($alerts !== []) {
            // Never extend a journal whose history no longer matches the database.
            return $result;
        }

        while (true) {
            $rows = $this->pendingQuery($lastId)->orderBy('id')->limit($this->batchSize)->get();

            if ($rows->isEmpty()) {
                break;
            }

            $lines = $rows->map(fn ($row) => $this->serialize($row))->all();
            $content = implode("\n", $lines)."\n";
            $firstId = (int) $rows->first()->id;
            $newLastId = (int) $rows->last()->id;

            $manifest = $this->buildManifest($firstId, $newLastId, $lines, $content, $chain);

            // Data first, manifest second: a crash in between is repaired on the next run.
            $this->store->put($manifest['data_file'], (string) gzencode($content, 9));
            $this->store->put($this->manifestName($firstId, $newLastId), $this->encodeManifest($manifest));

            $chain = $manifest['chain_hash'];
            $lastId = $newLastId;
            $result['exported_rows'] += count($lines);
            $result['files']++;
            $result['last_exported_id'] = $lastId;

            if ($rows->count() < $this->batchSize) {
                break;
            }
        }

        return $result;
    }

    /**
     * @param  array<int, string>  $lines
     * @return array<string, mixed>
     */
    private function buildManifest(int $firstId, int $lastId, array $lines, string $content, string $chain): array
    {
        return [
            'first_id' => $firstId,
            'last_id' => $lastId,
            'rows' => count($lines),
            'content_sha256' => hash('sha256', $content),
            'last_row_sha256' => hash('sha256', $lines[array_key_last($lines)]),
            'previous_chain_hash' => $chain,
            'chain_hash' => hash('sha256', $chain.hash('sha256', $content)),
            'exported_at' => gmdate('c'),
            'data_file' => $this->dataName($firstId, $lastId),
        ];
    }

    /** @param  array<string, mixed>  $manifest */
    private function encodeManifest(array $manifest): string
    {
        return json_encode($manifest, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)."\n";
    }

    /**
     * @param  array<string, mixed>  $last  newest manifest
     * @return array<int, string>
     */
    private function integrityAlerts(array $last): array
    {
        $lastId = (int) $last['last_id'];

        $liveMax = (int) ($this->table()->max('id') ?? 0);
        if ($liveMax < $lastId) {
            return ["ALERT: the live activity_log highest id is #{$liveMax} but the journal already holds #{$lastId}. The database has gone BACK in time (restore or rollback)."];
        }

        $row = $this->table()->where('id', $lastId)->first();
        if ($row === null) {
            return ["ALERT: activity_log row #{$lastId} is in the journal but is missing from the database (restore, or activitylog:clean)."];
        }

        if (hash('sha256', $this->serialize($row)) !== $last['last_row_sha256']) {
            return ["ALERT: activity_log row #{$lastId} differs from the journal copy. The database may have been restored and its ids reused."];
        }

        return [];
    }

    /** @return Builder */
    private function pendingQuery(int $afterId)
    {
        $cutoff = now()->subSeconds($this->settleSeconds)->format('Y-m-d H:i:s');

        return $this->table()
            ->where('id', '>', $afterId)
            ->where(fn ($query) => $query->whereNull('created_at')->orWhere('created_at', '<=', $cutoff));
    }

    /** @return Builder */
    private function table()
    {
        return DB::connection(config('activitylog.database_connection'))
            ->table(config('activitylog.table_name', 'activity_log'));
    }

    private function serialize(object $row): string
    {
        return self::serializeRow($row);
    }

    /** The exact JSON line written to the journal; its sha256 is the manifest's last_row_sha256. */
    public static function serializeRow(object $row): string
    {
        $json = json_encode((array) $row, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_INVALID_UTF8_SUBSTITUTE);

        if ($json === false) {
            throw new RuntimeException('Cannot encode activity_log row #'.($row->id ?? '?').' as JSON.');
        }

        return $json;
    }

    /**
     * Rebuild manifests for data files left behind by a run that stopped between the two uploads.
     *
     * @param  array<int, array{name: string, first: int, last: int}>  $manifests
     * @param  array<int, array{name: string, first: int, last: int}>  $dataFiles
     * @return array<int, array{name: string, first: int, last: int}>
     */
    private function recoverMissingManifests(array $manifests, array $dataFiles): array
    {
        $lastManifestId = $manifests === [] ? 0 : $manifests[array_key_last($manifests)]['last'];
        $chain = $manifests === [] ? self::GENESIS_HASH : $this->readManifest($manifests[array_key_last($manifests)]['name'])['chain_hash'];

        foreach ($dataFiles as $file) {
            if ($file['first'] <= $lastManifestId) {
                continue;
            }

            $content = gzdecode($this->store->read($file['name']));
            if ($content === false || $content === '') {
                throw new RuntimeException("Journal data file {$file['name']} is unreadable; fix it by hand before exporting again.");
            }

            $lines = explode("\n", rtrim($content, "\n"));
            $manifest = $this->buildManifest($file['first'], $file['last'], $lines, $content, $chain);
            $manifest['recovered'] = true;

            $name = $this->manifestName($file['first'], $file['last']);
            $this->store->put($name, $this->encodeManifest($manifest));

            $manifests[] = ['name' => $name, 'first' => $file['first'], 'last' => $file['last']];
            $chain = $manifest['chain_hash'];
            $lastManifestId = $file['last'];
        }

        return $manifests;
    }

    /** @return array<string, mixed> */
    private function readManifest(string $name): array
    {
        $data = json_decode($this->store->read($name), true);

        if (! is_array($data) || ! isset($data['last_id'], $data['chain_hash'], $data['last_row_sha256'])) {
            throw new RuntimeException("Journal manifest {$name} is unreadable or incomplete.");
        }

        $data['last_id'] = (int) $data['last_id'];

        return $data;
    }

    /**
     * @param  array<int, string>  $names
     * @return array<int, array{name: string, first: int, last: int}>
     */
    private function ranges(array $names, string $pattern): array
    {
        $ranges = [];

        foreach ($names as $name) {
            if (preg_match($pattern, $name, $matches)) {
                $ranges[] = ['name' => $name, 'first' => (int) $matches[1], 'last' => (int) $matches[2]];
            }
        }

        usort($ranges, fn (array $a, array $b) => $a['first'] <=> $b['first']);

        return $ranges;
    }

    private function dataName(int $first, int $last): string
    {
        return sprintf('activity_%010d-%010d.jsonl.gz', $first, $last);
    }

    private function manifestName(int $first, int $last): string
    {
        return sprintf('activity_%010d-%010d.manifest.json', $first, $last);
    }
}

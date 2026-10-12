<?php

namespace App\Services\ActivityJournal;

use Illuminate\Support\Facades\Process;
use RuntimeException;

/** Journal stored on any rclone remote (Backblaze B2 in production). */
class RcloneJournalStore implements JournalStore
{
    private readonly string $remote;

    public function __construct(private readonly string $binary, string $remote)
    {
        $this->remote = rtrim($remote, '/');
    }

    public function names(): array
    {
        $result = Process::timeout(120)->run([$this->binary, 'lsf', $this->remote, '--files-only']);

        if ($result->failed()) {
            throw new RuntimeException('rclone lsf failed: '.trim($result->errorOutput()));
        }

        return array_values(array_filter(
            array_map('trim', explode("\n", $result->output())),
            fn (string $name) => $name !== ''
        ));
    }

    public function read(string $name): string
    {
        $result = Process::timeout(120)->run([$this->binary, 'cat', $this->target($name)]);

        if ($result->failed()) {
            throw new RuntimeException("rclone cat failed for {$name}: ".trim($result->errorOutput()));
        }

        return $result->output();
    }

    public function put(string $name, string $contents): void
    {
        $temporary = tempnam(sys_get_temp_dir(), 'journal');

        if ($temporary === false || file_put_contents($temporary, $contents) === false) {
            throw new RuntimeException("Cannot stage journal file {$name} for upload.");
        }

        try {
            // --immutable makes rclone fail instead of replacing a file that already exists.
            $result = Process::timeout(300)->run([
                $this->binary, 'copyto', '--immutable', $temporary, $this->target($name),
            ]);
        } finally {
            @unlink($temporary);
        }

        if ($result->failed()) {
            throw new RuntimeException("rclone copyto failed for {$name}: ".trim($result->errorOutput()));
        }
    }

    private function target(string $name): string
    {
        if ($name !== basename($name) || $name === '' || $name === '.' || $name === '..') {
            throw new RuntimeException("Invalid journal file name {$name}.");
        }

        return $this->remote.'/'.$name;
    }
}

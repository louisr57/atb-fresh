<?php

namespace App\Services\ActivityJournal;

/**
 * Append-only storage for journal files. Implementations must never overwrite
 * an existing file.
 */
interface JournalStore
{
    /**
     * Names of every file in the journal.
     *
     * @return array<int, string>
     */
    public function names(): array;

    public function read(string $name): string;

    /**
     * @throws \RuntimeException when the file already exists or cannot be written
     */
    public function put(string $name, string $contents): void;
}

<?php

namespace App\Services\ActivityJournal;

use RuntimeException;

class LocalJournalStore implements JournalStore
{
    public function __construct(private readonly string $directory) {}

    public function names(): array
    {
        if (! is_dir($this->directory)) {
            return [];
        }

        $names = scandir($this->directory) ?: [];

        return array_values(array_filter(
            $names,
            fn (string $name) => is_file($this->directory.DIRECTORY_SEPARATOR.$name)
        ));
    }

    public function read(string $name): string
    {
        $contents = @file_get_contents($this->path($name));

        if ($contents === false) {
            throw new RuntimeException("Cannot read journal file {$name}.");
        }

        return $contents;
    }

    public function put(string $name, string $contents): void
    {
        if (! is_dir($this->directory) && ! mkdir($this->directory, 0700, true) && ! is_dir($this->directory)) {
            throw new RuntimeException("Cannot create journal directory {$this->directory}.");
        }

        $path = $this->path($name);

        if (file_exists($path)) {
            throw new RuntimeException("Refusing to overwrite existing journal file {$name}.");
        }

        if (file_put_contents($path, $contents) === false) {
            throw new RuntimeException("Cannot write journal file {$name}.");
        }
    }

    private function path(string $name): string
    {
        if ($name !== basename($name) || $name === '' || $name === '.' || $name === '..') {
            throw new RuntimeException("Invalid journal file name {$name}.");
        }

        return $this->directory.DIRECTORY_SEPARATOR.$name;
    }
}

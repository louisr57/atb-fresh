<?php

namespace App\Services;

use RuntimeException;

/**
 * Minimal, read-only parser for plain-text mysqldump files in the default
 * "extended insert" format (one `INSERT INTO `t` VALUES (...),(...);` line per table).
 *
 * For each table it extracts the column names (from CREATE TABLE), the AUTO_INCREMENT
 * value at dump time, and the row data. Nothing is ever executed against a database.
 */
class MysqlDumpReader
{
    /** @var array<string, array{columns: array<int, string>, auto_increment: int|null, rows: array<int, array<string, string|null>>}> */
    private array $tables = [];

    private ?string $completedAt = null;

    private ?string $timeZone = null;

    private ?string $currentTable = null;

    public static function fromFile(string $path): self
    {
        if (! is_file($path) || ! is_readable($path)) {
            throw new RuntimeException("Dump file not found or not readable: {$path}");
        }

        $handle = fopen($path, 'r');
        if ($handle === false) {
            throw new RuntimeException("Unable to open dump file: {$path}");
        }

        $reader = new self;

        try {
            while (($line = fgets($handle)) !== false) {
                $reader->consumeLine(rtrim($line, "\r\n"));
            }
        } finally {
            fclose($handle);
        }

        return $reader;
    }

    public static function fromString(string $contents): self
    {
        $reader = new self;

        foreach (preg_split('/\r\n|\n|\r/', $contents) ?: [] as $line) {
            $reader->consumeLine($line);
        }

        return $reader;
    }

    private function consumeLine(string $line): void
    {
        if (preg_match('/^-- Dump completed on (.+)$/', $line, $m)) {
            $this->completedAt = trim($m[1]);

            return;
        }

        if (preg_match("/^\\/\\*!\\d+ SET TIME_ZONE='([^']+)' \\*\\/;/", $line, $m)) {
            $this->timeZone = $m[1];

            return;
        }

        if (preg_match('/^CREATE TABLE `([^`]+)`/', $line, $m)) {
            $this->currentTable = $m[1];
            $this->tables[$m[1]] ??= ['columns' => [], 'auto_increment' => null, 'rows' => []];
            $this->tables[$m[1]]['columns'] = [];

            return;
        }

        if ($this->currentTable !== null) {
            if (preg_match('/^\s+`([^`]+)`\s/', $line, $m)) {
                $this->tables[$this->currentTable]['columns'][] = $m[1];

                return;
            }

            if (str_starts_with($line, ')')) {
                if (preg_match('/AUTO_INCREMENT=(\d+)/', $line, $m)) {
                    $this->tables[$this->currentTable]['auto_increment'] = (int) $m[1];
                }
                $this->currentTable = null;
            }

            return;
        }

        if (preg_match('/^INSERT INTO `([^`]+)`(?: \(([^)]*)\))? VALUES /', $line, $m)) {
            $this->addRows($m[1], $m[2] ?? '', substr($line, strlen($m[0])));
        }
    }

    private function addRows(string $table, string $columnList, string $valuesSql): void
    {
        $this->tables[$table] ??= ['columns' => [], 'auto_increment' => null, 'rows' => []];

        $columns = $columnList !== ''
            ? array_map(fn (string $c) => trim($c, ' `'), explode(',', $columnList))
            : $this->tables[$table]['columns'];

        foreach ($this->parseValues($valuesSql) as $tuple) {
            if (count($tuple) !== count($columns)) {
                throw new RuntimeException(sprintf(
                    'Column count mismatch in table %s: expected %d values, found %d.',
                    $table,
                    count($columns),
                    count($tuple)
                ));
            }
            $this->tables[$table]['rows'][] = array_combine($columns, $tuple);
        }
    }

    /**
     * Parse the `(...),(...);` portion of an extended INSERT into arrays of values.
     * Strings are unescaped, NULL becomes null, and numbers are kept as strings.
     *
     * @return array<int, array<int, string|null>>
     */
    public function parseValues(string $sql): array
    {
        $tuples = [];
        $length = strlen($sql);
        $i = 0;

        while ($i < $length) {
            if ($sql[$i] !== '(') {
                $i++; // separators between tuples and the terminating ";"

                continue;
            }

            $i++;
            $tuple = [];

            while (true) {
                while ($i < $length && ctype_space($sql[$i])) {
                    $i++;
                }
                if (substr($sql, $i, 8) === '_binary ') {
                    $i += 8;
                }
                if ($i >= $length) {
                    throw new RuntimeException('Unexpected end of INSERT statement.');
                }

                if ($sql[$i] === "'") {
                    [$value, $i] = $this->readQuoted($sql, $i + 1, $length);
                    $tuple[] = $value;
                } else {
                    $start = $i;
                    while ($i < $length && $sql[$i] !== ',' && $sql[$i] !== ')') {
                        $i++;
                    }
                    $token = trim(substr($sql, $start, $i - $start));
                    $tuple[] = strtoupper($token) === 'NULL' ? null : $token;
                }

                if ($i >= $length) {
                    throw new RuntimeException('Unexpected end of INSERT statement.');
                }
                if ($sql[$i] === ',') {
                    $i++;

                    continue;
                }
                if ($sql[$i] === ')') {
                    $i++;
                    break;
                }

                throw new RuntimeException("Unexpected character '{$sql[$i]}' in INSERT statement.");
            }

            $tuples[] = $tuple;
        }

        return $tuples;
    }

    /**
     * @return array{0: string, 1: int} the unescaped string and the index after its closing quote
     */
    private function readQuoted(string $sql, int $i, int $length): array
    {
        $escapes = ['0' => "\0", 'b' => "\x08", 'n' => "\n", 'r' => "\r", 't' => "\t", 'Z' => "\x1A"];
        $buffer = '';

        while ($i < $length) {
            $char = $sql[$i];

            if ($char === '\\' && $i + 1 < $length) {
                $buffer .= $escapes[$sql[$i + 1]] ?? $sql[$i + 1];
                $i += 2;

                continue;
            }

            if ($char === "'") {
                if ($i + 1 < $length && $sql[$i + 1] === "'") {
                    $buffer .= "'";
                    $i += 2;

                    continue;
                }

                return [$buffer, $i + 1];
            }

            $buffer .= $char;
            $i++;
        }

        throw new RuntimeException('Unterminated string in INSERT statement.');
    }

    /** @return array<int, string> */
    public function tableNames(): array
    {
        return array_keys($this->tables);
    }

    public function hasTable(string $table): bool
    {
        return isset($this->tables[$table]);
    }

    /** @return array<int, string> */
    public function columns(string $table): array
    {
        return $this->tables[$table]['columns'] ?? [];
    }

    public function autoIncrement(string $table): ?int
    {
        return $this->tables[$table]['auto_increment'] ?? null;
    }

    /** @return array<int, array<string, string|null>> */
    public function rows(string $table): array
    {
        return $this->tables[$table]['rows'] ?? [];
    }

    /** The "Dump completed on" line, in the dumping server's clock (UTC on production). */
    public function completedAt(): ?string
    {
        return $this->completedAt;
    }

    /** Session time zone mysqldump used for TIMESTAMP values (normally '+00:00'). */
    public function timeZone(): ?string
    {
        return $this->timeZone;
    }
}

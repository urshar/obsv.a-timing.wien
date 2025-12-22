<?php

namespace App\Services;

use RuntimeException;

class CsvReader
{
    /**
     * @return array{headers: string[], rows: array<int, array<string, string|null>>, delimiter: string}
     */
    public function readAuto(string $path): array
    {
        $delimiter = $this->detectDelimiter($path);
        $result = $this->read($path, $delimiter);
        $result['delimiter'] = $delimiter;

        return $result;
    }

    public function detectDelimiter(string $path): string
    {
        $fh = fopen($path, 'r');
        if (! $fh) {
            throw new RuntimeException("Cannot open CSV: $path");
        }
        $line = fgets($fh) ?: '';
        fclose($fh);

        // Count candidates
        $candidates = [
            ';' => substr_count($line, ';'),
            ',' => substr_count($line, ','),
            "\t" => substr_count($line, "\t"),
        ];

        arsort($candidates);
        $best = array_key_first($candidates);

        // Fallback: comma
        return ($candidates[$best] > 0) ? $best : ',';
    }

    /**
     * @return array{headers: string[], rows: array<int, array<string, string|null>>}
     */
    public function read(string $path, string $delimiter = ','): array
    {
        $fh = fopen($path, 'r');
        if (! $fh) {
            throw new RuntimeException("Cannot open CSV: $path");
        }

        $headers = null;
        $rows = [];

        while (($data = fgetcsv($fh, 0, $delimiter)) !== false) {
            if ($data === [null] || (count($data) === 1 && trim((string) $data[0]) === '')) {
                continue;
            }

            if ($headers === null) {
                $headers = array_map(fn ($h) => trim((string) $h), $data);

                continue;
            }

            $row = [];
            foreach ($headers as $i => $h) {
                $val = $data[$i] ?? null;
                $val = is_string($val) ? trim($val) : $val;
                $row[$h] = ($val === '') ? null : $val;
            }
            $rows[] = $row;
        }

        fclose($fh);

        if ($headers === null) {
            throw new RuntimeException('CSV has no header row.');
        }

        return ['headers' => $headers, 'rows' => $rows];
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\Continent;
use App\Models\Nation;
use App\Services\CsvReader;
use App\Support\ImportReportingTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Throwable;

class NationImportController extends Controller
{
    use ImportReportingTrait;

    private const ROUTE_IMPORT_SHOW = 'nations.import.show';

    private const REPORT_TITLE = 'Nations CSV import finished.';

    private const REQUIRED_HEADERS = ['nameEn'];

    public function show()
    {
        return view('nations.import');
    }

    public function preview(Request $request, CsvReader $csv)
    {
        $request->validate([
            'csv' => ['required', 'file', 'mimes:csv,txt'],
            'truncate' => ['nullable'],
            'strict_unique' => ['nullable'],
        ]);

        $truncate = $request->boolean('truncate');
        $strictUnique = $request->boolean('strict_unique');

        $token = Str::uuid()->toString();
        $path = $request->file('csv')->storeAs('imports', "nations_{$token}.csv");

        $abs = Storage::path($path);
        $data = $csv->readAuto($abs);

        $issues = [];
        $this->validateHeaders($data['headers'], $issues);

        if (count($data['rows']) === 0) {
            $this->addIssue($issues, 'warning', 'empty_file', 'CSV contains no data rows.');
        }

        $previewRows = array_slice($data['rows'], 0, 20);

        return view('nations.import_preview', [
            'token' => $token,
            'storagePath' => $path,
            'delimiter' => $data['delimiter'],
            'headers' => $data['headers'],
            'previewRows' => $previewRows,
            'totalRows' => count($data['rows']),
            'issues' => $issues,

            // unified keys
            'truncate' => $truncate,
            'strictUnique' => $strictUnique,
        ]);
    }

    private function validateHeaders(array $headers, array &$issues): void
    {
        $missing = array_values(array_diff(self::REQUIRED_HEADERS, $headers));

        if (! empty($missing)) {
            $this->addIssue(
                $issues,
                'error',
                'missing_headers',
                'Missing required columns: '.implode(', ', $missing)
            );
        }
    }

    public function commit(Request $request, CsvReader $csv)
    {
        $request->validate([
            'token' => ['required', 'uuid'],
            'storagePath' => ['required', 'string'],
            'truncate' => ['nullable'],
            'strict_unique' => ['nullable'],
        ]);

        $truncate = $request->boolean('truncate');
        $strictUnique = $request->boolean('strict_unique');

        $path = $request->input('storagePath');
        if (! Storage::exists($path)) {
            $issues = [];
            $this->addIssue($issues, 'error', 'file_missing', 'Import file not found (expired). Please upload again.');

            $summary = $this->buildSummary(self::REPORT_TITLE, 0, 0, 0);

            return $this->redirectWithImportReport(self::ROUTE_IMPORT_SHOW, $summary, $issues, 'error');
        }

        $abs = Storage::path($path);

        try {
            $data = $csv->readAuto($abs);

            $continentCodeToId = Continent::query()->pluck('id', 'code')->toArray();

            $inserted = 0;
            $updated = 0;
            $skipped = 0;

            $rowIssues = []; // structured issues from row-level problems

            DB::transaction(function () use (
                $truncate,
                $strictUnique,
                $data,
                $continentCodeToId,
                &$inserted,
                &$updated,
                &$skipped,
                &$rowIssues
            ) {
                if ($truncate) {
                    $this->truncateNationsAndRegions();
                }

                foreach ($data['rows'] as $idx => $row) {
                    $line = $idx + 2;

                    $nameEn = $row['nameEn'] ?? null;
                    if (! $nameEn) {
                        $skipped++;
                        $this->addIssue($rowIssues, 'warning', 'missing_nameEn', "Line $line: missing nameEn");

                        continue;
                    }

                    $iso3 = $row['iso3'] ?? null;
                    $iso2 = $row['iso2'] ?? null;

                    $continentId = null;
                    if (! empty($row['continent_code'])) {
                        $continentId = $continentCodeToId[$row['continent_code']] ?? null;
                        if (! $continentId) {
                            $this->addIssue(
                                $rowIssues,
                                'warning',
                                'unknown_continent_code',
                                "Line $line: unknown continent_code '{$row['continent_code']}'"
                            );
                        }
                    }

                    $match = $iso3
                        ? ['iso3' => $iso3]
                        : ($iso2 ? ['iso2' => $iso2] : ['nameEn' => $nameEn]);

                    $payload = [
                        'nameEn' => $nameEn,
                        'nameDe' => $row['nameDe'] ?? null,
                        'continent_id' => $continentId,
                        'ioc' => $row['ioc'] ?? null,
                        'iso2' => $iso2,
                        'iso3' => $iso3,
                        'tld' => $row['tld'] ?? null,
                        'Capital' => $row['Capital'] ?? null,
                        'currencyAlphabeticCode' => $row['currencyAlphabeticCode'] ?? null,
                        'currencyName' => $row['currencyName'] ?? null,
                        'isIndependent' => $row['isIndependent'] ?? null,
                    ];

                    $conflictFields = $this->findUniqueConflicts($payload, $match);

                    if ($conflictFields) {
                        if ($strictUnique) {
                            $skipped++;
                            $this->addIssue(
                                $rowIssues,
                                'warning',
                                'unique_conflict',
                                "Line $line: UNIQUE conflict on ".implode(', ', $conflictFields)
                            );

                            continue;
                        }
                        foreach ($conflictFields as $f) {
                            $payload[$f] = null;
                        }
                        $this->addIssue(
                            $rowIssues,
                            'info',
                            'unique_conflict_nullified',
                            "Line $line: UNIQUE conflict on ".implode(', ', $conflictFields).' (fields set to null)'
                        );
                    }

                    $existing = Nation::where($match)->first();
                    if ($existing) {
                        $existing->fill($payload)->save();
                        $updated++;
                    } else {
                        Nation::create($payload);
                        $inserted++;
                    }
                }
            });

            $summary = $this->buildSummary(self::REPORT_TITLE, $inserted, $updated, $skipped);
            $status = $this->computeStatus($rowIssues, $skipped);

            return $this->redirectWithImportReport(self::ROUTE_IMPORT_SHOW, $summary, $rowIssues, $status);

        } catch (Throwable $e) {
            report($e);

            $issues = [];
            $this->addIssue($issues, 'error', 'exception', $e->getMessage());

            $summary = $this->buildSummary(self::REPORT_TITLE, 0, 0, 0);

            return $this->redirectWithImportReport(self::ROUTE_IMPORT_SHOW, $summary, $issues, 'error');

        } finally {
            try {
                Storage::delete($path);
            } catch (Throwable $e) {
                report($e);
            }
        }
    }

    private function truncateNationsAndRegions(): void
    {
        DB::statement('PRAGMA foreign_keys=OFF');

        if (DB::getSchemaBuilder()->hasTable('regions')) {
            DB::table('regions')->delete();
        }

        DB::table('nations')->delete();

        if (DB::getSchemaBuilder()->hasTable('sqlite_sequence')) {
            DB::table('sqlite_sequence')->where('name', 'nations')->delete();
        }

        DB::statement('PRAGMA foreign_keys=ON');
    }

    private function findUniqueConflicts(array $payload, array $match): array
    {
        $conflicts = [];

        foreach (['ioc', 'iso2', 'iso3'] as $field) {
            if (empty($payload[$field])) {
                continue;
            }

            $q = Nation::where($field, $payload[$field]);
            foreach ($match as $k => $v) {
                $q->where($k, '!=', $v);
            }

            if ($q->exists()) {
                $conflicts[] = $field;
            }
        }

        return $conflicts;
    }
}

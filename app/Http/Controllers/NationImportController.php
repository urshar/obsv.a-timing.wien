<?php

namespace App\Http\Controllers;

use App\Models\Continent;
use App\Models\Nation;
use App\Services\CsvReader;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Throwable;

class NationImportController extends Controller
{
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

        $token = (string) Str::uuid();
        $path = $request->file('csv')->storeAs('imports', "nations_{$token}.csv");

        $abs = Storage::path($path);
        $data = $csv->readAuto($abs);

        $issues = $this->validateHeaders($data['headers']);
        $previewRows = array_slice($data['rows'], 0, 20);

        return view('nations.import_preview', [
            'token' => $token,
            'storagePath' => $path,
            'delimiter' => $data['delimiter'],
            'headers' => $data['headers'],
            'previewRows' => $previewRows,
            'totalRows' => count($data['rows']),
            'truncate' => $request->boolean('truncate'),
            'strictUnique' => $request->boolean('strict_unique'),
            'issues' => $issues,
        ]);
    }

    private function validateHeaders(array $headers): array
    {
        $missing = array_values(array_diff(self::REQUIRED_HEADERS, $headers));
        if (! $missing) {
            return [];
        }

        return [
            [
                'level' => 'error',
                'code' => 'MISSING_HEADERS',
                'message' => 'Missing required columns: '.implode(', ', $missing),
            ],
        ];
    }

    public function commit(Request $request, CsvReader $csv)
    {
        $request->validate([
            'token' => ['required', 'uuid'],
            'storagePath' => ['required', 'string'],
            'truncate' => ['nullable'],
            'strict_unique' => ['nullable'],
        ]);

        $path = $request->input('storagePath');

        if (! Storage::exists($path)) {
            return redirect()->route('nations.import.show')
                ->with('import_status', 'error')
                ->with('import_summary', [
                    'title' => 'Import file not found (expired). Please upload again.',
                    'inserted' => 0,
                    'updated' => 0,
                    'skipped' => 0,
                ])
                ->with('import_issues', []);
        }

        $truncate = $request->boolean('truncate');
        $strictUnique = $request->boolean('strict_unique');

        $inserted = 0;
        $updated = 0;
        $skipped = 0;
        $issues = [];

        try {
            $data = $csv->readAuto(Storage::path($path));
            $continentCodeToId = Continent::query()->pluck('id', 'code')->toArray();

            DB::transaction(function () use (
                $data,
                $continentCodeToId,
                $truncate,
                $strictUnique,
                &$inserted,
                &$updated,
                &$skipped,
                &$issues
            ) {
                if ($truncate) {
                    DB::statement('PRAGMA foreign_keys=OFF');
                    if (DB::getSchemaBuilder()->hasTable('regions')) {
                        DB::table('regions')->delete();
                    }
                    DB::table('nations')->delete();
                    DB::statement('PRAGMA foreign_keys=ON');
                }

                foreach ($data['rows'] as $idx => $row) {
                    $line = $idx + 2;

                    $nameEn = $row['nameEn'] ?? null;
                    if (! $nameEn) {
                        $skipped++;
                        $issues[] = [
                            'level' => 'warning',
                            'code' => 'MISSING_NAMEEN',
                            'message' => "Line {$line}: missing nameEn",
                        ];

                        continue;
                    }

                    $iso2 = $row['iso2'] ?? null;
                    $iso3 = $row['iso3'] ?? null;

                    $continentId = null;
                    if (! empty($row['continent_code'])) {
                        $continentId = $continentCodeToId[$row['continent_code']] ?? null;
                        if (! $continentId) {
                            $issues[] = [
                                'level' => 'warning',
                                'code' => 'UNKNOWN_CONTINENT',
                                'message' => "Line {$line}: unknown continent_code '{$row['continent_code']}'",
                            ];
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

                    // UNIQUE checks
                    $conflicts = [];
                    foreach (['ioc', 'iso2', 'iso3'] as $f) {
                        if (empty($payload[$f])) {
                            continue;
                        }

                        $q = Nation::where($f, $payload[$f]);
                        foreach ($match as $k => $v) {
                            $q->where($k, '!=', $v);
                        }
                        if ($q->exists()) {
                            $conflicts[] = $f;
                        }
                    }

                    if ($conflicts) {
                        if ($strictUnique) {
                            $skipped++;
                            $issues[] = [
                                'level' => 'warning',
                                'code' => 'UNIQUE_CONFLICT',
                                'message' => "Line {$line}: UNIQUE conflict on ".implode(', ', $conflicts),
                            ];

                            continue;
                        }
                        foreach ($conflicts as $f) {
                            $payload[$f] = null;
                        }
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

        } catch (Throwable $e) {
            report($e);

            return redirect()->route('nations.import.show')
                ->with('import_status', 'error')
                ->with('import_summary', [
                    'title' => 'Import failed',
                    'inserted' => 0,
                    'updated' => 0,
                    'skipped' => 0,
                ])
                ->with('import_issues', [
                    [
                        'level' => 'error',
                        'code' => 'EXCEPTION',
                        'message' => $e->getMessage(),
                    ],
                ]);

        } finally {
            try {
                Storage::delete($path);
            } catch (Throwable $e) {
                report($e);
            }
        }

        $status = count(array_filter($issues, fn ($i) => $i['level'] === 'error'))
            ? 'error'
            : (count($issues) ? 'warning' : 'success');

        return redirect()->route('nations.import.show')
            ->with('import_status', $status)
            ->with('import_summary', [
                'title' => 'Nations CSV import finished.',
                'inserted' => $inserted,
                'updated' => $updated,
                'skipped' => $skipped,
            ])
            ->with('import_issues', $issues);
    }
}

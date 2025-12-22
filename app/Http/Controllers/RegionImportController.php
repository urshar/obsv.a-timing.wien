<?php

namespace App\Http\Controllers;

use App\Models\Nation;
use App\Models\Region;
use App\Services\CsvReader;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Throwable;

class RegionImportController extends Controller
{
    private const REQUIRED_HEADERS = ['nameEn'];

    public function show()
    {
        return view('regions.import');
    }

    public function preview(Request $request, CsvReader $csv)
    {
        $request->validate([
            'csv' => ['required', 'file', 'mimes:csv,txt'],
        ]);

        $token = (string) Str::uuid();
        $path = $request->file('csv')->storeAs('imports', "regions_{$token}.csv");

        $data = $csv->readAuto(Storage::path($path));

        $issues = $this->validateHeaders($data['headers']);
        if (! in_array('nation_iso2', $data['headers'], true) && ! in_array('nation_nameEn', $data['headers'], true)) {
            $issues[] = [
                'level' => 'error', 'code' => 'MISSING_NATION_REF',
                'message' => 'Missing nation reference column: provide either nation_iso2 or nation_nameEn.',
            ];
        }

        return view('regions.import_preview', [
            'token' => $token,
            'storagePath' => $path,
            'delimiter' => $data['delimiter'],
            'headers' => $data['headers'],
            'previewRows' => array_slice($data['rows'], 0, 20),
            'totalRows' => count($data['rows']),
            'issues' => $issues,
            'truncate' => false,
            'strictUnique' => false,
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
        ]);

        $path = $request->input('storagePath');

        if (! Storage::exists($path)) {
            return redirect()->route('regions.import.show')
                ->with('import_status', 'error')
                ->with('import_summary', [
                    'title' => 'Import file not found (expired). Please upload again.',
                    'inserted' => 0,
                    'updated' => 0,
                    'skipped' => 0,
                ])
                ->with('import_issues', []);
        }

        $inserted = 0;
        $updated = 0;
        $skipped = 0;
        $issues = [];

        try {
            $data = $csv->readAuto(Storage::path($path));

            DB::transaction(function () use ($data, &$inserted, &$updated, &$skipped, &$issues) {
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

                    $nation = null;
                    if (! empty($row['nation_iso2'])) {
                        $nation = Nation::where('iso2', $row['nation_iso2'])->first();
                    } elseif (! empty($row['nation_nameEn'])) {
                        $nation = Nation::where('nameEn', $row['nation_nameEn'])->first();
                    }

                    if (! $nation) {
                        $skipped++;
                        $issues[] = [
                            'level' => 'warning',
                            'code' => 'NATION_NOT_FOUND',
                            'message' => "Line {$line}: nation not found",
                        ];

                        continue;
                    }

                    $match = [
                        'nation_id' => $nation->id,
                        'nameEn' => $nameEn,
                    ];

                    $payload = [
                        'nation_id' => $nation->id,
                        'nameEn' => $nameEn,
                        'nameDe' => $row['nameDe'] ?? null,
                        'abbreviation' => $row['abbreviation'] ?? null,
                        'lsvCode' => $row['lsvCode'] ?? null,
                        'bsvCode' => $row['bsvCode'] ?? null,
                        'isoSubRegionCode' => $row['isoSubRegionCode'] ?? null,
                    ];

                    $existing = Region::where($match)->first();
                    if ($existing) {
                        $existing->fill($payload)->save();
                        $updated++;
                    } else {
                        Region::create($payload);
                        $inserted++;
                    }
                }
            });

        } catch (Throwable $e) {
            report($e);

            return redirect()->route('regions.import.show')
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

        return redirect()->route('regions.import.show')
            ->with('import_status', $status)
            ->with('import_summary', [
                'title' => 'Regions CSV import finished.',
                'inserted' => $inserted,
                'updated' => $updated,
                'skipped' => $skipped,
            ])
            ->with('import_issues', $issues);
    }
}

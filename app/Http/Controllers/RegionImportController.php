<?php

namespace App\Http\Controllers;

use App\Models\Nation;
use App\Models\Region;
use App\Services\CsvReader;
use App\Support\ImportReportingTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Throwable;

class RegionImportController extends Controller
{
    use ImportReportingTrait;

    private const ROUTE_IMPORT_SHOW = 'regions.import.show';

    private const REPORT_TITLE = 'Regions CSV import finished.';

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

        $token = Str::uuid()->toString();
        $path = $request->file('csv')->storeAs('imports', "regions_{$token}.csv");

        $abs = Storage::path($path);
        $data = $csv->readAuto($abs);

        $issues = [];
        $this->validateHeaders($data['headers'], $issues);

        if (
            ! in_array('nation_iso2', $data['headers'], true) &&
            ! in_array('nation_nameEn', $data['headers'], true)
        ) {
            $this->addIssue(
                $issues,
                'error',
                'missing_nation_reference',
                'Missing nation reference column: provide either nation_iso2 or nation_nameEn.'
            );
        }

        if (count($data['rows']) === 0) {
            $this->addIssue($issues, 'warning', 'empty_file', 'CSV contains no data rows.');
        }

        $previewRows = array_slice($data['rows'], 0, 20);

        return view('regions.import_preview', [
            'token' => $token,
            'storagePath' => $path,
            'delimiter' => $data['delimiter'],
            'headers' => $data['headers'],
            'previewRows' => $previewRows,
            'totalRows' => count($data['rows']),
            'issues' => $issues,

            // unified keys
            'truncate' => false,
            'strictUnique' => false,
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
        ]);

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

            $inserted = 0;
            $updated = 0;
            $skipped = 0;

            $rowIssues = [];

            $nationByIso2 = Nation::query()->whereNotNull('iso2')->get()->keyBy('iso2');
            $nationByName = Nation::query()->get()->keyBy('nameEn');

            DB::transaction(function () use (
                $data,
                $nationByIso2,
                $nationByName,
                &$inserted,
                &$updated,
                &$skipped,
                &$rowIssues
            ) {
                foreach ($data['rows'] as $idx => $row) {
                    $line = $idx + 2;

                    $nationIso2 = $row['nation_iso2'] ?? null;
                    $nationName = $row['nation_nameEn'] ?? null;

                    $nation = null;
                    if ($nationIso2 && isset($nationByIso2[$nationIso2])) {
                        $nation = $nationByIso2[$nationIso2];
                    } elseif ($nationName && isset($nationByName[$nationName])) {
                        $nation = $nationByName[$nationName];
                    }

                    if (! $nation) {
                        $skipped++;
                        $this->addIssue(
                            $rowIssues,
                            'warning',
                            'nation_not_found',
                            "Line $line: nation not found (nation_iso2='$nationIso2', nation_nameEn='$nationName')"
                        );

                        continue;
                    }

                    $nameEn = $row['nameEn'] ?? null;
                    if (! $nameEn) {
                        $skipped++;
                        $this->addIssue($rowIssues, 'warning', 'missing_nameEn', "Line $line: missing nameEn");

                        continue;
                    }

                    $isoSub = $row['isoSubRegionCode'] ?? null;

                    $match = $isoSub
                        ? ['nation_id' => $nation->id, 'isoSubRegionCode' => $isoSub]
                        : ['nation_id' => $nation->id, 'nameEn' => $nameEn];

                    $payload = [
                        'nation_id' => $nation->id,
                        'nameEn' => $nameEn,
                        'nameDe' => $row['nameDe'] ?? null,
                        'lsvCode' => $row['lsvCode'] ?? null,
                        'bsvCode' => $row['bsvCode'] ?? null,
                        'isoSubRegionCode' => $isoSub,
                        'abbreviation' => $row['abbreviation'] ?? null,
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
}

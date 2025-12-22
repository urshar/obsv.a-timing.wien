<?php

namespace App\Support;

use Illuminate\Http\RedirectResponse;

trait ImportReportingTrait
{
    /**
     * @param  array<int, array{level:string, code:string, message:string}>  $issues
     */
    protected function addIssue(array &$issues, string $level, string $code, string $message): void
    {
        $issues[] = [
            'level' => $level,
            'code' => $code,
            'message' => $message,
        ];
    }

    /**
     * @return array{title:string, inserted:int, updated:int, skipped:int}
     */
    protected function buildSummary(string $title, int $inserted, int $updated, int $skipped): array
    {
        return [
            'title' => $title,
            'inserted' => $inserted,
            'updated' => $updated,
            'skipped' => $skipped,
        ];
    }

    /**
     * @param  array<int, array{level?:string}>  $issues
     */
    protected function computeStatus(array $issues, int $skipped): string
    {
        $hasError = false;
        $hasWarning = ($skipped > 0);

        foreach ($issues as $i) {
            $lvl = $i['level'] ?? 'warning';
            if ($lvl === 'error') {
                $hasError = true;
            } elseif ($lvl === 'warning') {
                $hasWarning = true;
            }
        }

        if ($hasError) {
            return 'error';
        }
        if ($hasWarning) {
            return 'warning';
        }

        return 'success';
    }

    /**
     * @param  array{title:string, inserted:int, updated:int, skipped:int}  $summary
     * @param  array<int, array{level:string, code:string, message:string}>  $issues
     */
    protected function redirectWithImportReport(
        string $routeName,
        array $summary,
        array $issues,
        string $status
    ): RedirectResponse {
        return redirect()
            ->route($routeName)
            ->with('import_status', $status)
            ->with('import_summary', $summary)
            ->with('import_issues', $issues);
    }
}

<?php

namespace App\Support\Concerns;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

trait DeletesInChunks
{
    /**
     * Delete rows from a table using chunked whereIn().
     */
    protected function deleteWhereInChunks(
        string $table,
        string $column,
        Collection $ids,
        int $chunkSize = 1000
    ): void {
        if ($ids->isEmpty()) {
            return;
        }

        $ids->chunk($chunkSize)->each(function (Collection $chunk) use ($table, $column) {
            DB::table($table)->whereIn($column, $chunk)->delete();
        });
    }
}

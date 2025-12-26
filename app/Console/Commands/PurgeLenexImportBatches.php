<?php

namespace App\Console\Commands;

use App\Models\ImportBatch;
use App\Services\Lenex\LenexImportService;
use Illuminate\Console\Command;
use Symfony\Component\Console\Command\Command as CommandAlias;

class PurgeLenexImportBatches extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'lenex:purge-batches {--days=7}';

    /**
     * The console command description.
     */
    protected $description = 'Remove old preview/aborted LENEX import batches including XML and mappings';

    public function handle(LenexImportService $service): int
    {
        $days = (int) $this->option('days');
        $cutoff = now()->subDays($days);

        $this->info("Purging LENEX batches older than {$days} days …");

        $batches = ImportBatch::query()
            ->whereIn('status', ['preview', 'aborted'])
            ->where('created_at', '<', $cutoff)
            ->get();

        foreach ($batches as $batch) {
            $this->line("→ Purging batch #{$batch->id}");

            // zentraler Cleanup
            $service->abortBatch($batch);

            // Batch selbst entfernen
            $batch->delete();
        }

        $this->info("Done. {$batches->count()} batch(es) removed.");

        return CommandAlias::SUCCESS;
    }
}

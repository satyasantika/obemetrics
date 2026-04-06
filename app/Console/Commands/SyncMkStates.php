<?php

namespace App\Console\Commands;

use App\Actions\SyncMkState;
use App\Models\Mk;
use Illuminate\Console\Command;

class SyncMkStates extends Command
{
    protected $signature = 'mk:sync-states {--id= : Sync hanya satu MK berdasarkan ID}';

    protected $description = 'Re-evaluasi dan sinkronkan state semua MK berdasarkan data aktual';

    public function handle(): void
    {
        $query = Mk::query();

        if ($id = $this->option('id')) {
            $query->where('id', $id);
        }

        $mks = $query->get();

        if ($mks->isEmpty()) {
            $this->warn('Tidak ada MK ditemukan.');
            return;
        }

        $this->info("Menyinkronkan {$mks->count()} MK...");
        $bar = $this->output->createProgressBar($mks->count());
        $bar->start();

        foreach ($mks as $mk) {
            SyncMkState::sync($mk);
            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->info('Selesai. State semua MK telah diperbarui.');
    }
}

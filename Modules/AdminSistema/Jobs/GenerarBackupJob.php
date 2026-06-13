<?php

namespace Modules\AdminSistema\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Artisan;

class GenerarBackupJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 300;

    public function handle(): void
    {
        $exitCode = Artisan::call('backup:export-seeders');

        if ($exitCode !== 0) {
            throw new \RuntimeException(
                'backup:export-seeders terminó con código ' . $exitCode
            );
        }
    }
}

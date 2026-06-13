<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Modules\AdminSistema\Services\DriveService;

class BackupSyncDrive extends Command
{
    protected $signature = 'backup:sync-drive
                            {--file= : Ruta al ZIP a sincronizar. Por defecto: último ZIP disponible}
                            {--dry-run : Simular sin subir a Drive}';

    protected $description = 'Sincroniza el último Snapshot Institucional ZIP con Google Drive';

    public function handle(): int
    {
        $this->info('');
        $this->info('══════════════════════════════════════════════════');
        $this->info('  IEE — Sincronización manual → Google Drive');
        if ($this->option('dry-run')) {
            $this->info('  MODO: DRY-RUN');
        }
        $this->info('══════════════════════════════════════════════════');

        // DRIVE-001: Verificar estado de conexión
        $estado = DriveService::estadoConexion();
        $this->line("  Estado Drive: {$estado['etiqueta']} — {$estado['mensaje']}");

        if (in_array($estado['estado'], ['sin-rclone', 'sin-soporte'])) {
            $this->error("  ✗ {$estado['mensaje']}");
            return self::FAILURE;
        }

        // Resolver ZIP a sincronizar
        $zipPath = $this->resolverZip();
        if ($zipPath === null) {
            return self::FAILURE;
        }

        $zipName = basename($zipPath);
        $sizeKb  = round(filesize($zipPath) / 1024, 1);
        $this->info("  ZIP: {$zipName} ({$sizeKb} KB)");

        if (!$this->option('dry-run')) {
            $this->info("  Destino: {$estado['carpeta']}");
        }

        $this->info('');
        $this->info('Sincronizando...');

        $result = DriveService::subirZip($zipPath, $zipName, (bool) $this->option('dry-run'));

        if ($result['exito']) {
            $this->line("  ✓ {$result['mensaje']}");
            if (!$this->option('dry-run')) {
                $localKb  = round($result['size_local'] / 1024, 1);
                $remoteKb = round($result['size_remote'] / 1024, 1);
                $this->line("  ✓ Local: {$localKb} KB | Drive: {$remoteKb} KB");
            }
        } else {
            $this->error("  ✗ {$result['mensaje']}");
            return self::FAILURE;
        }

        $this->info('');
        $this->info('══════════════════════════════════════════════════');
        $this->info('  ✓ Sincronización completada.');
        $this->info('══════════════════════════════════════════════════');

        return self::SUCCESS;
    }

    private function resolverZip(): ?string
    {
        $file = $this->option('file');

        if ($file) {
            $path = str_starts_with($file, '/') ? $file : base_path($file);
            if (!file_exists($path)) {
                $this->error("  ✗ ZIP no encontrado: {$path}");
                return null;
            }
            return $path;
        }

        // Sin --file: buscar el ZIP más reciente
        $zips = glob(base_path('backups/IEE-????-??-??.zip')) ?: [];
        if (empty($zips)) {
            $this->error('  ✗ No se encontraron ZIPs en backups/. Genera un respaldo primero.');
            return null;
        }

        rsort($zips);
        return $zips[0];
    }
}

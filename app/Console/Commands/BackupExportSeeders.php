<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use ZipArchive;

class BackupExportSeeders extends Command
{
    protected $signature = 'backup:export-seeders
                            {--dry-run : Simular sin escribir archivos ni subir a Drive}
                            {--skip-drive : Omitir la subida a Google Drive}
                            {--date= : Fecha del respaldo (YYYY-MM-DD). Por defecto: hoy}';

    protected $description = 'Exporta datos institucionales a CSV y genera respaldo ZIP para Google Drive';

    /**
     * Tablas exportadas en orden de dependencia FK.
     * Excluye: sessions, cache, failed_jobs, password_reset_tokens, personal_access_tokens,
     *           notifications, jobs, migrations.
     */
    private const TABLES = [
        // Gestión de acceso (base)
        'permissions',
        'apps',
        'roles',
        'users',
        'permission_role',
        'permission_user',
        'app_role',
        // Catálogos de inventario (sin dependencias entre sí)
        'categorias',
        'dependencias',
        'ubicaciones',
        'origenes',
        'estados',
        'almacenamientos',
        'mantenimientos',
        // Bienes e historial (dependen de catálogos y users)
        'bienes',
        'detalles',
        'bienes_responsables',
        'mantenimientos_programados',
        'historial_modificaciones_bienes',
        'historial_ubicaciones_bienes',
        'historial_eliminaciones_bienes',
        'historial_dependencias_bienes',
        // Auditoría
        'auditoria_passwords',
    ];

    private const DAILY_RETENTION  = 30;
    private const MONTHLY_RETENTION = 12;

    private bool $dryRun;
    private string $date;
    private string $backupDir;
    private string $zipPath;
    private string $zipName;

    public function handle(): int
    {
        $this->dryRun  = (bool) $this->option('dry-run');
        $this->date    = $this->option('date') ?? now()->format('Y-m-d');
        $this->backupDir = base_path("backups/{$this->date}");
        $this->zipName   = "IEE-{$this->date}.zip";
        $this->zipPath   = base_path("backups/{$this->zipName}");

        $this->printHeader();

        if (!$this->dryRun && !$this->ensureBackupDir()) {
            return self::FAILURE;
        }

        // Paso 1: Exportar tablas a CSV
        $this->info('Exportando tablas...');
        $counts = $this->exportTables();

        // Paso 2: Metadata
        $this->info('Generando metadata.json...');
        $this->generateMetadata($counts);

        // Paso 3: Comprimir
        $this->info('Comprimiendo en ZIP...');
        if (!$this->dryRun && !$this->createZip()) {
            return self::FAILURE;
        }

        // Paso 4: Retención
        $this->info('Aplicando política de retención...');
        $this->applyRetention();

        // Paso 5: Drive
        if (!$this->option('skip-drive')) {
            $this->info('Subiendo a Google Drive...');
            $this->uploadToDrive();
        } else {
            $this->comment('  → Drive: omitido (--skip-drive)');
        }

        $this->printSummary($counts);
        return self::SUCCESS;
    }

    // ──────────────────────────────────────────────────────────────────────────
    // Exportación de tablas
    // ──────────────────────────────────────────────────────────────────────────

    private function exportTables(): array
    {
        $counts = [];

        foreach (self::TABLES as $table) {
            $count = $this->exportTable($table);
            $counts[$table] = $count;
        }

        return $counts;
    }

    private function exportTable(string $table): int
    {
        if (!Schema::hasTable($table)) {
            $this->comment("  [SKIP] {$table} — tabla no existe en esta BD");
            return 0;
        }

        $rows = DB::table($table)->get();
        $count = $rows->count();

        if ($this->dryRun) {
            $this->line("  [DRY] {$table}: {$count} filas → {$table}.csv");
            return $count;
        }

        $path   = "{$this->backupDir}/{$table}.csv";
        $handle = fopen($path, 'w');

        if ($count > 0) {
            $headers = array_keys((array) $rows->first());
            fputcsv($handle, $headers);

            foreach ($rows as $row) {
                fputcsv($handle, array_map(
                    static fn($v) => $v ?? '',
                    (array) $row
                ));
            }
        } else {
            // Tabla vacía: exportar solo headers para mantener estructura
            $columns = Schema::getColumnListing($table);
            fputcsv($handle, $columns);
        }

        fclose($handle);

        $this->line("  ✓ {$table}: {$count} filas");
        return $count;
    }

    // ──────────────────────────────────────────────────────────────────────────
    // Metadata
    // ──────────────────────────────────────────────────────────────────────────

    private function generateMetadata(array $counts): void
    {
        $versiones = config('versiones', []);
        $total = array_sum($counts);

        $meta = [
            'fecha'               => $this->date,
            'generado_en'         => now()->format('Y-m-d H:i:s'),
            'entorno'             => app()->environment(),
            'db_database'         => config('database.connections.mysql.database'),
            'version_iee'         => $versiones['IEE']          ?? 'n/a',
            'version_bhagamapps'  => $versiones['BhagamApps']   ?? 'n/a',
            'version_inventario'  => $versiones['Inventario']    ?? 'n/a',
            'version_user'        => $versiones['User']          ?? 'n/a',
            'version_apps'        => $versiones['Apps']          ?? 'n/a',
            'tablas_exportadas'   => count(array_filter($counts, fn($c) => $c >= 0)),
            'total_registros'     => $total,
            'conteos'             => $counts,
        ];

        if ($this->dryRun) {
            $this->line('  [DRY] metadata.json generado');
            return;
        }

        file_put_contents(
            "{$this->backupDir}/metadata.json",
            json_encode($meta, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
        );

        $this->line("  ✓ metadata.json ({$total} registros totales)");
    }

    // ──────────────────────────────────────────────────────────────────────────
    // Compresión ZIP
    // ──────────────────────────────────────────────────────────────────────────

    private function createZip(): bool
    {
        if ($this->dryRun) {
            $this->line("  [DRY] → {$this->zipName}");
            return true;
        }

        if (file_exists($this->zipPath)) {
            unlink($this->zipPath);
        }

        $zip = new ZipArchive();
        if ($zip->open($this->zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            $this->error("  ✗ No se pudo crear el ZIP: {$this->zipPath}");
            return false;
        }

        $files = glob("{$this->backupDir}/*");
        foreach ($files as $file) {
            if (is_file($file)) {
                $zip->addFile($file, basename($file));
            }
        }

        $zip->close();

        $sizeKb = round(filesize($this->zipPath) / 1024, 1);
        $this->line("  ✓ {$this->zipName} ({$sizeKb} KB)");
        return true;
    }

    // ──────────────────────────────────────────────────────────────────────────
    // Política de retención
    // ──────────────────────────────────────────────────────────────────────────

    private function applyRetention(): void
    {
        $backupsRoot = base_path('backups');
        $zips = glob("{$backupsRoot}/IEE-????-??-??.zip");

        if (empty($zips)) {
            return;
        }

        // Ordenar de más reciente a más antiguo por nombre (YYYY-MM-DD es ordenable)
        rsort($zips);

        $keep = [];

        // Retención diaria: los 30 más recientes
        foreach (array_slice($zips, 0, self::DAILY_RETENTION) as $zip) {
            $keep[$zip] = 'daily';
        }

        // Retención mensual: primer backup de cada mes en los últimos 12 meses
        $cutoffDate = now()->subMonths(self::MONTHLY_RETENTION)->format('Y-m-d');
        $monthsSeen = [];

        foreach ($zips as $zip) {
            if (!preg_match('/IEE-(\d{4}-\d{2}-\d{2})\.zip$/', $zip, $m)) {
                continue;
            }
            $zipDate  = $m[1];
            $month    = substr($zipDate, 0, 7); // YYYY-MM

            if ($zipDate >= $cutoffDate && !isset($monthsSeen[$month])) {
                $monthsSeen[$month] = true;
                $keep[$zip] = 'monthly';
            }
        }

        // Eliminar ZIPs que no están en la lista de retención
        foreach ($zips as $zip) {
            if (!isset($keep[$zip])) {
                if ($this->dryRun) {
                    $this->comment("  [DRY] eliminar: " . basename($zip));
                } else {
                    unlink($zip);
                    $this->comment("  🗑 Eliminado (retención): " . basename($zip));
                }
            }
        }

        // Limpiar directorios de backups que ya no tienen ZIP correspondiente
        $keepDates = [];
        foreach (array_keys($keep) as $zip) {
            if (preg_match('/IEE-(\d{4}-\d{2}-\d{2})\.zip$/', $zip, $m)) {
                $keepDates[] = $m[1];
            }
        }

        $dirs = glob("{$backupsRoot}/????-??-??", GLOB_ONLYDIR);
        foreach ($dirs as $dir) {
            $dirDate = basename($dir);
            if (!in_array($dirDate, $keepDates)) {
                if ($this->dryRun) {
                    $this->comment("  [DRY] eliminar dir: backups/{$dirDate}/");
                } else {
                    $this->removeDirectory($dir);
                    $this->comment("  🗑 Dir eliminado (retención): backups/{$dirDate}/");
                }
            }
        }

        $this->line('  ✓ Retención: conservados ' . count($keep) . ' ZIP(s)');
    }

    // ──────────────────────────────────────────────────────────────────────────
    // Google Drive via rclone
    // ──────────────────────────────────────────────────────────────────────────

    private function uploadToDrive(): void
    {
        $rcloneBin = env('BACKUP_RCLONE_BIN', '/usr/bin/rclone');
        $remote    = env('BACKUP_RCLONE_REMOTE', 'iee-backup');
        $destPath  = env('BACKUP_RCLONE_DEST', 'IEE-Backups');

        if (!file_exists($rcloneBin)) {
            $this->warn("  ⚠ rclone no encontrado en {$rcloneBin}. Omitiendo Drive.");
            return;
        }

        if (!file_exists($this->zipPath)) {
            $this->warn("  ⚠ ZIP no encontrado: {$this->zipPath}. Omitiendo Drive.");
            return;
        }

        $remoteDest = "{$remote}:{$destPath}";

        if ($this->dryRun) {
            $this->line("  [DRY] rclone copy {$this->zipName} → {$remoteDest}");
            return;
        }

        // Si hay credenciales de Service Account en env, pasarlas como env vars a rclone.
        // Rclone soporta: RCLONE_CONFIG_{REMOTE}_{OPTION}=valor
        // Ver: docs/operations/BACKUP-RESTORE-GUIDE.md § Configuración Google Drive
        $envPrefix  = 'RCLONE_CONFIG_' . strtoupper(str_replace('-', '_', $remote)) . '_';
        $saJson     = env('BACKUP_GDRIVE_SA_JSON', '');
        $folderId   = env('BACKUP_GDRIVE_FOLDER_ID', '');

        $rcloneEnv = [];
        if ($saJson) {
            $rcloneEnv[$envPrefix . 'TYPE']                           = 'drive';
            $rcloneEnv[$envPrefix . 'SCOPE']                          = 'drive.file';
            $rcloneEnv[$envPrefix . 'SERVICE_ACCOUNT_CREDENTIALS']    = $saJson;
            if ($folderId) {
                $rcloneEnv[$envPrefix . 'ROOT_FOLDER_ID'] = $folderId;
            }
        }

        $envStr = '';
        foreach ($rcloneEnv as $key => $val) {
            $envStr .= escapeshellarg($key) . '=' . escapeshellarg($val) . ' ';
        }

        $cmd = ($envStr ? "env {$envStr}" : '')
            . escapeshellarg($rcloneBin) . ' copy '
            . escapeshellarg($this->zipPath) . ' '
            . escapeshellarg($remoteDest)
            . ' --stats 0 2>&1';

        exec($cmd, $output, $exitCode);

        if ($exitCode === 0) {
            $this->line("  ✓ Subido a Drive: {$remoteDest}/{$this->zipName}");
        } else {
            $errorMsg = implode(' | ', array_filter($output));
            $this->error("  ✗ Error Drive (código {$exitCode}): {$errorMsg}");
            $this->warn('  → El ZIP local está disponible en: backups/' . $this->zipName);
        }
    }

    // ──────────────────────────────────────────────────────────────────────────
    // Helpers
    // ──────────────────────────────────────────────────────────────────────────

    private function ensureBackupDir(): bool
    {
        if (!is_dir($this->backupDir) && !mkdir($this->backupDir, 0755, true)) {
            $this->error("No se pudo crear el directorio: {$this->backupDir}");
            return false;
        }
        return true;
    }

    private function removeDirectory(string $dir): void
    {
        $files = array_diff(scandir($dir), ['.', '..']);
        foreach ($files as $file) {
            $path = "{$dir}/{$file}";
            is_dir($path) ? $this->removeDirectory($path) : unlink($path);
        }
        rmdir($dir);
    }

    private function printHeader(): void
    {
        $mode = $this->dryRun ? '  MODO: DRY-RUN (sin escritura)' : '  MODO: REAL → producción';
        $this->info('══════════════════════════════════════════════════');
        $this->info('  IEE — Backup Export Seeders');
        $this->info("  Fecha: {$this->date}");
        $this->info($mode);
        $this->info('══════════════════════════════════════════════════');
    }

    private function printSummary(array $counts): void
    {
        $total = array_sum($counts);
        $this->info('══════════════════════════════════════════════════');
        $this->info('  BACKUP COMPLETADO');
        $this->info("  Tablas:    " . count($counts));
        $this->info("  Registros: {$total}");
        $this->info("  ZIP:       backups/{$this->zipName}");
        if (!$this->dryRun) {
            $kb = file_exists($this->zipPath) ? round(filesize($this->zipPath) / 1024, 1) . ' KB' : 'n/a';
            $this->info("  Tamaño:    {$kb}");
        }
        $this->info('══════════════════════════════════════════════════');
    }
}

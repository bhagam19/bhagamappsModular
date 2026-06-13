<?php

namespace App\Console\Commands;

use Database\Seeders\InstitutionalRestoreSeeder;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use ZipArchive;

class BackupRestoreFromZip extends Command
{
    protected $signature = 'backup:restore-from-zip
                            {--file= : Ruta al ZIP (ej: backups/IEE-2026-06-13.zip)}
                            {--dry-run : Simular sin modificar la base de datos}
                            {--force : Omitir confirmación interactiva}';

    protected $description = 'Restaura la base de datos institucional desde un Snapshot ZIP';

    /**
     * Tablas cuyo CSV viene del ZIP y debe copiarse a Seeders/data/ antes de restaurar.
     * Clave: nombre del CSV en el ZIP. Valor: ruta destino relativa a base_path().
     */
    private const CSV_SEEDER_MAP = [
        'permissions'                    => 'Modules/User/Database/Seeders/data/permissions.csv',
        'permission_role'                => 'Modules/User/Database/Seeders/data/permission_role.csv',
        'app_role'                       => 'Modules/User/Database/Seeders/data/app_role.csv',
        'users'                          => 'Modules/User/Database/Seeders/data/users.csv',
        'bienes'                         => 'Modules/Inventario/Database/Seeders/data/bienes.csv',
        'categorias'                     => 'Modules/Inventario/Database/Seeders/data/categorias.csv',
        'dependencias'                   => 'Modules/Inventario/Database/Seeders/data/dependencias.csv',
        'detalles'                       => 'Modules/Inventario/Database/Seeders/data/detalles.csv',
        'origenes'                       => 'Modules/Inventario/Database/Seeders/data/origenes.csv',
        'historial_modificaciones_bienes' => 'Modules/Inventario/Database/Seeders/data/historial_modificaciones_bienes.csv',
        'bienes_responsables'            => 'Modules/Inventario/Database/Seeders/data/bienes_responsables.csv',
        'mantenimientos_programados'     => 'Modules/Inventario/Database/Seeders/data/mantenimientos_programados.csv',
    ];

    /**
     * Tablas sin seeder de datos (vacías o no restaurables automáticamente).
     * Se omiten de la validación post-restauración.
     */
    private const TABLAS_SIN_SEEDER = [
        'permission_user',
        'auditoria_passwords',
        'historial_ubicaciones_bienes',
        'historial_eliminaciones_bienes',
        'historial_dependencias_bienes',
    ];

    private string $tempDir = '';

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');

        $this->printHeader($dryRun);

        // RESTORE-001 + RESTORE-002: Resolver y validar ZIP
        $zipPath = $this->resolveZipPath();
        if ($zipPath === null) {
            return self::FAILURE;
        }

        if (!$this->validateZip($zipPath)) {
            return self::FAILURE;
        }

        // RESTORE-003: Leer y mostrar metadata del ZIP
        $metadata = $this->leerMetadataDesdeZip($zipPath);
        if ($metadata === null) {
            return self::FAILURE;
        }
        $this->mostrarMetadata($metadata);

        // Confirmación interactiva (omitida en --force o --dry-run)
        if (!$dryRun && !$this->option('force')) {
            if (!$this->confirmarRestauracion($metadata)) {
                $this->comment('Restauración cancelada por el usuario.');
                return self::SUCCESS;
            }
        }

        // RESTORE-004: Extraer ZIP a directorio temporal
        $this->tempDir = $this->extraerZip($zipPath);
        if ($this->tempDir === '') {
            return self::FAILURE;
        }

        try {
            // RESTORE-005: Sincronizar CSVs con Seeders/data/
            $this->info('');
            $this->info('▶ RESTORE-005: Sincronizando CSV con directorios de seeders...');
            if (!$this->sincronizarCsvSeeders($dryRun)) {
                return self::FAILURE;
            }

            if ($dryRun) {
                $this->info('');
                $this->info('DRY-RUN finalizado. Base de datos no modificada.');
                $this->info('Para restaurar realmente: php artisan backup:restore-from-zip --file=' . $this->option('file') . ' --force');
                return self::SUCCESS;
            }

            // RESTORE-006 + RESTORE-007: Ejecutar InstitutionalRestoreSeeder en transacción
            $this->info('');
            $this->info('▶ RESTORE-006: Ejecutando restauración institucional (transacción)...');
            if (!$this->ejecutarRestauracion()) {
                $this->registrarAuditoria($zipPath, $metadata, false, 'Error en transacción — rollback ejecutado');
                return self::FAILURE;
            }

            // RESTORE-008: Validación post-restauración
            $this->info('');
            $this->info('▶ RESTORE-008: Validando restauración...');
            $valido = $this->validarRestauracion($metadata);

            // RESTORE-009: Registro de auditoría
            $this->info('');
            $this->registrarAuditoria($zipPath, $metadata, $valido, $valido ? 'OK' : 'INCONSISTENCIAS DETECTADAS');

            $this->printFooter($valido);

            return $valido ? self::SUCCESS : self::FAILURE;

        } finally {
            // RESTORE-007: Limpiar temporales siempre (éxito o error)
            $this->limpiarTemporales();
        }
    }

    // ── RESTORE-001: Resolver ruta ZIP ───────────────────────────────────────

    private function resolveZipPath(): ?string
    {
        $file = $this->option('file');

        if (!$file) {
            $this->error('Debes especificar la ruta al ZIP con --file=');
            $this->line('  Ejemplo: php artisan backup:restore-from-zip --file=backups/IEE-2026-06-13.zip');
            return null;
        }

        // Ruta absoluta → usar tal cual; relativa → resolver desde base_path()
        return str_starts_with($file, '/') ? $file : base_path($file);
    }

    // ── RESTORE-002: Validar ZIP ─────────────────────────────────────────────

    private function validateZip(string $zipPath): bool
    {
        $this->info('▶ RESTORE-002: Validando ZIP...');

        if (!file_exists($zipPath)) {
            $this->error("  ✗ Archivo no encontrado: {$zipPath}");
            return false;
        }

        if (!is_readable($zipPath)) {
            $this->error("  ✗ Archivo no legible (permisos): {$zipPath}");
            return false;
        }

        $zip = new ZipArchive();
        $result = $zip->open($zipPath, ZipArchive::RDONLY);

        if ($result !== true) {
            $this->error("  ✗ ZIP inválido o corrupto (código ZipArchive: {$result})");
            return false;
        }

        $tieneMetadata = $zip->locateName('metadata.json') !== false;
        $totalArchivos  = $zip->numFiles;
        $zip->close();

        if (!$tieneMetadata) {
            $this->error('  ✗ El ZIP no contiene metadata.json — no es un Snapshot Institucional válido.');
            return false;
        }

        $sizeKb = round(filesize($zipPath) / 1024, 1);
        $this->line("  ✓ ZIP válido: " . basename($zipPath) . " ({$sizeKb} KB, {$totalArchivos} archivos)");

        return true;
    }

    // ── RESTORE-003: Leer metadata desde ZIP ─────────────────────────────────

    private function leerMetadataDesdeZip(string $zipPath): ?array
    {
        $this->info('▶ RESTORE-003: Leyendo metadata del Snapshot...');

        $zip = new ZipArchive();
        $zip->open($zipPath, ZipArchive::RDONLY);
        $json = $zip->getFromName('metadata.json');
        $zip->close();

        if ($json === false) {
            $this->error('  ✗ No se pudo leer metadata.json del ZIP.');
            return null;
        }

        $data = json_decode($json, true);

        if (!is_array($data) || !isset($data['conteos'])) {
            $this->error('  ✗ metadata.json inválido o sin campo "conteos".');
            return null;
        }

        return $data;
    }

    private function mostrarMetadata(array $meta): void
    {
        $this->line('');
        $this->line('  ┌─────────────────────────────────────────────┐');
        $this->line("  │  Fecha snapshot : {$meta['fecha']}");
        $this->line("  │  Generado       : {$meta['generado_en']}");
        $this->line("  │  Entorno        : {$meta['entorno']}");
        $this->line("  │  IEE            : v{$meta['version_iee']}");
        $this->line("  │  BhagamApps     : v{$meta['version_bhagamapps']}");
        $this->line("  │  Tablas         : {$meta['tablas_exportadas']}");
        $this->line("  │  Registros      : {$meta['total_registros']}");
        $this->line('  └─────────────────────────────────────────────┘');
    }

    // ── Confirmación interactiva ─────────────────────────────────────────────

    private function confirmarRestauracion(array $meta): bool
    {
        $this->newLine();
        $this->warn('  ⚠  ADVERTENCIA DE RESTAURACIÓN');
        $this->warn('  Los registros existentes en la BD se sobreescribirán con los del snapshot.');
        $this->warn('  Registros adicionales en la BD no presentes en el backup NO se eliminarán.');
        $this->warn("  Snapshot: {$meta['fecha']} — {$meta['total_registros']} registros en {$meta['tablas_exportadas']} tablas.");
        $this->newLine();

        return $this->confirm('¿Confirmar restauración institucional?', false);
    }

    // ── RESTORE-004: Extraer ZIP ─────────────────────────────────────────────

    private function extraerZip(string $zipPath): string
    {
        $this->info('');
        $this->info('▶ RESTORE-004: Extrayendo ZIP...');

        $tempDir = storage_path('app/restore-temp/' . now()->format('YmdHis'));

        if (!mkdir($tempDir, 0755, true)) {
            $this->error("  ✗ No se pudo crear directorio temporal: {$tempDir}");
            return '';
        }

        $zip = new ZipArchive();
        if ($zip->open($zipPath) !== true) {
            $this->error("  ✗ No se pudo abrir el ZIP para extracción.");
            rmdir($tempDir);
            return '';
        }

        $zip->extractTo($tempDir);
        $zip->close();

        $archivos = array_diff(scandir($tempDir), ['.', '..']);
        $this->line("  ✓ Extraídos " . count($archivos) . " archivos a: storage/app/restore-temp/" . basename($tempDir));

        return $tempDir;
    }

    // ── RESTORE-005: Sincronizar CSVs con Seeders/data/ ──────────────────────

    private function sincronizarCsvSeeders(bool $dryRun): bool
    {
        $errores = 0;
        $copiados = 0;
        $omitidos = 0;

        foreach (self::CSV_SEEDER_MAP as $tabla => $destRel) {
            $src     = "{$this->tempDir}/{$tabla}.csv";
            $destAbs = base_path($destRel);

            if (!file_exists($src)) {
                $this->warn("  ⚠ {$tabla}.csv no encontrado en ZIP — omitido.");
                $omitidos++;
                continue;
            }

            if ($dryRun) {
                // En dry-run solo mostrar qué se copiaría
                $filas = max(0, count(file($src)) - 1);
                $this->line("  [DRY] {$tabla}.csv → {$destRel} ({$filas} filas)");
                $copiados++;
                continue;
            }

            $destDir = dirname($destAbs);
            if (!is_dir($destDir) && !mkdir($destDir, 0755, true)) {
                $this->error("  ✗ No se pudo crear directorio: {$destDir}");
                $errores++;
                continue;
            }

            if (!copy($src, $destAbs)) {
                $this->error("  ✗ Error al copiar {$tabla}.csv → {$destRel}");
                $errores++;
                continue;
            }

            $filas = max(0, count(file($destAbs)) - 1);
            $this->line("  ✓ {$tabla}.csv → {$destRel} ({$filas} filas)");
            $copiados++;
        }

        if ($errores > 0) {
            $this->error("  ✗ {$errores} error(es) al sincronizar CSV. Abortando.");
            return false;
        }

        $prefijo = $dryRun ? '[DRY] ' : '';
        $this->line("  {$prefijo}✓ {$copiados} CSV sincronizados" . ($omitidos > 0 ? ", {$omitidos} omitidos." : '.'));

        return true;
    }

    // ── RESTORE-006 + RESTORE-007: Restaurar en transacción ─────────────────

    private function ejecutarRestauracion(): bool
    {
        try {
            DB::transaction(function () {
                $seeder = app(InstitutionalRestoreSeeder::class);
                $seeder->setContainer(app());
                $seeder->setCommand($this);
                $seeder->run();
            });

            $this->line('  ✓ Transacción confirmada (commit).');
            return true;

        } catch (\Throwable $e) {
            $this->error('  ✗ Error durante la restauración — transacción revertida (rollback).');
            $this->error('    Mensaje : ' . $e->getMessage());
            $this->error('    Archivo  : ' . $e->getFile() . ':' . $e->getLine());
            return false;
        }
    }

    // ── RESTORE-008: Validación post-restauración ────────────────────────────

    private function validarRestauracion(array $metadata): bool
    {
        $conteos     = $metadata['conteos'] ?? [];
        $errores     = [];
        $omitidos    = [];
        $correctas   = 0;

        foreach ($conteos as $tabla => $esperado) {
            if (in_array($tabla, self::TABLAS_SIN_SEEDER)) {
                $omitidos[] = $tabla;
                continue;
            }

            try {
                $actual = DB::table($tabla)->count();

                if ($actual < $esperado) {
                    $errores[] = "  ✗ {$tabla}: esperados≥{$esperado}, encontrados={$actual}";
                } else {
                    $this->line("  ✓ {$tabla}: {$actual} (esperado {$esperado})");
                    $correctas++;
                }
            } catch (\Throwable $e) {
                $errores[] = "  ✗ {$tabla}: error al contar — " . $e->getMessage();
            }
        }

        if (!empty($omitidos)) {
            $this->comment('  → Tablas sin seeder (omitidas de validación): ' . implode(', ', $omitidos));
        }

        if (empty($errores)) {
            $this->info("  ✓ Validación post-restauración: EXITOSA ({$correctas} tablas verificadas)");
            return true;
        }

        $this->error('  ✗ Validación post-restauración: INCONSISTENCIAS DETECTADAS');
        foreach ($errores as $error) {
            $this->error($error);
        }

        return false;
    }

    // ── RESTORE-009: Auditoría ───────────────────────────────────────────────

    private function registrarAuditoria(string $zipPath, array $metadata, bool $exito, string $detalle): void
    {
        $entrada = json_encode([
            'fecha'          => now()->format('Y-m-d H:i:s'),
            'backup'         => basename($zipPath),
            'version_iee'    => $metadata['version_iee'] ?? 'n/a',
            'version_bha'    => $metadata['version_bhagamapps'] ?? 'n/a',
            'tablas'         => $metadata['tablas_exportadas'] ?? 0,
            'registros'      => $metadata['total_registros'] ?? 0,
            'resultado'      => $exito ? 'EXITOSA' : 'FALLIDA',
            'detalle'        => $detalle,
        ], JSON_UNESCAPED_UNICODE);

        $logPath = storage_path('logs/restore.log');
        file_put_contents($logPath, $entrada . PHP_EOL, FILE_APPEND | LOCK_EX);

        $this->line('  ✓ Auditoría registrada en storage/logs/restore.log');
    }

    // ── RESTORE-007: Limpieza de temporales ─────────────────────────────────

    private function limpiarTemporales(): void
    {
        if ($this->tempDir === '' || !is_dir($this->tempDir)) {
            return;
        }

        $this->info('');
        $this->info('▶ Limpiando directorio temporal...');
        $this->eliminarDirectorio($this->tempDir);

        // Eliminar restore-temp/ si quedó vacío
        $padre = dirname($this->tempDir);
        if (is_dir($padre) && count(scandir($padre)) === 2) {
            rmdir($padre);
        }

        $this->line('  ✓ Temporales eliminados.');
    }

    private function eliminarDirectorio(string $dir): void
    {
        foreach (array_diff(scandir($dir), ['.', '..']) as $archivo) {
            $ruta = "{$dir}/{$archivo}";
            is_dir($ruta) ? $this->eliminarDirectorio($ruta) : unlink($ruta);
        }
        rmdir($dir);
    }

    // ── UI ───────────────────────────────────────────────────────────────────

    private function printHeader(bool $dryRun): void
    {
        $modo = $dryRun ? '  MODO: DRY-RUN (simulación — BD no se modifica)' : '  MODO: RESTAURACIÓN REAL';
        $this->info('');
        $this->info('══════════════════════════════════════════════════');
        $this->info('  IEE — Restauración desde Snapshot Institucional');
        $this->info($modo);
        $this->info('══════════════════════════════════════════════════');
    }

    private function printFooter(bool $exito): void
    {
        $this->info('');
        $this->info('══════════════════════════════════════════════════');
        if ($exito) {
            $this->info('  ✓ RESTAURACIÓN COMPLETADA EXITOSAMENTE');
            $this->info('  → Limpiar caché: php artisan cache:clear');
            $this->info('  → Verificar acceso: http://<host>/iee');
        } else {
            $this->error('  ✗ RESTAURACIÓN COMPLETADA CON INCONSISTENCIAS');
            $this->warn('  → Revisar: storage/logs/restore.log');
            $this->warn('  → Si la BD quedó en estado inconsistente, restaurar');
            $this->warn('    manualmente o repetir el proceso con --force.');
        }
        $this->info('══════════════════════════════════════════════════');
    }
}

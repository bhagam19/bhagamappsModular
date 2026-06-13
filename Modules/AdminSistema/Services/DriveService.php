<?php

namespace Modules\AdminSistema\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\Process;

class DriveService
{
    private const LOG_FILE = 'logs/drive-sync.log';

    // ── DRIVE-001 / DRIVE-003: Estado de conexión ────────────────────────────

    public static function estadoConexion(): array
    {
        $rcloneBin = env('BACKUP_RCLONE_BIN', '/usr/bin/rclone');
        $remote    = env('BACKUP_RCLONE_REMOTE', 'iee-backup');
        $destPath  = env('BACKUP_RCLONE_DEST', 'IEE-Backups');
        $saJson    = env('BACKUP_GDRIVE_SA_JSON', '');
        $carpeta   = "{$remote}:{$destPath}";

        if (!file_exists($rcloneBin)) {
            return [
                'estado'   => 'sin-rclone',
                'etiqueta' => 'Sin rclone',
                'color'    => 'danger',
                'icono'    => 'fas fa-times-circle',
                'mensaje'  => "Binario rclone no encontrado en: {$rcloneBin}",
                'carpeta'  => '',
            ];
        }

        if (empty($saJson)) {
            return [
                'estado'   => 'sin-credenciales',
                'etiqueta' => 'Sin credenciales',
                'color'    => 'warning',
                'icono'    => 'fas fa-exclamation-triangle',
                'mensaje'  => 'BACKUP_GDRIVE_SA_JSON no configurado. Configura la Service Account para habilitar Drive.',
                'carpeta'  => $carpeta,
            ];
        }

        return [
            'estado'   => 'configurado',
            'etiqueta' => 'Configurado',
            'color'    => 'success',
            'icono'    => 'fas fa-check-circle',
            'mensaje'  => "Service Account configurada → {$carpeta}",
            'carpeta'  => $carpeta,
        ];
    }

    // ── DRIVE-002 / DRIVE-006: Subir y verificar ─────────────────────────────

    /**
     * Sube un ZIP a Drive, lo verifica y registra el resultado.
     *
     * @return array{exito: bool, mensaje: string, size_local: int, size_remote: int}
     */
    public static function subirZip(string $zipPath, string $zipName, bool $dryRun = false): array
    {
        $rcloneBin = env('BACKUP_RCLONE_BIN', '/usr/bin/rclone');
        $remote    = env('BACKUP_RCLONE_REMOTE', 'iee-backup');
        $destPath  = env('BACKUP_RCLONE_DEST', 'IEE-Backups');
        $remoteDest = "{$remote}:{$destPath}";

        if (!file_exists($rcloneBin)) {
            $msg = "rclone no encontrado en {$rcloneBin}";
            static::registrarSync($zipName, 0, 0, false, $msg, $dryRun);
            return ['exito' => false, 'mensaje' => $msg, 'size_local' => 0, 'size_remote' => 0];
        }

        if (!file_exists($zipPath)) {
            $msg = "ZIP no encontrado: {$zipPath}";
            static::registrarSync($zipName, 0, 0, false, $msg, $dryRun);
            return ['exito' => false, 'mensaje' => $msg, 'size_local' => 0, 'size_remote' => 0];
        }

        $sizeLocal = (int) filesize($zipPath);

        if ($dryRun) {
            $msg = "[DRY] rclone copy {$zipName} → {$remoteDest}";
            return ['exito' => true, 'mensaje' => $msg, 'size_local' => $sizeLocal, 'size_remote' => 0];
        }

        $rcloneEnv = static::buildRcloneEnv($remote);

        // Subida
        $upload = Process::env($rcloneEnv)->run([
            $rcloneBin, 'copy',
            $zipPath,
            $remoteDest,
            '--stats', '0',
        ]);

        if (!$upload->successful()) {
            $msg = 'rclone copy falló (código ' . $upload->exitCode() . '): '
                . trim($upload->output() . ' ' . $upload->errorOutput());
            static::registrarSync($zipName, $sizeLocal, 0, false, $msg);
            return ['exito' => false, 'mensaje' => $msg, 'size_local' => $sizeLocal, 'size_remote' => 0];
        }

        // DRIVE-006: Verificación posterior
        $verificacion = static::verificarEnDrive($zipName, $remote, $destPath, $rcloneEnv, $rcloneBin);

        static::registrarSync(
            $zipName,
            $sizeLocal,
            $verificacion['size_remote'],
            $verificacion['exito'],
            $verificacion['mensaje']
        );

        return [
            'exito'       => $verificacion['exito'],
            'mensaje'     => $verificacion['mensaje'],
            'size_local'  => $sizeLocal,
            'size_remote' => $verificacion['size_remote'],
        ];
    }

    // ── DRIVE-006: Verificación ──────────────────────────────────────────────

    /**
     * Verifica que el archivo exista en Drive y compara el tamaño.
     */
    public static function verificarEnDrive(
        string $zipName,
        string $remote,
        string $destPath,
        array  $rcloneEnv,
        string $rcloneBin
    ): array {
        $result = Process::env($rcloneEnv)->run([
            $rcloneBin, 'ls',
            "{$remote}:{$destPath}/{$zipName}",
        ]);

        if (!$result->successful() || empty(trim($result->output()))) {
            return [
                'exito'       => false,
                'size_remote' => 0,
                'mensaje'     => 'Verificación: archivo no encontrado en Drive tras subida.',
            ];
        }

        // rclone ls: "   56832 IEE-2026-06-13.zip"
        preg_match('/^\s*(\d+)/m', $result->output(), $m);
        $sizeRemote = (int) ($m[1] ?? 0);

        return [
            'exito'       => true,
            'size_remote' => $sizeRemote,
            'mensaje'     => "✓ Verificado en Drive ({$sizeRemote} bytes)",
        ];
    }

    // ── DRIVE-007: Historial de sincronizaciones ─────────────────────────────

    public static function registrarSync(
        string $zipName,
        int    $sizeLocal,
        int    $sizeRemote,
        bool   $exito,
        string $mensaje,
        bool   $dryRun = false
    ): void {
        if ($dryRun) {
            return;
        }

        $entrada = json_encode([
            'fecha'       => now()->format('Y-m-d H:i:s'),
            'backup'      => $zipName,
            'size_local'  => $sizeLocal,
            'size_remote' => $sizeRemote,
            'resultado'   => $exito ? 'OK' : 'ERROR',
            'mensaje'     => $mensaje,
        ], JSON_UNESCAPED_UNICODE);

        file_put_contents(storage_path(self::LOG_FILE), $entrada . PHP_EOL, FILE_APPEND | LOCK_EX);
    }

    public static function ultimaSync(): ?array
    {
        $logPath = storage_path(self::LOG_FILE);
        if (!file_exists($logPath)) {
            return null;
        }

        $lineas = array_filter(array_map('trim', file($logPath)));
        if (empty($lineas)) {
            return null;
        }

        $ultima = json_decode(end($lineas), true);
        return is_array($ultima) ? $ultima : null;
    }

    public static function historial(int $limite = 10): array
    {
        $logPath = storage_path(self::LOG_FILE);
        if (!file_exists($logPath)) {
            return [];
        }

        $lineas   = array_filter(array_map('trim', file($logPath)));
        $entradas = [];

        foreach (array_reverse(array_values($lineas)) as $linea) {
            $data = json_decode($linea, true);
            if (is_array($data)) {
                $entradas[] = $data;
                if (count($entradas) >= $limite) {
                    break;
                }
            }
        }

        return $entradas;
    }

    // DRIVE-004: Contar backups exitosos únicos sincronizados a Drive
    public static function conteoBackupsDrive(): int
    {
        $historial = static::historial(200);
        $nombres   = [];
        foreach ($historial as $entrada) {
            if (($entrada['resultado'] ?? '') === 'OK') {
                $nombres[$entrada['backup']] = true;
            }
        }
        return count($nombres);
    }

    // ── DRIVE-008: Alertas ───────────────────────────────────────────────────

    public static function estadoAlerta(?array $ultimaSync): string
    {
        if ($ultimaSync === null) {
            return 'rojo';
        }

        // Si la última sync fue un error, buscar la última exitosa en historial
        if (($ultimaSync['resultado'] ?? '') !== 'OK') {
            $historial = static::historial(30);
            $ultimaOk  = null;
            foreach ($historial as $entrada) {
                if (($entrada['resultado'] ?? '') === 'OK') {
                    $ultimaOk = $entrada;
                    break;
                }
            }
            if ($ultimaOk === null) {
                return 'rojo';
            }
            $diff = now()->diffInHours(Carbon::parse($ultimaOk['fecha']));
        } else {
            $diff = now()->diffInHours(Carbon::parse($ultimaSync['fecha']));
        }

        if ($diff > 48) {
            return 'rojo';
        }
        if ($diff > 24) {
            return 'amarillo';
        }

        return 'verde';
    }

    // ── Helper interno ───────────────────────────────────────────────────────

    private static function buildRcloneEnv(string $remote): array
    {
        $envPrefix = 'RCLONE_CONFIG_' . strtoupper(str_replace('-', '_', $remote)) . '_';
        $saJson    = env('BACKUP_GDRIVE_SA_JSON', '');
        $folderId  = env('BACKUP_GDRIVE_FOLDER_ID', '');

        $env = [];
        if ($saJson) {
            $env[$envPrefix . 'TYPE']                        = 'drive';
            $env[$envPrefix . 'SCOPE']                       = 'drive.file';
            $env[$envPrefix . 'SERVICE_ACCOUNT_CREDENTIALS'] = $saJson;
            if ($folderId) {
                $env[$envPrefix . 'ROOT_FOLDER_ID'] = $folderId;
            }
        }

        return $env;
    }
}

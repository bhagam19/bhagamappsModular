<?php

namespace Modules\AdminSistema\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\Process;

/**
 * Servicio de integración con Google Drive.
 *
 * Métodos disponibles por contexto:
 *  - proc_open habilitado (CLI)  : rclone via Process facade
 *  - proc_open deshabilitado (web): API nativa Google Drive v3 (cURL + JWT)
 *
 * subirZip() detecta automáticamente el método disponible y NUNCA lanza
 * excepción — siempre devuelve ['exito' => bool, 'mensaje' => string, ...].
 */
class DriveService
{
    private const LOG_FILE    = 'logs/drive-sync.log';
    private const OAUTH_URL   = 'https://oauth2.googleapis.com/token';
    private const UPLOAD_URL  = 'https://www.googleapis.com/upload/drive/v3/files';
    private const DRIVE_SCOPE = 'https://www.googleapis.com/auth/drive.file';

    // ── DRIVE-001/003: Estado de conexión ────────────────────────────────────

    public static function estadoConexion(): array
    {
        $rcloneBin = env('BACKUP_RCLONE_BIN', '/usr/bin/rclone');
        $remote    = env('BACKUP_RCLONE_REMOTE', 'iee-backup');
        $destPath  = env('BACKUP_RCLONE_DEST', 'IEE-Backups');
        $saJson    = env('BACKUP_GDRIVE_SA_JSON', '');
        $carpeta   = "{$remote}:{$destPath}";

        $procOpen = function_exists('proc_open');
        $nativo   = function_exists('curl_exec') && function_exists('openssl_sign');

        // Sin ningún mecanismo disponible
        if (!$procOpen && !$nativo) {
            return [
                'estado'   => 'sin-soporte',
                'etiqueta' => 'Sin soporte',
                'color'    => 'danger',
                'icono'    => 'fas fa-times-circle',
                'mensaje'  => 'proc_open y cURL deshabilitados. Drive no disponible en este hosting.',
                'carpeta'  => '',
                'metodo'   => 'ninguno',
            ];
        }

        // Método rclone (proc_open disponible)
        if ($procOpen) {
            if (!file_exists($rcloneBin)) {
                return [
                    'estado'   => 'sin-rclone',
                    'etiqueta' => 'Sin rclone',
                    'color'    => 'danger',
                    'icono'    => 'fas fa-times-circle',
                    'mensaje'  => "rclone no encontrado en: {$rcloneBin}",
                    'carpeta'  => '',
                    'metodo'   => 'rclone',
                ];
            }
            if (empty($saJson)) {
                return [
                    'estado'   => 'sin-credenciales',
                    'etiqueta' => 'Sin credenciales',
                    'color'    => 'warning',
                    'icono'    => 'fas fa-exclamation-triangle',
                    'mensaje'  => 'BACKUP_GDRIVE_SA_JSON no configurado.',
                    'carpeta'  => $carpeta,
                    'metodo'   => 'rclone',
                ];
            }
            return [
                'estado'   => 'configurado',
                'etiqueta' => 'Configurado (rclone)',
                'color'    => 'success',
                'icono'    => 'fas fa-check-circle',
                'mensaje'  => "rclone → {$carpeta}",
                'carpeta'  => $carpeta,
                'metodo'   => 'rclone',
            ];
        }

        // Método API nativa (cURL + openssl, proc_open no disponible)
        if (empty($saJson)) {
            return [
                'estado'   => 'sin-credenciales',
                'etiqueta' => 'Sin credenciales',
                'color'    => 'warning',
                'icono'    => 'fas fa-exclamation-triangle',
                'mensaje'  => 'BACKUP_GDRIVE_SA_JSON no configurado (método disponible: API nativa cURL).',
                'carpeta'  => $carpeta,
                'metodo'   => 'api-nativa',
            ];
        }

        return [
            'estado'   => 'configurado',
            'etiqueta' => 'Configurado (API)',
            'color'    => 'success',
            'icono'    => 'fas fa-check-circle',
            'mensaje'  => "Google Drive API nativa (cURL) → {$carpeta}",
            'carpeta'  => $carpeta,
            'metodo'   => 'api-nativa',
        ];
    }

    // ── DRIVE-002/006: Subida principal ──────────────────────────────────────

    /**
     * Sube un ZIP a Google Drive usando el método disponible en el contexto actual.
     *
     * Detección automática:
     *  1. proc_open disponible → rclone (CLI/cron)
     *  2. proc_open no disponible, cURL + openssl disponibles → API nativa (web)
     *  3. Ninguno disponible → fallo silencioso (sin excepción)
     *
     * Nunca lanza excepción. El backup local siempre tiene prioridad.
     *
     * @return array{exito: bool, mensaje: string, size_local: int, size_remote: int}
     */
    public static function subirZip(string $zipPath, string $zipName, bool $dryRun = false): array
    {
        $remote   = env('BACKUP_RCLONE_REMOTE', 'iee-backup');
        $destPath = env('BACKUP_RCLONE_DEST', 'IEE-Backups');

        if (!file_exists($zipPath)) {
            return ['exito' => false, 'mensaje' => "ZIP no encontrado: {$zipPath}", 'size_local' => 0, 'size_remote' => 0];
        }

        $sizeLocal = (int) filesize($zipPath);

        if ($dryRun) {
            $metodo = function_exists('proc_open') ? 'rclone' : 'API nativa cURL';
            return [
                'exito'       => true,
                'mensaje'     => "[DRY] {$metodo}: {$zipName} → {$remote}:{$destPath}",
                'size_local'  => $sizeLocal,
                'size_remote' => 0,
            ];
        }

        // Elegir método según disponibilidad del contexto PHP
        if (function_exists('proc_open')) {
            $result = static::subirConRclone($zipPath, $zipName, $remote, $destPath, $sizeLocal);
        } elseif (function_exists('curl_exec') && function_exists('openssl_sign')) {
            $saJson = env('BACKUP_GDRIVE_SA_JSON', '');
            if (empty($saJson)) {
                // Sin credenciales: fallo silencioso, NO registrar en log
                return ['exito' => false, 'mensaje' => 'BACKUP_GDRIVE_SA_JSON no configurado. Drive omitido.', 'size_local' => $sizeLocal, 'size_remote' => 0];
            }
            $result = static::subirConApiNativa($zipPath, $zipName, $sizeLocal);
        } else {
            // Nada disponible: fallo silencioso
            return ['exito' => false, 'mensaje' => 'Drive no disponible (proc_open y cURL deshabilitados).', 'size_local' => $sizeLocal, 'size_remote' => 0];
        }

        // Registrar en log solo cuando se intentó la conexión con Drive
        static::registrarSync($zipName, $result['size_local'], $result['size_remote'], $result['exito'], $result['mensaje']);

        return $result;
    }

    // ── Método 1: rclone (proc_open disponible) ──────────────────────────────

    private static function subirConRclone(
        string $zipPath,
        string $zipName,
        string $remote,
        string $destPath,
        int    $sizeLocal
    ): array {
        $rcloneBin  = env('BACKUP_RCLONE_BIN', '/usr/bin/rclone');
        $remoteDest = "{$remote}:{$destPath}";
        $rcloneEnv  = static::buildRcloneEnv($remote);

        try {
            $upload = Process::env($rcloneEnv)->run([
                $rcloneBin, 'copy', $zipPath, $remoteDest, '--stats', '0',
            ]);
        } catch (\Throwable $e) {
            // proc_open deshabilitado u otro problema del sistema — no propagar
            return [
                'exito'       => false,
                'mensaje'     => 'rclone no ejecutable en este contexto: ' . $e->getMessage(),
                'size_local'  => $sizeLocal,
                'size_remote' => 0,
            ];
        }

        if (!$upload->successful()) {
            $msg = 'rclone copy falló (código ' . $upload->exitCode() . '): '
                 . trim($upload->output() . ' ' . $upload->errorOutput());
            return ['exito' => false, 'mensaje' => $msg, 'size_local' => $sizeLocal, 'size_remote' => 0];
        }

        // Verificar via rclone ls (DRIVE-006)
        $sizeRemote = $sizeLocal;
        try {
            $verify = Process::env($rcloneEnv)->run([
                $rcloneBin, 'ls', "{$remote}:{$destPath}/{$zipName}",
            ]);
            preg_match('/^\s*(\d+)/m', $verify->output(), $m);
            if (isset($m[1])) {
                $sizeRemote = (int) $m[1];
            }
        } catch (\Throwable) {
            // Verificación opcional — no bloquear si falla
        }

        return [
            'exito'       => true,
            'mensaje'     => "✓ Subido y verificado con rclone ({$sizeRemote} bytes en Drive)",
            'size_local'  => $sizeLocal,
            'size_remote' => $sizeRemote,
        ];
    }

    // ── Método 2: API nativa Google Drive v3 (cURL + JWT) ───────────────────

    private static function subirConApiNativa(string $zipPath, string $zipName, int $sizeLocal): array
    {
        $saJson = env('BACKUP_GDRIVE_SA_JSON', '');
        $sa     = json_decode($saJson, true);

        if (!is_array($sa) || empty($sa['private_key']) || empty($sa['client_email'])) {
            return [
                'exito'       => false,
                'mensaje'     => 'BACKUP_GDRIVE_SA_JSON inválido (faltan private_key o client_email).',
                'size_local'  => $sizeLocal,
                'size_remote' => 0,
            ];
        }

        $accessToken = static::obtenerAccessToken($sa);
        if ($accessToken === null) {
            return [
                'exito'       => false,
                'mensaje'     => 'No se pudo obtener token OAuth2 de Google. Verificar Service Account.',
                'size_local'  => $sizeLocal,
                'size_remote' => 0,
            ];
        }

        return static::uploadMultipart($zipPath, $zipName, $accessToken, $sizeLocal);
    }

    /**
     * Obtiene access token de Google OAuth2 usando JWT de Service Account.
     * Requiere: openssl_sign() (disponible en web PHP).
     */
    private static function obtenerAccessToken(array $sa): ?string
    {
        $now    = time();
        $header = static::base64url(json_encode(['alg' => 'RS256', 'typ' => 'JWT']));
        $claims = static::base64url(json_encode([
            'iss'   => $sa['client_email'],
            'scope' => self::DRIVE_SCOPE,
            'aud'   => self::OAUTH_URL,
            'iat'   => $now,
            'exp'   => $now + 3600,
        ]));

        $sigInput = "{$header}.{$claims}";

        if (!openssl_sign($sigInput, $signature, $sa['private_key'], OPENSSL_ALGO_SHA256)) {
            return null;
        }

        $jwt  = "{$sigInput}." . static::base64url($signature);
        $resp = static::httpPost(
            self::OAUTH_URL,
            http_build_query([
                'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
                'assertion'  => $jwt,
            ]),
            ['Content-Type: application/x-www-form-urlencoded']
        );

        if ($resp['http_code'] !== 200 || empty($resp['body'])) {
            return null;
        }

        $data = json_decode($resp['body'], true);
        return $data['access_token'] ?? null;
    }

    /**
     * Sube el ZIP a Drive usando multipart upload (Drive API v3).
     * Requiere: curl_exec() (disponible en web PHP).
     * DRIVE-006: verificación incluida en la respuesta de la API.
     */
    private static function uploadMultipart(
        string $zipPath,
        string $zipName,
        string $accessToken,
        int    $sizeLocal
    ): array {
        $folderId = env('BACKUP_GDRIVE_FOLDER_ID', '');
        $metadata = ['name' => $zipName];
        if ($folderId) {
            $metadata['parents'] = [$folderId];
        }

        $fileContent = file_get_contents($zipPath);
        if ($fileContent === false) {
            return ['exito' => false, 'mensaje' => 'No se pudo leer el ZIP para la subida.', 'size_local' => $sizeLocal, 'size_remote' => 0];
        }

        $boundary = 'b_' . md5(uniqid('', true));
        $body     = "--{$boundary}\r\n"
                  . "Content-Type: application/json; charset=UTF-8\r\n\r\n"
                  . json_encode($metadata) . "\r\n"
                  . "--{$boundary}\r\n"
                  . "Content-Type: application/zip\r\n\r\n"
                  . $fileContent . "\r\n"
                  . "--{$boundary}--";

        $resp = static::httpPost(
            self::UPLOAD_URL . '?uploadType=multipart&fields=id,size',
            $body,
            [
                "Authorization: Bearer {$accessToken}",
                "Content-Type: multipart/related; boundary={$boundary}",
            ]
        );

        if (empty($resp['body']) || !in_array($resp['http_code'], [200, 201])) {
            $detail = $resp['error'] ?: "HTTP {$resp['http_code']}";
            if (!empty($resp['body'])) {
                $errData = json_decode($resp['body'], true);
                $detail  = $errData['error']['message'] ?? $detail;
            }
            return ['exito' => false, 'mensaje' => "Drive API: {$detail}", 'size_local' => $sizeLocal, 'size_remote' => 0];
        }

        $data       = json_decode($resp['body'], true);
        $fileId     = $data['id'] ?? null;
        $sizeRemote = (int) ($data['size'] ?? $sizeLocal);

        if (!$fileId) {
            return ['exito' => false, 'mensaje' => 'Drive API: respuesta sin file ID.', 'size_local' => $sizeLocal, 'size_remote' => 0];
        }

        return [
            'exito'       => true,
            'mensaje'     => "✓ Subido via API nativa Google Drive (id={$fileId}, {$sizeRemote} bytes)",
            'size_local'  => $sizeLocal,
            'size_remote' => $sizeRemote,
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

    // ── Helpers ──────────────────────────────────────────────────────────────

    /**
     * POST HTTP via cURL. Disponible en web PHP aunque proc_open esté deshabilitado.
     */
    private static function httpPost(string $url, string $body, array $headers): array
    {
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => $body,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_HTTPHEADER     => $headers,
            CURLOPT_TIMEOUT        => 60,
        ]);

        $response  = curl_exec($ch);
        $httpCode  = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        return [
            'body'      => is_string($response) ? $response : '',
            'http_code' => $httpCode,
            'error'     => $curlError,
        ];
    }

    private static function base64url(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

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

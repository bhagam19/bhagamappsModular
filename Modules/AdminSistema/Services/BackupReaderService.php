<?php

namespace Modules\AdminSistema\Services;

class BackupReaderService
{
    public static function listar(): array
    {
        $zips = glob(base_path('backups/IEE-????-??-??.zip')) ?: [];
        rsort($zips);

        $backups = [];
        foreach ($zips as $zip) {
            if (!preg_match('/IEE-(\d{4}-\d{2}-\d{2})\.zip$/', $zip, $m)) {
                continue;
            }
            $fecha = $m[1];
            $meta  = static::leerMetadata($fecha);

            $backups[] = [
                'fecha'    => $fecha,
                'zip_name' => basename($zip),
                'zip_size' => file_exists($zip) ? filesize($zip) : 0,
                'meta'     => $meta,
            ];
        }

        return $backups;
    }

    public static function leerMetadata(string $fecha): ?array
    {
        $path = base_path("backups/{$fecha}/metadata.json");
        if (!file_exists($path)) {
            return null;
        }

        $decoded = json_decode(file_get_contents($path), true);
        return is_array($decoded) ? $decoded : null;
    }

    public static function ultimoBackup(): ?array
    {
        $backups = static::listar();
        return $backups[0] ?? null;
    }

    public static function estadoAlerta(?array $ultimo): string
    {
        if ($ultimo === null) {
            return 'rojo';
        }

        $fecha = $ultimo['meta']['generado_en'] ?? $ultimo['fecha'] . ' 02:00:00';
        $diff  = now()->diffInHours(\Carbon\Carbon::parse($fecha));

        if ($diff > 48) {
            return 'rojo';
        }
        if ($diff > 24) {
            return 'amarillo';
        }

        return 'verde';
    }

    public static function proximaEjecucion(): string
    {
        $now  = now();
        $next = $now->copy()->setTime(2, 0, 0);
        if ($now->gte($next)) {
            $next->addDay();
        }

        return $next->format('Y-m-d H:i');
    }

    public static function formatSize(int $bytes): string
    {
        if ($bytes >= 1048576) {
            return round($bytes / 1048576, 2) . ' MB';
        }
        if ($bytes >= 1024) {
            return round($bytes / 1024, 1) . ' KB';
        }
        return $bytes . ' B';
    }
}

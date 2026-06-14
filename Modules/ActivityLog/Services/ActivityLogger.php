<?php

namespace Modules\ActivityLog\Services;

use Modules\ActivityLog\Entities\ActivityLog;

class ActivityLogger
{
    /**
     * Registra una acción en el log de auditoría institucional.
     *
     * @param  string      $modulo          Ej: 'Inventario', 'Users', 'Backups'
     * @param  string      $accion          Ej: 'crear', 'editar', 'eliminar'
     * @param  string      $descripcion     Texto descriptivo de la acción
     * @param  string|null $tipoObjeto      Ej: 'Bien', 'Usuario', 'Snapshot'
     * @param  int|null    $objetoId        ID del objeto afectado
     * @param  array|null  $datosAnteriores Snapshot del estado previo (JSON)
     * @param  array|null  $datosNuevos     Snapshot del estado nuevo (JSON)
     */
    public static function log(
        string  $modulo,
        string  $accion,
        string  $descripcion,
        ?string $tipoObjeto      = null,
        ?int    $objetoId        = null,
        ?array  $datosAnteriores = null,
        ?array  $datosNuevos     = null,
    ): void {
        try {
            $user    = auth()->user();
            $request = request();

            ActivityLog::create([
                'user_id'          => $user?->id,
                'modulo'           => $modulo,
                'tipo_objeto'      => $tipoObjeto,
                'objeto_id'        => $objetoId,
                'accion'           => $accion,
                'descripcion'      => $descripcion,
                'datos_anteriores' => $datosAnteriores,
                'datos_nuevos'     => $datosNuevos,
                'ip_address'       => $request?->ip(),
                'user_agent'       => substr((string) $request?->userAgent(), 0, 500),
                'created_at'       => now(),
            ]);
        } catch (\Throwable) {
            // El log nunca debe interrumpir el flujo principal
        }
    }
}

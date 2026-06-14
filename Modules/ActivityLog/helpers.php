<?php

use Modules\ActivityLog\Services\ActivityLogger;

if (!function_exists('activity_log')) {
    /**
     * Helper global de auditoría institucional.
     */
    function activity_log(
        string  $modulo,
        string  $accion,
        string  $descripcion,
        ?string $tipoObjeto      = null,
        ?int    $objetoId        = null,
        ?array  $datosAnteriores = null,
        ?array  $datosNuevos     = null,
    ): void {
        ActivityLogger::log(
            modulo:          $modulo,
            accion:          $accion,
            descripcion:     $descripcion,
            tipoObjeto:      $tipoObjeto,
            objetoId:        $objetoId,
            datosAnteriores: $datosAnteriores,
            datosNuevos:     $datosNuevos,
        );
    }
}

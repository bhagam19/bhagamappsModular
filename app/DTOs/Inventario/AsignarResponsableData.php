<?php

namespace App\DTOs\Inventario;

final class AsignarResponsableData
{
    public function __construct(
        public readonly int    $user_id,
        public readonly string $fecha_asignacion,
    ) {
    }
}

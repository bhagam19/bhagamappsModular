<?php

namespace App\DTOs\Inventario;

final class TransferirResponsableData
{
    public function __construct(
        public readonly int    $user_id,
        public readonly string $fecha_asignacion,
        public readonly string $fecha_retiro_anterior,
    ) {
    }
}

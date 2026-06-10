<?php

namespace App\Actions\Inventario;

use App\DTOs\Inventario\AsignarResponsableData;
use App\Models\Inventario\Bien;
use App\Models\Inventario\BienResponsable;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use LogicException;

class AsignarResponsableBienAction
{
    public function execute(Bien $bien, AsignarResponsableData $data): BienResponsable
    {
        return DB::transaction(function () use ($bien, $data) {
            // RI-003: no puede existir más de un responsable activo
            if ($bien->responsableActual()->exists()) {
                throw new LogicException('El bien ya tiene un responsable vigente. Use transferir para cambiar el custodio.');
            }

            return BienResponsable::create([
                'bien_id'               => $bien->id,
                'user_id'               => $data->user_id,
                'fecha_asignacion'      => $data->fecha_asignacion,
                'fecha_retiro'          => null,
                'asignado_por_user_id'  => Auth::id(),
            ]);
        });
    }
}

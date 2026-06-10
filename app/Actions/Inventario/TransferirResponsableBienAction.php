<?php

namespace App\Actions\Inventario;

use App\DTOs\Inventario\TransferirResponsableData;
use App\Models\Inventario\Bien;
use App\Models\Inventario\BienResponsable;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use LogicException;

class TransferirResponsableBienAction
{
    public function execute(Bien $bien, TransferirResponsableData $data): BienResponsable
    {
        return DB::transaction(function () use ($bien, $data) {
            $actual = $bien->responsableActual()->lockForUpdate()->first();

            if (! $actual) {
                throw new LogicException('El bien no tiene un responsable vigente para transferir.');
            }

            // RI-002: fecha_retiro no puede ser anterior a fecha_asignacion
            if ($data->fecha_retiro_anterior < $actual->fecha_asignacion->toDateString()) {
                throw new LogicException('La fecha de retiro no puede ser anterior a la fecha de asignación del responsable actual.');
            }

            $actual->update(['fecha_retiro' => $data->fecha_retiro_anterior]);

            return BienResponsable::create([
                'bien_id'              => $bien->id,
                'user_id'              => $data->user_id,
                'fecha_asignacion'     => $data->fecha_asignacion,
                'fecha_retiro'         => null,
                'asignado_por_user_id' => Auth::id(),
            ]);
        });
    }
}

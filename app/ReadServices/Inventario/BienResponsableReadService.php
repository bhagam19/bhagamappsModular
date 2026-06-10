<?php

namespace App\ReadServices\Inventario;

use App\Models\Inventario\Bien;
use App\Models\Inventario\BienResponsable;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class BienResponsableReadService
{
    public function historialPorBien(Bien $bien): Collection
    {
        return BienResponsable::with(['usuario', 'asignadoPor'])
            ->where('bien_id', $bien->id)
            ->orderByDesc('fecha_asignacion')
            ->get();
    }

    public function responsableActual(Bien $bien): ?BienResponsable
    {
        return BienResponsable::with(['usuario', 'asignadoPor'])
            ->where('bien_id', $bien->id)
            ->whereNull('fecha_retiro')
            ->first();
    }

    public function bienesAsignados(User $usuario): LengthAwarePaginator
    {
        return BienResponsable::with(['bien', 'bien.categoria', 'bien.ubicacion'])
            ->where('user_id', $usuario->id)
            ->whereNull('fecha_retiro')
            ->orderByDesc('fecha_asignacion')
            ->paginate(20)
            ->withQueryString();
    }

    public function usuariosParaSelector(): Collection
    {
        return User::orderBy('name')->get(['id', 'name', 'email']);
    }
}

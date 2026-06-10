<?php

namespace Modules\Inventario\Http\Controllers;

use Modules\Inventario\Entities\Bien;
use Modules\User\Entities\User;

use Barryvdh\Snappy\Facades\SnappyPdf;

class ActaPDFController
{
    public function show($userId)
    {
        $user = User::findOrFail($userId);

        $bienes = Bien::with(['detalle', 'estado', 'dependencia'])
            ->join('dependencias', 'bienes.dependencia_id', '=', 'dependencias.id')
            ->where('dependencias.user_id', $userId)
            ->orderBy('dependencias.nombre')
            ->orderBy('bienes.nombre')
            ->select('bienes.*')
            ->get();

        $nombreCompleto = mb_strtoupper($user->nombres . ' ' . $user->apellidos, 'UTF-8');
        $miFecha = now()->translatedFormat('d \d\e F \d\e Y');

        $pdf = SnappyPdf::loadView('inventario::livewire.actas.actaPDF', compact('bienes', 'nombreCompleto', 'miFecha', 'user'));

        return $pdf->download('acta_entrega_' . $userId . '.pdf');
    }
}

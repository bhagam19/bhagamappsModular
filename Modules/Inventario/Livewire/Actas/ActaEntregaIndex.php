<?php

namespace Modules\Inventario\Livewire\Actas;

use Livewire\Component;
use Modules\Users\Models\User;
use Modules\Inventario\Entities\Bien;
use Modules\Inventario\Livewire\Actas\ActaPrinter;

class ActaEntregaIndex extends Component
{
    public $userId;
    public $user;
    public $users;
    public $bienes;
    public $nombreCompleto;
    public $miFecha;
    public $contenidoActa;
    public int $itemsPorPagina=20;

    public bool $mostrarSelector = false;

    public function mount()
    {
        abort_unless(auth()->user()->hasPermission('ver-acta-entrega'), 403);

        $this->bienes = collect();
        $this->contenidoActa = null;
        $this->miFecha = now()->translatedFormat('d \d\e F \d\e Y');

        $usuario = auth()->user();

        if ($usuario->hasRole('Administrador') || $usuario->hasRole('Rector')) {
            $this->mostrarSelector = true;
            $this->users = User::orderBy('nombres')->get();
            $this->userId = null;
        } else {
            $this->mostrarSelector = false;
            $this->users = collect();
            $this->userId = $usuario->id;
        }

        // Solo generamos si ya hay un userId
        if ($this->userId) {
            $this->generarActa();
        }
    }

    public function updatedUserId($value)
    {
        $this->generarActa();
    }

    public function updatedItemsPorPagina()
    {
        if ($this->userId) {
            $this->generarActa();
        }
    }

    public function generarActa()
    {
        
        $this->user = User::find($this->userId);
        $this->miFecha = now()->translatedFormat('d \d\e F \d\e Y');

        if ($this->user) {
            $this->bienes = Bien::with(['detalle', 'estado', 'dependencia'])
                ->join('dependencias', 'bienes.dependencia_id', '=', 'dependencias.id')
                ->where('bienes.usuario_id', $this->userId)
                ->orderBy('dependencias.nombre')
                ->orderBy('bienes.nombre')
                ->select('bienes.*')
                ->get();

            //$this->itemsPorPagina = $this->bienes->count();

            $this->nombreCompleto = mb_strtoupper($this->user->nombres . ' ' . $this->user->apellidos, 'UTF-8');

            $this->contenidoActa = $this->bienes->isNotEmpty()
                ? ActaPrinter::renderActaPaginada(
                    $this->bienes,
                    $this->user,
                    $this->nombreCompleto,
                    $this->miFecha,
                    $this->itemsPorPagina
                )
                : null;
        }
    }

    public function render()
    {
        return view('inventario::livewire.actas.acta-entrega-index', [
            'users' => $this->users,
            'userId' => $this->userId,
            'user' => $this->user,
            'nombreCompleto' => $this->nombreCompleto ?? '',
            'miFecha' => $this->miFecha ?? now()->format('d/m/Y'),
            'bienes' => $this->bienes ?? collect(),
            'contenidoActa' => $this->contenidoActa,
        ])->layout('layouts.app');
    }
}

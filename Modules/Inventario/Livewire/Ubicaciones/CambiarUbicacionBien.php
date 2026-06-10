<?php

namespace Modules\Inventario\Livewire\Ubicaciones;

use Livewire\Component;
use Modules\Inventario\Entities\Bien;
use Modules\Inventario\Entities\Ubicacion;
use Modules\Inventario\Entities\HistorialUbicacionBien;

class CambiarUbicacionBien extends Component
{
    public int $bienId;
    public ?int $nuevaUbicacionId = null;
    public string $fechaMovimiento = '';
    public string $observaciones = '';

    public array $ubicaciones = [];

    public function mount(int $bienId): void
    {
        abort_unless(auth()->user()?->hasPermission('cambiar-ubicacion-bienes'), 403);
        $this->bienId         = $bienId;
        $this->fechaMovimiento = now()->toDateString();
        $this->ubicaciones    = Ubicacion::orderBy('nombre')->pluck('nombre', 'id')->toArray();
    }

    public function guardar(): void
    {
        abort_unless(auth()->user()?->hasPermission('cambiar-ubicacion-bienes'), 403);

        $this->validate([
            'nuevaUbicacionId' => 'required|exists:ubicaciones,id',
            'fechaMovimiento'  => 'required|date',
            'observaciones'    => 'nullable|string|max:500',
        ]);

        $bien = Bien::with('ubicacionActual')->findOrFail($this->bienId);

        HistorialUbicacionBien::create([
            'bien_id'              => $this->bienId,
            'ubicacion_origen_id'  => $bien->ubicacionActual?->ubicacion_destino_id,
            'ubicacion_destino_id' => $this->nuevaUbicacionId,
            'user_id'              => auth()->id(),
            'fecha_movimiento'     => $this->fechaMovimiento,
            'observaciones'        => $this->observaciones ?: null,
        ]);

        $this->dispatch('mostrar-mensaje', tipo: 'success', mensaje: 'Ubicación actualizada correctamente.');
        $this->dispatch('ubicacion-cambiada');

        $this->nuevaUbicacionId = null;
        $this->observaciones    = '';
        $this->fechaMovimiento  = now()->toDateString();
    }

    public function render()
    {
        $bien = Bien::with('ubicacionActual.ubicacionDestino')->findOrFail($this->bienId);

        return view('inventario::livewire.ubicaciones.cambiar-ubicacion-bien', [
            'bien' => $bien,
        ]);
    }
}

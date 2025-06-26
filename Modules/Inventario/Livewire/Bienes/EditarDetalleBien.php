<?php

namespace Modules\Inventario\Livewire\Bienes;

use Livewire\Component;
use Modules\User\Entities\User;
use Modules\Inventario\Entities\{
    Bien,
    Detalle,
    HistorialModificacionBien
};
use Illuminate\Support\Facades\Notification;
use Modules\Inventario\Livewire\Hmb\NotificacionHmb;
use Illuminate\Support\Arr;

class EditarDetalleBien extends Component
{
    public Bien $bien;
    public $bienId;
    public $editandoDetalle = false;
    public $detalle = [];

    public array $camposDetalle = [
        'car_especial',
        'marca',
        'color',
        'tamano',
        'material',
        'otra'
    ];

    public function toggleEdit()
    {
        $this->editandoDetalle = !$this->editandoDetalle;
    }

    protected $rules = [
        'detalle.car_especial' => 'nullable|string|max:255',
        'detalle.marca'        => 'nullable|string|max:255',
        'detalle.color'        => 'nullable|string|max:255',
        'detalle.tamano'       => 'nullable|string|max:255',
        'detalle.material'     => 'nullable|string|max:255',
        'detalle.otra'         => 'nullable|string|max:255',
    ];

    public function mount($bienId)
    {
        $this->bienId = $bienId;

        $this->bien = Bien::with('detalle')->findOrFail($bienId);

        $this->detalle = Arr::only(optional($this->bien->detalle)->toArray() ?? [], [
            'car_especial',
            'marca',
            'color',
            'tamano',
            'material',
            'otra'
        ]);
    }

    public function actualizar()
    {
        $this->validate();

        $user = auth()->user();

        if (!$user->dependencias->pluck('id')->contains($this->bien->dependencia_id)) {
            return redirect()->route('inventario.bienes.index');
        }

        // Obtener o crear el detalle del bien
        $detalle = Detalle::firstOrNew(['bien_id' => $this->bienId]);

        // Obtener los atributos actuales (excluyendo los irrelevantes)
        $detalleActual = collect($detalle->getAttributes())
            ->only(['car_especial', 'marca', 'color', 'tamano', 'material', 'otra'])
            ->toArray();

        // Mezclar con los nuevos valores del formulario
        $detalleNuevo = array_merge($detalleActual, $this->detalle);

        // Filtrar SOLO los que realmente cambiaron
        $modificaciones = [];
        foreach ($detalleNuevo as $campo => $valorNuevo) {
            $valorAnterior = $detalleActual[$campo] ?? null;

            // Normalizar nulls vacíos o string 'null' → null
            $valorAnterior = $valorAnterior === 'null' ? null : $valorAnterior;
            $valorNuevo = $valorNuevo === 'null' ? null : $valorNuevo;

            // Si cambió, lo agregamos
            if ($valorAnterior != $valorNuevo) {
                $modificaciones[$campo] = [
                    'anterior' => $valorAnterior ?? 'null',
                    'nuevo' => $valorNuevo ?? 'null'
                ];
            }
        }

        // Si no hay modificaciones reales, salir
        if (empty($modificaciones)) {
            $this->toggleEdit();
            session()->flash('info', 'No hubo modificaciones reales.');
            return;
        }

        // Si tiene rol autorizado, guardar directamente
        if ($user->hasRole('Administrador') || $user->hasRole('Rector')) {
            $detalle->fill($this->detalle);
            $detalle->save();
            $this->toggleEdit();

            $this->dispatch('bienActualizado');
            session()->flash('mensaje', 'Detalles actualizados correctamente.');
            return;
        }

        // User sin permiso → guardar solicitud pendiente
        // Verificar si ya existe un cambio pendiente para este bien y este campo
        $yaExiste = HistorialModificacionBien::where('bien_id', $this->bienId)
            ->where('tipo_objeto', 'detalle')
            ->where('estado', 'pendiente')
            ->exists();

        if ($yaExiste) {
            session()->flash('warning', 'Ya hay un cambio pendiente para los detalles de este bien.');
            $this->toggleEdit();
            return;
        }

        // Guardar solicitud pendiente
        $modificacionPendiente = HistorialModificacionBien::create([
            'bien_id' => $this->bien->id,
            'tipo_objeto' => 'detalle',
            'campo' => 'detalle',
            'valor_anterior' => json_encode(array_map(fn($v) => $v['anterior'], $modificaciones), JSON_UNESCAPED_UNICODE),
            'valor_nuevo' => json_encode(array_map(fn($v) => $v['nuevo'], $modificaciones), JSON_UNESCAPED_UNICODE),
            'dependencia_id' => $this->bien->dependencia_id,
            'estado' => 'pendiente',
        ]);

        // Notificar a administradores y rector
        $usersDestino = User::whereHas('role', function ($query) {
            $query->whereIn('nombre', ['Administrador', 'Rector']);
        })->get();

        Notification::send($usersDestino, new NotificacionHmb($modificacionPendiente));

        $this->toggleEdit();
        session()->flash('info', 'El cambio de detalles fue enviado para aprobación.');
    }

    public function detalleTieneModificacionPendiente()
    {
        $user = auth()->user();

        $query = HistorialModificacionBien::where('bien_id', $this->bienId)
            ->where('campo', 'detalle')
            ->where('estado', 'pendiente');

        // Si no es administrador o rector → limitar por dependencias
        if (!in_array($user->role->nombre ?? '', ['Administrador', 'Rector'])) {
            $dependenciaIds = $user->dependencias->pluck('id');
            if ($dependenciaIds->isEmpty()) return null;

            $query->whereIn('dependencia_id', $dependenciaIds);
        }

        return $query->first();
    }


    public function render()
    {
        return view('inventario::livewire.bienes.editar-detalle-bien');
    }
}

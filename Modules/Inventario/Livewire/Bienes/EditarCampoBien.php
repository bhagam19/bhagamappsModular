<?php

namespace Modules\Inventario\Livewire\Bienes;

use Livewire\Component;
use Illuminate\Support\Facades\Notification;
use Modules\Users\Models\User;
use Modules\Inventario\Entities\{
    Bien,
    Estado,
    Almacenamiento,
    Mantenimiento,
    Dependencia,
    Ubicacion,
    Categoria,
    HistorialModificacionBien
};
use Modules\Inventario\Livewire\Hmb\NotificacionHmb;

class EditarCampoBien extends Component
{
    public Bien $bien;
    public $bienId;
    public string $campo;
    public $valor;


    public bool $editando = false;

    public string $tipo;
    public array $opciones;

    public function mount(int $bienId, string $campo, ?string $tipo = null, array $opciones = [])
    {

        $this->bien = Bien::findOrFail($bienId);

        $this->campo = $campo;
        $this->valor = $this->bien->$campo;
        $this->tipo = $tipo ?? $this->inferirTipo($campo);
        $this->opciones = $this->tipo === 'select'
            ? ($opciones ?: $this->cargarOpciones($campo))
            : [];
    }

    protected function inferirTipo(string $campo): string
    {
        return match (true) {
            str_ends_with($campo, '_id') => 'select',
            str_contains($campo, 'observacion') => 'textarea',
            str_contains($campo, 'descripcion') => 'textarea',
            str_contains($campo, 'fecha') => 'date',
            str_contains($campo, 'cantidad'),
            str_contains($campo, 'precio') => 'number',
            default => 'text',
        };
    }

    protected function inferirTabla(): string
    {
        return match ($this->campo) {
            'categoria_id' => 'categorias',
            'estado_id' => 'estados',
            'ubicacion_id' => 'ubicaciones',
            'dependencia_id' => 'dependencias',
            'almacenamiento_id' => 'almacenamientos',
            'mantenimiento_id' => 'mantenimientos',
            'usuario_id' => 'users',
            default => 'opciones', // fallback si no se conoce
        };
    }

    protected function cargarOpciones(string $campo): array
    {
        return match ($campo) {
            'categoria_id' => Categoria::orderBy('nombre')
                ->get()
                ->mapWithKeys(fn($c) => [$c->id => $c->nombre])
                ->toArray(),
            'estado_id' => Estado::pluck('nombre', 'id')->toArray(),
            'ubicacion_id' => Ubicacion::pluck('nombre', 'id')->toArray(),
            'dependencia_id' => Dependencia::pluck('nombre', 'id')->toArray(),
            'almacenamiento_id' => Almacenamiento::pluck('nombre', 'id')->toArray(),
            'mantenimiento_id' => Mantenimiento::pluck('nombre', 'id')->toArray(),
            'usuario_id' => User::orderBy('nombres')
                ->orderBy('apellidos')
                ->get()
                ->mapWithKeys(function ($user) {
                    return [$user->id => trim("{$user->nombres} {$user->apellidos}")];
                })
                ->toArray(),
            default => [],
        };
    }

    public function actualizar()
    {
        $rules = match ($this->tipo) {
            'number' => ['valor' => 'nullable|numeric'],
            'date' => ['valor' => 'nullable|date'],
            'select' => ['valor' => 'nullable|exists:' . $this->inferirTabla() . ',id'],
            default => ['valor' => 'nullable|string|max:255'],
        };

        $this->validate($rules);

        $usuario = auth()->user();

        $valorActual = $this->bien->{$this->campo};

        // Si no hubo cambios reales, salir
        if ($valorActual == $this->valor) {
            $this->editando = false;
            return;
        }

        // Si tiene rol autorizado, guardar directamente
        if ($usuario->hasRole('Administrador') || $usuario->hasRole('Rector')) {
            $this->bien->{$this->campo} = $this->valor;

            // Lógica especial para estado_id
            if ($this->campo === 'estado_id') {
                $estado = Estado::find($this->valor);
                if ($estado) {
                    $nombreEstado = strtolower($estado->nombre);

                    if ($nombreEstado === 'malo') {
                        $this->bien->mantenimiento_id = Mantenimiento::whereRaw('LOWER(nombre) = ?', ['dado de baja'])->value('id');
                        $this->bien->almacenamiento_id = Almacenamiento::whereRaw('LOWER(nombre) = ?', ['almacenado'])->value('id');
                    } elseif ($nombreEstado === 'regular') {
                        $this->bien->mantenimiento_id = Mantenimiento::whereRaw('LOWER(nombre) = ?', ['en mora'])->value('id');
                        $this->bien->almacenamiento_id = Almacenamiento::whereRaw('LOWER(nombre) = ?', ['en uso'])->value('id');
                    } elseif (in_array($nombreEstado, ['bueno', 'nuevo'])) {
                        $this->bien->mantenimiento_id = Mantenimiento::whereRaw('LOWER(nombre) = ?', ['al día'])->value('id');
                        $this->bien->almacenamiento_id = Almacenamiento::whereRaw('LOWER(nombre) = ?', ['en uso'])->value('id');
                    }
                }
            }

            $this->bien->save();
            $this->valor = $this->bien->{$this->campo};
            $this->editando = false;
            $this->dispatch('bienActualizado');
            session()->flash('message', 'Campo actualizado correctamente.');
            return;
        }

        // Usuario sin permiso → guardar solicitud pendiente

        // Verificar si el usuario pertenece a la dependencia del bien
        if (!$usuario->dependencias->pluck('id')->contains($this->bien->dependencia_id)) {
            return redirect()->route('inventario.bienes.index');
        }

        // Verificar si ya existe una modificación pendiente para este campo
        $yaExiste = HistorialModificacionBien::where('bien_id', $this->bien->id)
            ->where('campo', $this->campo)
            ->where('estado', 'pendiente')
            ->exists();

        if ($yaExiste) {
            session()->flash('warning', 'Ya hay una modificación pendiente para este campo.');
            $this->editando = false;
            return;
        }

        // Crear la modificación pendiente UNA sola vez
        $modificacionPendiente = HistorialModificacionBien::create([
            'bien_id' => $this->bien->id,
            'tipo_objeto' => 'bien',
            'campo' => $this->campo,
            'valor_anterior' => $valorActual,
            'valor_nuevo' => $this->valor,
            'dependencia_id' => $this->bien->dependencia_id,
            'estado' => 'pendiente',
        ]);

        // Enviar notificación a administradores y rector
        $usuariosDestino = User::whereHas('role', function ($query) {
            $query->whereIn('nombre', ['Administrador', 'Rector']);
        })->get();

        Notification::send($usuariosDestino, new NotificacionHmb($modificacionPendiente));

        $this->editando = false;
        session()->flash('info', 'El cambio fue enviado para aprobación.');
    }

    public function campoTieneModificacionPendiente()
    {
        $user = auth()->user();

        $query = HistorialModificacionBien::where('bien_id', $this->bienId)
            ->where('campo', $this->campo)
            ->where('estado', 'pendiente');

        // Verifica si el usuario tiene el rol adecuado
        if (!in_array($user->role->nombre ?? '', ['Administrador', 'Rector'])) {
            $dependenciaIds = $user->dependencias->pluck('id');
            if ($dependenciaIds->isEmpty()) return null;

            $query->whereIn('dependencia_id', $dependenciaIds);
        }

        return $query->first();
    }

    public function render()
    {
        return view('inventario::livewire.bienes.editar-campo-bien');
    }
}

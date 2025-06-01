<?php

namespace Modules\Inventario\Livewire\Bienes;

use Livewire\Component;
use Modules\Users\Models\User;
use Modules\Inventario\Entities\{
    Bien,
    Estado,
    Almacenamiento,
    Mantenimiento,
    Dependencia,
    Ubicacion,
    Categoria
};

class EditarCampoBien extends Component
{
    public Bien $bien;
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
                ->mapWithKeys(fn ($c) => [$c->id => $c->nombre])
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
        // Si el campo es select o number, puedes aplicar otras validaciones
        $rules = match ($this->tipo) {
            'number' => ['valor' => 'nullable|numeric'],
            'date' => ['valor' => 'nullable|date'],
            'select' => ['valor' => 'nullable|exists:' . $this->inferirTabla() . ',id'],
            default => ['valor' => 'nullable|string|max:255'],
        };

        $this->validate($rules);

        $this->bien->{$this->campo} = $this->valor;

        // Si el campo editado es estado_id
        if ($this->campo === 'estado_id') {
            $estado = Estado::find($this->valor);
            if ($estado) {
                $nombreEstado = strtolower($estado->nombre);

                if ($nombreEstado === 'malo') {
                    // Mantenimiento: Dado de Baja
                    $mantenimiento = Mantenimiento::whereRaw('LOWER(nombre) = ?', ['dado de baja'])->first();
                    if ($mantenimiento) {
                        $this->bien->mantenimiento_id = $mantenimiento->id;
                    }
                    // Almacenamiento: almacenado
                    $almacenamiento = Almacenamiento::whereRaw('LOWER(nombre) = ?', ['almacenado'])->first();
                    if ($almacenamiento) {
                        $this->bien->almacenamiento_id = $almacenamiento->id;
                    }
                } elseif (in_array($nombreEstado, ['regular'])) {
                    // Mantenimiento: En Mora
                    $mantenimiento = Mantenimiento::whereRaw('LOWER(nombre) = ?', ['en mora'])->first();
                    if ($mantenimiento) {
                        $this->bien->mantenimiento_id = $mantenimiento->id;
                    }
                    // Almacenamiento: En uso
                    $almacenamiento = Almacenamiento::whereRaw('LOWER(nombre) = ?', ['en uso'])->first();
                    if ($almacenamiento) {
                        $this->bien->almacenamiento_id = $almacenamiento->id;
                    }
                } elseif (in_array($nombreEstado, ['bueno', 'nuevo'])) {
                    // Mantenimiento: Al Día
                    $mantenimiento = Mantenimiento::whereRaw('LOWER(nombre) = ?', ['al día'])->first();
                    if ($mantenimiento) {
                        $this->bien->mantenimiento_id = $mantenimiento->id;
                    }
                    // Almacenamiento: En uso
                    $almacenamiento = Almacenamiento::whereRaw('LOWER(nombre) = ?', ['en uso'])->first();
                    if ($almacenamiento) {
                        $this->bien->almacenamiento_id = $almacenamiento->id;
                    }
                }
            }
        }

        $this->bien->save();                                                    // Guarda en BD        
        $this->valor = $this->bien->{$this->campo};                             // 3. Actualiza el valor mostrado con el dato real             
        $this->editando = false;                                                // 4. Sale del modo edición
        $this->dispatch('bienActualizado', $this->bien->id);            // 5. Notifica al componente padre u otros listeners
        session()->flash('message', 'Campo actualizado correctamente.');        // 6. Muestra mensaje flash (si aplica)
    }    

    public function render()
    {
        return view('inventario::livewire.bienes.editar-campo-bien');
    }
}

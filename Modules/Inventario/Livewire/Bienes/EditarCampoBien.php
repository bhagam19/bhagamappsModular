<?php

namespace Modules\Inventario\Livewire\Bienes;

use Livewire\Component;
use Modules\Inventario\Entities\Bien;

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
            'categoria_id' => \Modules\Inventario\Entities\Categoria::orderBy('nombre')
                ->get()
                ->mapWithKeys(fn ($c) => [$c->id => $c->nombre])
                ->toArray(),
            'estado_id' => \Modules\Inventario\Entities\Estado::pluck('nombre', 'id')->toArray(),
            'ubicacion_id' => \Modules\Inventario\Entities\Ubicacion::pluck('nombre', 'id')->toArray(),
            'dependencia_id' => \Modules\Inventario\Entities\Dependencia::pluck('nombre', 'id')->toArray(),
            'almacenamiento_id' => \Modules\Inventario\Entities\Almacenamiento::pluck('nombre', 'id')->toArray(),
            'mantenimiento_id' => \Modules\Inventario\Entities\Mantenimiento::pluck('nombre', 'id')->toArray(),
            'usuario_id' => \Modules\Users\Models\User::orderBy('nombres')
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
        $this->bien->save();

        $this->editando = false;
        session()->flash('message', 'Campo actualizado correctamente.');
    }

    

    public function render()
    {
        return view('inventario::livewire.bienes.editar-campo-bien');
    }
}

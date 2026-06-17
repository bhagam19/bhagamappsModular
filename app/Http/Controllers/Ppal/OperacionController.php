<?php

namespace App\Http\Controllers\Ppal;

use App\Http\Controllers\Controller;
use App\Models\Actividad;
use App\Models\Meta;
use App\Models\Tarea;
use Illuminate\Http\Request;
use Modules\Inventario\Entities\Dependencia;
use Modules\User\Entities\Role;
use Modules\User\Entities\User;

class OperacionController extends Controller
{
    private const ESTADOS = ['Pendiente', 'En Proceso', 'Completada', 'Suspendida', 'Cancelada'];

    public function index()
    {
        $metas = Meta::with([
            'objetivo.proceso.gestion',
            'componente',
            'actividades.tareas',
        ])->where('activo', true)->orderBy('codigo')->get();

        $usuarios     = User::orderBy('apellidos')->get(['id', 'nombres', 'apellidos']);
        $roles        = Role::orderBy('nombre')->get(['id', 'nombre']);
        $dependencias = Dependencia::orderBy('nombre')->get(['id', 'nombre']);
        $estados      = self::ESTADOS;

        return view('operacion.index', compact('metas', 'usuarios', 'roles', 'dependencias', 'estados'));
    }

    public function storeActividad(Request $request)
    {
        $validated = $request->validate([
            'meta_id'       => 'required|exists:metas,id',
            'componente_id' => 'required|exists:componentes,id',
            'codigo'        => 'required|string|max:20|unique:actividades,codigo',
            'nombre'        => 'required|string|max:250',
            'descripcion'   => 'nullable|string',
            'estado'        => 'required|in:' . implode(',', self::ESTADOS),
            'avance_manual' => 'required|integer|min:0|max:100',
            'fecha_inicio'  => 'nullable|date',
            'fecha_fin'     => 'nullable|date|after_or_equal:fecha_inicio',
        ]);

        $validated['avance_calculado'] = 0;

        Actividad::create($validated);

        return redirect()->back()->with('success', 'Actividad creada correctamente.');
    }

    public function updateActividad(Request $request, Actividad $actividad)
    {
        $validated = $request->validate([
            'nombre'        => 'required|string|max:250',
            'descripcion'   => 'nullable|string',
            'estado'        => 'required|in:' . implode(',', self::ESTADOS),
            'avance_manual' => 'required|integer|min:0|max:100',
            'fecha_inicio'  => 'nullable|date',
            'fecha_fin'     => 'nullable|date|after_or_equal:fecha_inicio',
        ]);

        $validated['avance_calculado'] = $actividad->calcularAvance();

        $actividad->update($validated);

        return redirect()->back()->with('success', 'Actividad actualizada correctamente.');
    }

    public function storeTarea(Request $request, Actividad $actividad)
    {
        $validated = $request->validate([
            'codigo'           => 'required|string|max:25|unique:tareas,codigo',
            'nombre'           => 'required|string|max:250',
            'descripcion'      => 'nullable|string',
            'responsable_tipo' => 'nullable|in:usuario,rol,dependencia',
            'responsable_id'   => 'nullable|integer|min:1',
            'estado'           => 'required|in:' . implode(',', self::ESTADOS),
            'avance'           => 'required|integer|min:0|max:100',
            'fecha_inicio'     => 'nullable|date',
            'fecha_fin'        => 'nullable|date|after_or_equal:fecha_inicio',
        ]);

        $validated['actividad_id'] = $actividad->id;

        Tarea::create($validated);

        $actividad->update(['avance_calculado' => $actividad->calcularAvance()]);

        return redirect()->back()->with('success', 'Tarea creada correctamente.');
    }

    public function updateTarea(Request $request, Tarea $tarea)
    {
        $validated = $request->validate([
            'nombre'           => 'required|string|max:250',
            'descripcion'      => 'nullable|string',
            'responsable_tipo' => 'nullable|in:usuario,rol,dependencia',
            'responsable_id'   => 'nullable|integer|min:1',
            'estado'           => 'required|in:' . implode(',', self::ESTADOS),
            'avance'           => 'required|integer|min:0|max:100',
            'fecha_inicio'     => 'nullable|date',
            'fecha_fin'        => 'nullable|date|after_or_equal:fecha_inicio',
        ]);

        $tarea->update($validated);

        $tarea->actividad->update(['avance_calculado' => $tarea->actividad->calcularAvance()]);

        return redirect()->back()->with('success', 'Tarea actualizada correctamente.');
    }
}

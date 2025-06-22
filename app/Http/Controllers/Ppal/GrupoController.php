<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Grupo;

class GrupoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $grupos = Grupo::all();    
        return view('admin.grupos.index', compact('grupos'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.grupos.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Validar los datos
        $request->validate([
            'nombre' => 'required|array',  // Asegúrate de que sea un arreglo
            'nombre.*' => 'required|string|max:255', // Validar cada nombre individualmente
        ]);

        // Guardar los grupos
        foreach ($request->nombre as $nombre) {
            Grupo::create([
                'nombre' => $nombre,
            ]);
        }

        // Redirigir al índice con mensaje de éxito
        return redirect()->route('admin.grupos.index')->with('success', 'Grupo(s) creados con éxito');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Grupo $grupo)
    {
        $grupo->delete();
        return redirect()->route('admin.grupos.index')->with('info', 'Grupo eliminado con éxito.');
    }
}

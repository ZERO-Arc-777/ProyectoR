<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MateriaA;
use Illuminate\Http\Request;

class MateriaController
{
    public function index(Request $request)
    {
        $buscar = $request->input('buscar');
        
        $materias = MateriaA::when($buscar, function($query, $buscar) {
            return $query->where('nombre', 'LIKE', "%{$buscar}%");
        })->orderBy('semestre', 'asc')->paginate(10);

        return view('admin.materias.index', compact('materias', 'buscar'));
    }

    public function create()
    {
        return view('admin.materias.creadorM');
    }

    public function store(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:255',
            'semestre' => 'required|integer|min:1|max:6',
        ]);

        dd([
        'nombre' => strtoupper($request->nombre),
        'semestre' => $request->semestre,
        'estado' => 'ACTIVA'
        ]);

        MateriaA::create([
            'nombre' => strtoupper($request->nombre),
            'semestre' => $request->semestre,
            'estado' => 'ACTIVA'
        ]);

        return redirect()->route('admin.materias')->with('success', 'Materia registrada correctamente.');
    }

    public function edit($id_materia)
    {
        $materia = MateriaA::findOrFail($id_materia);
        return view('admin.materias.editorM', compact('materia'));
    }

    public function update(Request $request, $id_materia)
    {
        $request->validate([
            'nombre' => 'required|string|max:255',
            'semestre' => 'required|integer|min:1|max:6',
            'estado' => 'required|in:ACTIVA,INACTIVA'
        ]);

        $materia = MateriaA::findOrFail($id_materia);
        
        $materia->update([
            'nombre' => strtoupper($request->nombre),
            'semestre' => $request->semestre,
            'estado' => $request->estado
        ]);

        return redirect()->route('admin.materias')->with('success', 'Materia actualizada correctamente.');
    }

    public function actualizarPeriodo(Request $request)
    {
        $request->validate([
            'periodo_actual' => 'required|string|max:255'
        ]);

        \App\Models\Configuracion::updateOrCreate(
            ['clave' => 'periodo_actual'],
            ['valor' => $request->periodo_actual]
        );

        return back()->with('success', '¡Periodo escolar actualizado correctamente!');
    }
}

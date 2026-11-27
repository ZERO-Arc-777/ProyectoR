<?php

namespace App\Http\Controllers\Admin;

use App\Models\Materia;
use Illuminate\Http\Request;

class MateriaController
{
    public function index(Request $request)
    {
        $datos =$request->validate([
            'buscar' => 'nullable|string|max:100',
            'estado' => 'nullable|string|in:ACTIVA,INACTIVA',
        ]);

        $buscar = trim($datos['buscar'] ?? '');
        $estado =$datos['estado'] ?? null;

        $materias = Materia::query()
            ->when($buscar !== '', function ($query) use ($buscar) {
                $query->where('nombre', 'LIKE', "\%{$buscar}%");
            })
            ->when($estado !== null, function ($query) use ($estado) {
                $query->where('estado',$estado);
            })
            ->orderBy('semestre', 'asc')
            ->paginate(10)
            ->withQueryString();

        return view(
            'admin.materias.index',
            compact('materias', 'buscar', 'estado')
        );
    }

    public function create()
    {
        return view('admin.materias.creadorM');
    }

    public function store(Request $request)
    {
        $datos =$request->validate([
            'nombre'   => 'required|string|max:100',
            'semestre' => 'required|integer|min:1|max:6',
        ]);

        Materia::create([
            'nombre'   => strtoupper(trim($datos['nombre'])),
            'semestre' => $datos['semestre'],
            'estado'   => 'ACTIVA',
        ]);

        return redirect()
            ->route('admin.materias')
            ->with('success', 'Materia registrada correctamente.');
    }

    public function edit($id_materia)
    {
        $materia = Materia::findOrFail($id_materia);

        return view('admin.materias.editorM', compact('materia'));
    }

    public function update(Request $request,$id_materia)
    {
        $datos =$request->validate([
            'nombre'   => 'required|string|max:100',
            'semestre' => 'required|integer|min:1|max:6',
            'estado'   => 'required|in:ACTIVA,INACTIVA',
        ]);

        $materia = Materia::findOrFail($id_materia);

        $materia->update([
            'nombre'   => strtoupper(trim($datos['nombre'])),
            'semestre' => $datos['semestre'],
            'estado'   => $datos['estado'],
        ]);

        return redirect()
            ->route('admin.materias')
            ->with('success', 'Materia actualizada correctamente.');
    }
}

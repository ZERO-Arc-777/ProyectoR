<?php

namespace App\Http\Controllers\Admin;

use App\Models\Materia;
use App\Models\Configuracion;
use Illuminate\Http\Request;

class MateriaController
{
    // ==========================================
    // CONSULTAR MATERIAS
    // ==========================================

    public function index(Request $request)
    {
        $datos = $request->validate([
            'buscar' => 'nullable|string|max:100',
        ]);

        $buscar = trim($datos['buscar'] ?? '');

        // Consultar las materias registradas.
        $materias = Materia::query()
            ->when($buscar !== '', function ($query) use ($buscar) {
                $query->where('nombre', 'LIKE', "%{$buscar}%");
            })
            ->orderBy('semestre', 'asc')
            ->paginate(10)
            ->withQueryString();

        // Recuperar el periodo escolar guardado.
        $periodoActual = Configuracion::where(
            'clave',
            'periodo_actual'
        )->value('valor') ?? '';

        // Enviar la información a la vista.
        return view(
            'admin.materias.index',
            compact(
                'materias',
                'buscar',
                'periodoActual'
            )
        );
    }

    // ==========================================
    // FORMULARIO DE REGISTRO
    // ==========================================

    public function create()
    {
        return view('admin.materias.creadorM');
    }

    // ==========================================
    // REGISTRAR MATERIA
    // ==========================================

    public function store(Request $request)
    {
        $datos = $request->validate([
            'nombre' => 'required|string|max:100',
            'semestre' => 'required|integer|min:1|max:6',
        ]);

        Materia::create([
            'nombre' => strtoupper(trim($datos['nombre'])),
            'semestre' => $datos['semestre'],
            'estado' => 'ACTIVA',
        ]);

        return redirect()
            ->route('admin.materias')
            ->with(
                'success',
                'Materia registrada correctamente.'
            );
    }

    // ==========================================
    // FORMULARIO DE EDICIÓN
    // ==========================================

    public function edit($id_materia)
    {
        $materia = Materia::findOrFail($id_materia);

        return view(
            'admin.materias.editorM',
            compact('materia')
        );
    }

    // ==========================================
    // ACTUALIZAR MATERIA
    // ==========================================

    public function update(Request $request, $id_materia)
    {
        $datos = $request->validate([
            'nombre' => 'required|string|max:100',
            'semestre' => 'required|integer|min:1|max:6',
            'estado' => 'required|in:ACTIVA,INACTIVA',
        ]);

        $materia = Materia::findOrFail($id_materia);

        $materia->update([
            'nombre' => strtoupper(trim($datos['nombre'])),
            'semestre' => $datos['semestre'],
            'estado' => $datos['estado'],
        ]);

        return redirect()
            ->route('admin.materias')
            ->with(
                'success',
                'Materia actualizada correctamente.'
            );
    }

    // ==========================================
    // ACTUALIZAR PERIODO ESCOLAR
    // ==========================================

    public function actualizarPeriodo(Request $request)
    {
        $datos = $request->validate([
            'periodo_actual' => 'required|string|max:255',
        ]);

        // Registrar o actualizar el periodo escolar.
        Configuracion::updateOrCreate(
            ['clave' => 'periodo_actual'],
            ['valor' => $datos['periodo_actual']]
        );

        return back()->with(
            'success',
            '¡Periodo escolar actualizado correctamente!'
        );
    }
}

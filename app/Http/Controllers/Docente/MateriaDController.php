<?php

namespace App\Http\Controllers\Docente;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Docente;
use App\Models\Configuracion;
use App\Models\Alumno;
use App\Models\AsignacionAcademica;
use App\Models\Calificacion;

class MateriaDController
{
    public function index()
    {
        $docente = Docente::where('id_usuario', Auth::id())->firstOrFail();
        $periodoActual = Configuracion::where('clave', 'periodo_actual')->value('valor') ?? 'No definido';

        $asignaciones = AsignacionAcademica::with('materia')
            ->where('id_docente', $docente->id_docente)
            ->where('periodo', $periodoActual)
            ->get();

        return view('docente.materias', compact('asignaciones', 'periodoActual'));
    }

    public function calificar($id_asignacion)
    {
        $docente = Docente::where('id_usuario', Auth::id())->firstOrFail();
        $periodoActual = Configuracion::where('clave', 'periodo_actual')->value('valor') ?? 'No definido';

        $asignacion = AsignacionAcademica::with('materia')
            ->where('id_asignacion', $id_asignacion)
            ->where('id_docente', $docente->id_docente)
            ->where('periodo', $periodoActual)
            ->firstOrFail();

        $alumnos = Alumno::where('estado', 'ACTIVO')
            ->where('semestre', $asignacion->materia->semestre)
            ->where('grupo', $asignacion->grupo)
            ->orderBy('apellido_paterno')
            ->orderBy('apellido_materno')
            ->orderBy('nombres')
            ->get();

        $calificacionesPrevias = Calificacion::where('id_materia', $asignacion->id_materia)
            ->where('periodo', $periodoActual)
            ->pluck('calificacion', 'id_alumno');

        return view('docente.calificar', compact('asignacion', 'alumnos', 'periodoActual', 'calificacionesPrevias'));
    }

    public function guardarCalificaciones(Request $request, $id_asignacion)
    {
        $docente = Docente::where('id_usuario', Auth::id())->firstOrFail();
        $periodoActual = Configuracion::where('clave', 'periodo_actual')->value('valor') ?? 'No definido';

        $asignacion = AsignacionAcademica::with('materia')
            ->where('id_asignacion', $id_asignacion)
            ->where('id_docente', $docente->id_docente)
            ->where('periodo', $periodoActual)
            ->firstOrFail();

        $request->validate([
            'calificaciones'   => 'required|array',
            'calificaciones.*' => 'nullable|numeric|min:0|max:10',
        ]);

        foreach ($request->calificaciones as $id_alumno => $nota) {
            if ($nota !== null && $nota !== '') {
                Calificacion::updateOrCreate(
                    [
                        'id_alumno'  => $id_alumno,
                        'id_materia' => $asignacion->id_materia,
                        'periodo'    => $periodoActual,
                    ],
                    [
                        'calificacion' => $nota
                    ]
                );
            }
        }

        return redirect()->route('docente.materias')->with('success', 'Calificaciones registradas correctamente.');
    }
}

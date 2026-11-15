<?php

namespace App\Http\Controllers\Docente;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Models\Docente;
use App\Models\Configuracion;
use App\Models\AsignacionAcademica;
use App\Models\Alumno;

class GrupoController
{
    public function index()
    {
        $docente = Docente::where('id_usuario', Auth::id())->firstOrFail();
        $periodoActual = Configuracion::where('clave', 'periodo_actual')->value('valor') ?? 'No definido';
        
        $asignaciones = AsignacionAcademica::with('materia')
            ->where('id_docente', $docente->id_docente)
            ->where('periodo', $periodoActual)
            ->get();

        return view('docente.grupos.index', compact('asignaciones', 'periodoActual'));
    }

    public function verAlumnos($id_asignacion)
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

        return view('docente.grupos.listas', compact('asignacion', 'alumnos'));
    }
}

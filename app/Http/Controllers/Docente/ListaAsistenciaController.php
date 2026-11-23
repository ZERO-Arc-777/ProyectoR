<?php

namespace App\Http\Controllers\Docente;

use App\Models\Alumno;
use App\Models\AsignacionAcademica;
use App\Models\Docente;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class ListaAsistenciaController
{
    public function descargar(int $asignacion)
    {
        // Identificar al docente autenticado.
        $docente = Docente::where('id_usuario', Auth::id())
            ->firstOrFail();

        // Buscar únicamente asignaciones pertenecientes a este docente.
        $grupo = AsignacionAcademica::with('materia')
            ->where('id_asignacion', $asignacion)
            ->where('id_docente', $docente->id_docente)
            ->firstOrFail();

        // Obtener los alumnos activos del semestre y grupo asignados.
        $alumnos = Alumno::where('estado', 'ACTIVO')
            ->where('semestre', $grupo->materia->semestre)
            ->where('grupo', $grupo->grupo)
            ->orderBy('apellido_paterno')
            ->orderBy('apellido_materno')
            ->orderBy('nombres')
            ->get();

        // Construir nombre completo del docente.
        $nombreDocente = trim(implode(' ', array_filter([
            $docente->nombres,
            $docente->apellido_paterno,
            $docente->apellido_materno,
        ])));

        $semestre = $grupo->materia?->semestre ?? '-';
        $gradoGrupo = $semestre . '° Grupo "' . $grupo->grupo . '"';

        $pdf = Pdf::loadView('docente.asistencias', [
            'asignacion'    => $grupo,
            'alumnos'       => $alumnos,
            'nombreDocente' => $nombreDocente,
            'gradoGrupo'    => $gradoGrupo,
        ])->setPaper('letter', 'portrait');

        $nombreArchivo = 'Lista_Asistencia_'
            . $semestre . $grupo->grupo . '_'
            . Str::slug($grupo->materia?->nombre ?? 'materia', '_')
            . '.pdf';

        return $pdf->download($nombreArchivo);
    }
}
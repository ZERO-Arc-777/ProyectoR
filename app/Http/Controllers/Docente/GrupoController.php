<?php

namespace App\Http\Controllers\Docente;

use App\Models\Alumno;
use App\Models\AsignacionAcademica;
use App\Models\Calificacion;
use App\Models\Configuracion;
use App\Models\Docente;
use App\Models\HistorialGrupo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class GrupoController
{
    public function index(Request $request)
    {
        $docente = Docente::where('id_usuario', Auth::id())->firstOrFail();
        $periodoActual = Configuracion::where('clave', 'periodo_actual')->value('valor') ?? 'No definido';
        $periodoEstado = $this->obtenerEstadoPeriodo($periodoActual);

        $periodosDisponibles = $this->obtenerPeriodosDocente($docente->id_docente, $periodoActual);
        $periodoSolicitado = trim((string) $request->query('periodo', $periodoActual));

        $periodoSeleccionado = in_array($periodoSolicitado, $periodosDisponibles, true)
            ? $periodoSolicitado
            : $periodoActual;

        $esPeriodoActual = ($periodoSeleccionado === $periodoActual);

        $asignaciones = AsignacionAcademica::with('materia')
            ->where('id_docente', $docente->id_docente)
            ->where('periodo', $periodoSeleccionado)
            ->get();

        return view('docente.grupos.index', compact(
            'asignaciones',
            'periodoActual',
            'periodoEstado',
            'periodosDisponibles',
            'periodoSeleccionado',
            'esPeriodoActual'
        ));
    }

    public function verAlumnos($id_asignacion)
    {
        $docente = Docente::where('id_usuario', Auth::id())->firstOrFail();
        $periodoActual = Configuracion::where('clave', 'periodo_actual')->value('valor') ?? 'No definido';
        $periodoEstado = $this->obtenerEstadoPeriodo($periodoActual);

        $asignacion = AsignacionAcademica::with('materia')
            ->where('id_asignacion', $id_asignacion)
            ->where('id_docente', $docente->id_docente)
            ->firstOrFail();

        $esPeriodoActual = ($asignacion->periodo === $periodoActual);
        $esSoloLectura = (!$esPeriodoActual || $periodoEstado !== 'ABIERTO');

        if (!$esSoloLectura) {
            $alumnos = Alumno::where('estado', 'ACTIVO')
                ->where('semestre', $asignacion->materia->semestre)
                ->where('grupo', $asignacion->grupo)
                ->orderBy('apellido_paterno')
                ->orderBy('apellido_materno')
                ->orderBy('nombres')
                ->get();
        } else {
            $idsHistorial = HistorialGrupo::where('periodo', $asignacion->periodo)
                ->where('semestre', $asignacion->materia->semestre)
                ->where('grupo', $asignacion->grupo)
                ->pluck('id_alumno');

            $idsCalificados = Calificacion::where('id_asignacion', $asignacion->id_asignacion)
                ->pluck('id_alumno');

            $idsAlumnos = $idsHistorial->merge($idsCalificados)->unique()->values();

            $alumnos = Alumno::whereIn('id_alumno', $idsAlumnos)
                ->orderBy('apellido_paterno')
                ->orderBy('apellido_materno')
                ->orderBy('nombres')
                ->get();
        }

        return view('docente.grupos.listas', compact('asignacion', 'alumnos', 'esSoloLectura', 'esPeriodoActual'));
    }

    private function obtenerPeriodosDocente(int $idDocente, string $periodoActual): array
    {
        $historicos = AsignacionAcademica::where('id_docente', $idDocente)
            ->select('periodo', DB::raw('MAX(id_asignacion) as orden'))
            ->groupBy('periodo')
            ->orderByDesc('orden')
            ->pluck('periodo')
            ->all();

        $lista = [];
        if ($periodoActual !== '' && $periodoActual !== 'No definido') {
            $lista[] = $periodoActual;
        }

        foreach ($historicos as $p) {
            if ($p !== '' && !in_array($p, $lista, true)) {
                $lista[] = $p;
            }
        }

        return $lista;
    }

    private function obtenerEstadoPeriodo(string $periodoActual): string
    {
        $estado = Configuracion::where('clave', 'periodo_estado')->value('valor');

        if ($estado === 'ABIERTO' || $estado === 'CERRADO') {
            return $estado;
        }

        return ($periodoActual !== '' && $periodoActual !== 'No definido') ? 'ABIERTO' : 'CERRADO';
    }
}

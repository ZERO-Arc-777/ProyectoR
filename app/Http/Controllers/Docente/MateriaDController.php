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

class MateriaDController
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
        $esEditable = $esPeriodoActual && $periodoEstado === 'ABIERTO';

        $asignaciones = AsignacionAcademica::with('materia')
            ->where('id_docente', $docente->id_docente)
            ->where('periodo', $periodoSeleccionado)
            ->get();

        return view('docente.materias', compact(
            'asignaciones',
            'periodoActual',
            'periodoEstado',
            'periodosDisponibles',
            'periodoSeleccionado',
            'esPeriodoActual',
            'esEditable'
        ));
    }

    public function calificar($id_asignacion)
    {
        $docente = Docente::where('id_usuario', Auth::id())->firstOrFail();
        $periodoActual = Configuracion::where('clave', 'periodo_actual')->value('valor') ?? 'No definido';
        $periodoEstado = $this->obtenerEstadoPeriodo($periodoActual);

        // Permite abrir cualquier asignación propia (vigente o histórica).
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

            $calificacionesPrevias = Calificacion::where('id_materia', $asignacion->id_materia)
                ->where('periodo', $periodoActual)
                ->whereIn('id_alumno', $alumnos->pluck('id_alumno'))
                ->get()
                ->keyBy('id_alumno');
        } else {
            // Reconstrucción fiel del ciclo histórico o cerrado.
            $idsHistorial = HistorialGrupo::where('periodo', $asignacion->periodo)
                ->where('semestre', $asignacion->materia->semestre)
                ->where('grupo', $asignacion->grupo)
                ->pluck('id_alumno');

            $calificacionesPrevias = Calificacion::where(function ($q) use ($asignacion, $idsHistorial) {
                $q->where('id_asignacion', $asignacion->id_asignacion);

                if ($idsHistorial->isNotEmpty()) {
                    $q->orWhere(function ($legacy) use ($asignacion, $idsHistorial) {
                        $legacy->whereNull('id_asignacion')
                            ->where('id_materia', $asignacion->id_materia)
                            ->where('periodo', $asignacion->periodo)
                            ->whereIn('id_alumno', $idsHistorial);
                    });
                }
            })
                ->get()
                ->keyBy('id_alumno');

            $idsAlumnos = $idsHistorial
                ->merge($calificacionesPrevias->keys())
                ->unique()
                ->values();

            $alumnos = Alumno::whereIn('id_alumno', $idsAlumnos)
                ->orderBy('apellido_paterno')
                ->orderBy('apellido_materno')
                ->orderBy('nombres')
                ->get();
        }

        return view(
            'docente.calificar',
            compact(
                'asignacion',
                'alumnos',
                'periodoActual',
                'periodoEstado',
                'esPeriodoActual',
                'esSoloLectura',
                'calificacionesPrevias'
            )
        );
    }

    public function guardarCalificaciones(Request $request, $id_asignacion)
    {
        $docente = Docente::where('id_usuario', Auth::id())->firstOrFail();
        $periodoActual = Configuracion::where('clave', 'periodo_actual')->value('valor') ?? 'No definido';
        $periodoEstado = $this->obtenerEstadoPeriodo($periodoActual);

        if ($periodoEstado !== 'ABIERTO') {
            return redirect()
                ->route('docente.materias.calificar', $id_asignacion)
                ->withErrors(['periodo' => 'El periodo escolar está cerrado. No es posible modificar calificaciones.']);
        }

        $asignacion = AsignacionAcademica::with('materia')
            ->where('id_asignacion', $id_asignacion)
            ->where('id_docente', $docente->id_docente)
            ->where('periodo', $periodoActual)
            ->firstOrFail();

        $request->validate([
            'calificaciones'              => ['nullable', 'array'],
            'calificaciones.*.calif_1'    => ['nullable', 'integer', 'min:5', 'max:10'],
            'calificaciones.*.calif_2'    => ['nullable', 'integer', 'min:5', 'max:10'],
            'calificaciones.*.calif_3'    => ['nullable', 'integer', 'min:5', 'max:10'],
            'calificaciones.*.eval_final' => ['nullable', 'integer', 'min:5', 'max:10'],
        ], [
            'calificaciones.*.*.integer' => 'Todas las calificaciones deben ser números enteros sin decimales.',
            'calificaciones.*.*.min'     => 'La calificación mínima permitida es 5.',
            'calificaciones.*.*.max'     => 'La calificación máxima permitida es 10.',
        ]);

        $alumnos = Alumno::where('estado', 'ACTIVO')
            ->where('semestre', $asignacion->materia->semestre)
            ->where('grupo', $asignacion->grupo)
            ->get();

        $previas = Calificacion::where('id_materia', $asignacion->id_materia)
            ->where('periodo', $periodoActual)
            ->whereIn('id_alumno', $alumnos->pluck('id_alumno'))
            ->get()
            ->keyBy('id_alumno');

        $datosEnviados = $request->input('calificaciones', []);

        $normalizarEntero = static function ($valor): ?int {
            if ($valor === null || $valor === '') {
                return null;
            }

            return (int) $valor;
        };

        $nuevosEnEvalFinal = 0;
        $revertidosDeEvalFinal = 0;

        DB::transaction(function () use (
            $alumnos,
            $previas,
            $datosEnviados,
            $normalizarEntero,
            $asignacion,
            $periodoActual,
            &$nuevosEnEvalFinal,
            &$revertidosDeEvalFinal
        ) {
            foreach ($alumnos as $alumno) {
                $notas = $datosEnviados[$alumno->id_alumno] ?? [];
                $registroPrevio = $previas->get($alumno->id_alumno);

                $requeriaAntes = $registroPrevio !== null && Calificacion::requiereEvaluacionFinal(
                    $registroPrevio->calif_1 !== null ? (int) $registroPrevio->calif_1 : null,
                    $registroPrevio->calif_2 !== null ? (int) $registroPrevio->calif_2 : null,
                    $registroPrevio->calif_3 !== null ? (int) $registroPrevio->calif_3 : null
                );

                $calif1    = $normalizarEntero($notas['calif_1'] ?? null);
                $calif2    = $normalizarEntero($notas['calif_2'] ?? null);
                $calif3    = $normalizarEntero($notas['calif_3'] ?? null);
                $evalFinal = $normalizarEntero($notas['eval_final'] ?? null);

                $requiereAhora = Calificacion::requiereEvaluacionFinal($calif1, $calif2, $calif3);

                if (!$requiereAhora) {
                    if ($requeriaAntes) {
                        $revertidosDeEvalFinal++;
                    }
                    $evalFinal = null;
                } elseif (!$requeriaAntes) {
                    $nuevosEnEvalFinal++;
                    $evalFinal = null;
                }

                $hayDatos = $calif1 !== null || $calif2 !== null || $calif3 !== null || $evalFinal !== null;

                if ($hayDatos) {
                    $promedioFinal = Calificacion::calcularPromedioFinal($calif1, $calif2, $calif3, $evalFinal);

                    Calificacion::updateOrCreate(
                        [
                            'id_alumno'  => $alumno->id_alumno,
                            'id_materia' => $asignacion->id_materia,
                            'periodo'    => $periodoActual,
                        ],
                        [
                            'id_asignacion' => $asignacion->id_asignacion,
                            'calif_1'       => $calif1,
                            'calif_2'       => $calif2,
                            'calif_3'       => $calif3,
                            'eval_final'    => $evalFinal,
                            'calificacion'  => $promedioFinal,
                        ]
                    );
                } elseif ($registroPrevio !== null) {
                    $registroPrevio->update([
                        'id_asignacion' => $asignacion->id_asignacion,
                        'calif_1'       => null,
                        'calif_2'       => null,
                        'calif_3'       => null,
                        'eval_final'    => null,
                        'calificacion'  => null,
                    ]);
                }
            }
        });

        $mensaje = 'Calificaciones registradas correctamente.';
        if ($nuevosEnEvalFinal > 0) {
            $mensaje .= " Se habilitó Evaluación Final para {$nuevosEnEvalFinal} alumno(s).";
        }
        if ($revertidosDeEvalFinal > 0) {
            $mensaje .= " Se revirtió Evaluación Final para {$revertidosDeEvalFinal} alumno(s).";
        }

        return redirect()
            ->route('docente.materias.calificar', $asignacion->id_asignacion)
            ->with('success', $mensaje);
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

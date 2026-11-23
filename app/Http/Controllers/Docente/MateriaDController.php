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

        // Se envía el registro completo indexado por id_alumno para leer los 3 parciales, eval. final y promedio.
        $calificacionesPrevias = Calificacion::where('id_materia', $asignacion->id_materia)
            ->where('periodo', $periodoActual)
            ->whereIn('id_alumno', $alumnos->pluck('id_alumno'))
            ->get()
            ->keyBy('id_alumno');

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
            'calificaciones'              => ['nullable', 'array'],
            'calificaciones.*.calif_1'    => ['nullable', 'numeric', 'min:0', 'max:10'],
            'calificaciones.*.calif_2'    => ['nullable', 'numeric', 'min:0', 'max:10'],
            'calificaciones.*.calif_3'    => ['nullable', 'numeric', 'min:0', 'max:10'],
            'calificaciones.*.eval_final' => ['nullable', 'numeric', 'min:0', 'max:10'],
        ], [
            'calificaciones.*.*.numeric' => 'Todas las calificaciones deben ser valores numéricos.',
            'calificaciones.*.*.min'     => 'La calificación mínima permitida es 0.',
            'calificaciones.*.*.max'     => 'La calificación máxima permitida es 10.',
        ]);

        // Solo procesa alumnos activos que realmente pertenecen al semestre y grupo de esta asignación.
        $alumnos = Alumno::where('estado', 'ACTIVO')
            ->where('semestre', $asignacion->materia->semestre)
            ->where('grupo', $asignacion->grupo)
            ->get();

        $datosEnviados = $request->input('calificaciones', []);

        $normalizar = function ($valor): ?float {
            if ($valor === null || $valor === '') {
                return null;
            }

            return round((float) $valor, 2);
        };

        foreach ($alumnos as $alumno) {
            $notas = $datosEnviados[$alumno->id_alumno] ?? [];

            $calif1    = $normalizar($notas['calif_1'] ?? null);
            $calif2    = $normalizar($notas['calif_2'] ?? null);
            $calif3    = $normalizar($notas['calif_3'] ?? null);
            $evalFinal = $normalizar($notas['eval_final'] ?? null);

            $capturadas = array_values(array_filter(
                [$calif1, $calif2, $calif3, $evalFinal],
                fn ($nota) => $nota !== null
            ));

            $cantidad = count($capturadas);

            if ($cantidad > 0) {
                $promedio = round(array_sum($capturadas) / $cantidad, 2);

                Calificacion::updateOrCreate(
                    [
                        'id_alumno'  => $alumno->id_alumno,
                        'id_materia' => $asignacion->id_materia,
                        'periodo'    => $periodoActual,
                    ],
                    [
                        'calif_1'      => $calif1,
                        'calif_2'      => $calif2,
                        'calif_3'      => $calif3,
                        'eval_final'   => $evalFinal,
                        'calificacion' => $promedio,
                    ]
                );
            } else {
                Calificacion::where('id_alumno', $alumno->id_alumno)
                    ->where('id_materia', $asignacion->id_materia)
                    ->where('periodo', $periodoActual)
                    ->update([
                        'calif_1'      => null,
                        'calif_2'      => null,
                        'calif_3'      => null,
                        'eval_final'   => null,
                        'calificacion' => null,
                    ]);
            }
        }

        return redirect()
            ->route('docente.materias.calificar', $asignacion->id_asignacion)
            ->with('success', 'Calificaciones registradas correctamente.');
    }
}

<?php

namespace App\Http\Controllers\Admin;

use App\Models\Alumno;
use App\Models\AsignacionAcademica;
use App\Models\Calificacion;
use App\Models\Configuracion;
use App\Models\HistorialGrupo;
use App\Models\Materia;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class PeriodoController
{
    private const CICLOS_PERMITIDOS = [
        'Enero - Junio',
        'Agosto - Diciembre',
    ];

    public function index()
    {
        $periodoActual = $this->obtenerPeriodoActual();
        $periodoEstado = $this->obtenerPeriodoEstado($periodoActual);

        $anioActual = (int) now()->year;
        $aniosDisponibles = range($anioActual - 2, $anioActual + 5);

        // Descomponer el periodo actual para preseleccionar en "Corregir ciclo actual"
        $cicloActualSel = 'Agosto - Diciembre';
        $anioActualSel = $anioActual;

        if (preg_match('/^(Enero - Junio|Agosto - Diciembre)\s+(\d{4})$/', $periodoActual, $m)) {
            $cicloActualSel = $m[1];
            $anioActualSel = (int) $m[2];

            if (!in_array($anioActualSel, $aniosDisponibles, true)) {
                $aniosDisponibles[] = $anioActualSel;
                sort($aniosDisponibles);
            }
        }

        // Sugerencia automática para "Abrir nuevo ciclo"
        [$cicloSugerido, $anioSugerido] = $this->sugerirSiguienteCiclo($periodoActual, $anioActual);

        // Análisis de cierre solo cuando hay ciclo abierto
        $analisis = $periodoEstado === 'ABIERTO' && $periodoActual !== ''
            ? $this->analizarCierre($periodoActual)
            : null;

        // Historial de periodos cerrados
        $periodosCerrados = HistorialGrupo::select(
            'periodo',
            DB::raw('COUNT(*) as total_alumnos'),
            DB::raw("SUM(CASE WHEN estado_final = 'PROMOVIDO' THEN 1 ELSE 0 END) as promovidos"),
            DB::raw("SUM(CASE WHEN estado_final = 'EGRESADO' THEN 1 ELSE 0 END) as egresados"),
            DB::raw("SUM(CASE WHEN estado_final = 'BAJA' THEN 1 ELSE 0 END) as bajas"),
            DB::raw('MAX(created_at) as fecha_cierre')
        )
            ->groupBy('periodo')
            ->orderByDesc('fecha_cierre')
            ->get();

        return view('admin.periodos.index', compact(
            'periodoActual',
            'periodoEstado',
            'aniosDisponibles',
            'cicloActualSel',
            'anioActualSel',
            'cicloSugerido',
            'anioSugerido',
            'analisis',
            'periodosCerrados'
        ));
    }

    // ==========================================
    // 1. ABRIR NUEVO CICLO
    // ==========================================

    public function abrirCiclo(Request $request)
    {
        $periodoActual = $this->obtenerPeriodoActual();
        $periodoEstado = $this->obtenerPeriodoEstado($periodoActual);

        if ($periodoEstado === 'ABIERTO' && $periodoActual !== '') {
            return redirect()
                ->route('admin.periodo.index')
                ->withErrors(['periodo' => 'Debes cerrar el ciclo actual antes de abrir un nuevo periodo escolar.']);
        }

        $nuevoPeriodo = $this->validarYFormatearPeriodo($request);

        if ($this->periodoYaExisteEnHistorial($nuevoPeriodo)) {
            return redirect()
                ->route('admin.periodo.index')
                ->withInput()
                ->withErrors(['periodo' => "El periodo \"{$nuevoPeriodo}\" ya fue cerrado anteriormente. Selecciona un ciclo distinto."]);
        }

        DB::transaction(function () use ($nuevoPeriodo) {
            Configuracion::updateOrCreate(
                ['clave' => 'periodo_actual'],
                ['valor' => $nuevoPeriodo]
            );

            Configuracion::updateOrCreate(
                ['clave' => 'periodo_estado'],
                ['valor' => 'ABIERTO']
            );
        });

        return redirect()
            ->route('admin.periodo.index')
            ->with('success', "Nuevo ciclo escolar abierto correctamente: {$nuevoPeriodo}. Ya puedes asignar cargas a los docentes.");
    }

    // ==========================================
    // 2. CORREGIR CICLO ACTUAL (EN CASCADA)
    // ==========================================

    public function corregirCiclo(Request $request)
    {
        $periodoActual = $this->obtenerPeriodoActual();
        $periodoEstado = $this->obtenerPeriodoEstado($periodoActual);

        if ($periodoEstado !== 'ABIERTO' || $periodoActual === '') {
            return redirect()
                ->route('admin.periodo.index')
                ->withErrors(['periodo' => 'No hay ningún ciclo abierto para corregir.']);
        }

        $nuevoPeriodo = $this->validarYFormatearPeriodo($request);

        if ($nuevoPeriodo === $periodoActual) {
            return redirect()
                ->route('admin.periodo.index')
                ->withErrors(['periodo' => 'El periodo seleccionado es idéntico al periodo actual.']);
        }

        if ($this->periodoYaExisteEnRegistros($nuevoPeriodo)) {
            return redirect()
                ->route('admin.periodo.index')
                ->withInput()
                ->withErrors(['periodo' => "Ya existen registros guardados bajo el periodo \"{$nuevoPeriodo}\". No es posible sobrescribir un periodo existente."]);
        }

        DB::transaction(function () use ($periodoActual, $nuevoPeriodo) {
            Configuracion::updateOrCreate(
                ['clave' => 'periodo_actual'],
                ['valor' => $nuevoPeriodo]
            );

            DB::table('asignaciones_academicas')
                ->where('periodo', $periodoActual)
                ->update(['periodo' => $nuevoPeriodo, 'updated_at' => now()]);

            DB::table('calificaciones')
                ->where('periodo', $periodoActual)
                ->update(['periodo' => $nuevoPeriodo, 'updated_at' => now()]);

            DB::table('documentos')
                ->where('periodo', $periodoActual)
                ->update(['periodo' => $nuevoPeriodo, 'updated_at' => now()]);
        });

        return redirect()
            ->route('admin.periodo.index')
            ->with('success', "Ciclo corregido de \"{$periodoActual}\" a \"{$nuevoPeriodo}\" en todo el sistema sin modificar semestres ni estados de alumnos.");
    }

    // ==========================================
    // 3. CIERRE DE CICLO Y PROMOCIÓN AUTOMÁTICA
    // ==========================================

    public function cerrarCiclo(Request $request)
    {
        $periodoActual = $this->obtenerPeriodoActual();
        $periodoEstado = $this->obtenerPeriodoEstado($periodoActual);

        if ($periodoEstado !== 'ABIERTO' || $periodoActual === '') {
            return redirect()
                ->route('admin.periodo.index')
                ->withErrors(['periodo' => 'No existe un ciclo escolar abierto para cerrar.']);
        }

        $analisis = $this->analizarCierre($periodoActual);

        if (count($analisis['alertasPendientes']) > 0) {
            return redirect()
                ->route('admin.periodo.index')
                ->withErrors([
                    'cierre' => 'No se puede cerrar el ciclo escolar: todavía existen '
                        . count($analisis['alertasPendientes'])
                        . ' registro(s) pendiente(s) de captura por los docentes.',
                ]);
        }

        $fechaHoy = now()->toDateString();

        DB::transaction(function () use ($analisis, $periodoActual, $fechaHoy) {
            // 1. Promover alumnos aprobados de 1° a 5° semestre
            foreach ($analisis['alumnosPromovidos'] as $alumno) {
                HistorialGrupo::updateOrCreate(
                    [
                        'id_alumno' => $alumno->id_alumno,
                        'periodo'   => $periodoActual,
                    ],
                    [
                        'semestre'     => $alumno->semestre,
                        'grupo'        => $alumno->grupo,
                        'estado_final' => 'PROMOVIDO',
                    ]
                );

                $alumno->semestre = $alumno->semestre + 1;
                $alumno->save();
            }

            // 2. Egresar alumnos aprobados de 6° semestre
            foreach ($analisis['alumnosEgresados'] as $alumno) {
                HistorialGrupo::updateOrCreate(
                    [
                        'id_alumno' => $alumno->id_alumno,
                        'periodo'   => $periodoActual,
                    ],
                    [
                        'semestre'     => $alumno->semestre,
                        'grupo'        => $alumno->grupo,
                        'estado_final' => 'EGRESADO',
                    ]
                );

                $alumno->estado = 'EGRESADO';
                $alumno->save();

                $existeEgreso = DB::table('egresados')
                    ->where('id_alumno', $alumno->id_alumno)
                    ->exists();

                if (!$existeEgreso) {
                    DB::table('egresados')->insert([
                        'id_alumno'    => $alumno->id_alumno,
                        'fecha_egreso' => $fechaHoy,
                    ]);
                }
            }

            // 3. Dar de baja a alumnos que reprobaron Evaluación Final
            foreach ($analisis['candidatosBaja'] as $item) {
                $alumno = $item['alumno'];

                HistorialGrupo::updateOrCreate(
                    [
                        'id_alumno' => $alumno->id_alumno,
                        'periodo'   => $periodoActual,
                    ],
                    [
                        'semestre'     => $alumno->semestre,
                        'grupo'        => $alumno->grupo,
                        'estado_final' => 'BAJA',
                    ]
                );

                $alumno->estado = 'BAJA';
                $alumno->save();

                $materiasTexto = collect($item['materias_reprobadas'])
                    ->map(fn ($m) => "{$m['materia']} ({$m['nota']})")
                    ->implode(', ');

                DB::table('bajas')->insert([
                    'id_alumno'  => $alumno->id_alumno,
                    'fecha_baja' => $fechaHoy,
                    'motivo'     => Str::limit(
                        "Reprobación en Evaluación Final ({$periodoActual}): {$materiasTexto}",
                        250
                    ),
                ]);
            }

            // 4. Marcar ciclo como cerrado para habilitar "Abrir nuevo ciclo"
            Configuracion::updateOrCreate(
                ['clave' => 'periodo_estado'],
                ['valor' => 'CERRADO']
            );
        });

        $totalPromovidos = count($analisis['alumnosPromovidos']);
        $totalEgresados = count($analisis['alumnosEgresados']);
        $totalBajas = count($analisis['candidatosBaja']);

        return redirect()
            ->route('admin.periodo.index')
            ->with(
                'success',
                "Ciclo \"{$periodoActual}\" cerrado correctamente. "
                . "Promovidos: {$totalPromovidos} | Egresados: {$totalEgresados} | Bajas: {$totalBajas}. "
                . 'Ahora puedes abrir el nuevo ciclo escolar.'
            );
    }

    // ==========================================
    // MÉTODOS PRIVADOS DE ANÁLISIS Y UTILIDADES
    // ==========================================

    private function analizarCierre(string $periodoActual): array
    {
        $alumnosActivos = Alumno::where('estado', 'ACTIVO')
            ->orderBy('semestre')
            ->orderBy('grupo')
            ->orderBy('apellido_paterno')
            ->orderBy('apellido_materno')
            ->orderBy('nombres')
            ->get();

        $materiasPorSemestre = Materia::where('estado', 'ACTIVA')
            ->orderBy('nombre')
            ->get()
            ->groupBy('semestre');

        $asignaciones = AsignacionAcademica::with('docente')
            ->where('periodo', $periodoActual)
            ->get()
            ->keyBy(fn ($a) => $a->id_materia . '|' . strtoupper($a->grupo));

        $calificacionesPorAlumno = Calificacion::where('periodo', $periodoActual)
            ->get()
            ->groupBy('id_alumno');

        $alumnosPromovidos = [];
        $desglosePromovidos = [];
        $alumnosEgresados = [];
        $candidatosBaja = [];
        $alertasPendientes = [];

        foreach ($alumnosActivos as $alumno) {
            $materiasSemestre = $materiasPorSemestre->get($alumno->semestre, collect());
            $nombreAlumno = trim(implode(' ', array_filter([
                $alumno->apellido_paterno,
                $alumno->apellido_materno,
                $alumno->nombres,
            ])));

            if ($materiasSemestre->isEmpty()) {
                $alertasPendientes[] = [
                    'matricula' => $alumno->matricula,
                    'alumno'    => $nombreAlumno,
                    'grupo'     => $alumno->semestre . '° ' . $alumno->grupo,
                    'materia'   => 'Sin materias activas',
                    'docente'   => 'No asignado',
                    'motivo'    => "No existen materias activas registradas para {$alumno->semestre}° semestre.",
                ];
                continue;
            }

            $califsAlumno = $calificacionesPorAlumno
                ->get($alumno->id_alumno, collect())
                ->keyBy('id_materia');

            $tienePendiente = false;
            $materiasReprobadas = [];

            foreach ($materiasSemestre as $materia) {
                $asignacion = $asignaciones->get(
                    $materia->id_materia . '|' . strtoupper($alumno->grupo)
                );

                $nombreDocente = $asignacion && $asignacion->docente
                    ? trim(implode(' ', array_filter([
                        $asignacion->docente->nombres,
                        $asignacion->docente->apellido_paterno,
                        $asignacion->docente->apellido_materno,
                    ])))
                    : 'Sin docente asignado';

                $calif = $califsAlumno->get($materia->id_materia);

                if (!$calif) {
                    $tienePendiente = true;
                    $alertasPendientes[] = [
                        'matricula' => $alumno->matricula,
                        'alumno'    => $nombreAlumno,
                        'grupo'     => $alumno->semestre . '° ' . $alumno->grupo,
                        'materia'   => $materia->nombre,
                        'docente'   => $nombreDocente,
                        'motivo'    => 'Sin calificaciones capturadas',
                    ];
                    continue;
                }

                $p1 = $calif->calif_1 !== null ? (int) $calif->calif_1 : null;
                $p2 = $calif->calif_2 !== null ? (int) $calif->calif_2 : null;
                $p3 = $calif->calif_3 !== null ? (int) $calif->calif_3 : null;
                $ef = $calif->eval_final !== null ? (int) $calif->eval_final : null;

                $capturados = count(array_filter([$p1, $p2, $p3], static fn ($v) => $v !== null));
                $requiereEF = Calificacion::requiereEvaluacionFinal($p1, $p2, $p3);

                if ($requiereEF) {
                    if ($ef === null) {
                        $tienePendiente = true;
                        $alertasPendientes[] = [
                            'matricula' => $alumno->matricula,
                            'alumno'    => $nombreAlumno,
                            'grupo'     => $alumno->semestre . '° ' . $alumno->grupo,
                            'materia'   => $materia->nombre,
                            'docente'   => $nombreDocente,
                            'motivo'    => 'Falta capturar Evaluación Final',
                        ];
                    } elseif ($ef < 6) {
                        $materiasReprobadas[] = [
                            'materia' => $materia->nombre,
                            'nota'    => $ef,
                        ];
                    }
                } else {
                    if ($capturados < 3) {
                        $tienePendiente = true;
                        $alertasPendientes[] = [
                            'matricula' => $alumno->matricula,
                            'alumno'    => $nombreAlumno,
                            'grupo'     => $alumno->semestre . '° ' . $alumno->grupo,
                            'materia'   => $materia->nombre,
                            'docente'   => $nombreDocente,
                            'motivo'    => "Faltan parciales por capturar ({$capturados}/3)",
                        ];
                    }
                }
            }

            if ($tienePendiente) {
                continue;
            }

            if (count($materiasReprobadas) > 0) {
                $candidatosBaja[] = [
                    'alumno'              => $alumno,
                    'matricula'           => $alumno->matricula,
                    'nombre'              => $nombreAlumno,
                    'grupo'               => $alumno->semestre . '° ' . $alumno->grupo,
                    'materias_reprobadas' => $materiasReprobadas,
                ];
            } elseif ((int) $alumno->semestre >= 6) {
                $alumnosEgresados[] = $alumno;
            } else {
                $alumnosPromovidos[] = $alumno;
                $claveTransicion = $alumno->semestre . '° → ' . ($alumno->semestre + 1) . '°';
                $desglosePromovidos[$claveTransicion] = ($desglosePromovidos[$claveTransicion] ?? 0) + 1;
            }
        }

        return [
            'totalActivos'       => $alumnosActivos->count(),
            'alumnosPromovidos'  => $alumnosPromovidos,
            'desglosePromovidos' => $desglosePromovidos,
            'alumnosEgresados'   => $alumnosEgresados,
            'candidatosBaja'     => $candidatosBaja,
            'alertasPendientes'  => $alertasPendientes,
            'puedeCerrar'        => count($alertasPendientes) === 0,
        ];
    }

    private function validarYFormatearPeriodo(Request $request): string
    {
        $anioActual = (int) now()->year;
        $anioMinimo = $anioActual - 5;
        $anioMaximo = $anioActual + 10;

        $datos = $request->validate([
            'ciclo' => ['required', 'string', Rule::in(self::CICLOS_PERMITIDOS)],
            'anio'  => ['required', 'integer', "between:{$anioMinimo},{$anioMaximo}"],
        ], [
            'ciclo.required' => 'Selecciona el ciclo semestral.',
            'ciclo.in'       => 'El ciclo seleccionado debe ser Enero - Junio o Agosto - Diciembre.',
            'anio.required'  => 'Selecciona el año escolar.',
            'anio.integer'   => 'El año debe ser un número entero válido.',
        ]);

        return "{$datos['ciclo']} {$datos['anio']}";
    }

    private function obtenerPeriodoActual(): string
    {
        return trim((string) (Configuracion::where('clave', 'periodo_actual')->value('valor') ?? ''));
    }

    private function obtenerPeriodoEstado(string $periodoActual): string
    {
        $estado = Configuracion::where('clave', 'periodo_estado')->value('valor');

        if ($estado === 'ABIERTO' || $estado === 'CERRADO') {
            return $estado;
        }

        return $periodoActual !== '' ? 'ABIERTO' : 'CERRADO';
    }

    private function sugerirSiguienteCiclo(string $periodoActual, int $anioActual): array
    {
        if (preg_match('/^(Enero - Junio|Agosto - Diciembre)\s+(\d{4})$/', $periodoActual, $m)) {
            $cicloPrev = $m[1];
            $anioPrev = (int) $m[2];

            return $cicloPrev === 'Agosto - Diciembre'
                ? ['Enero - Junio', $anioPrev + 1]
                : ['Agosto - Diciembre', $anioPrev];
        }

        return now()->month <= 6
            ? ['Enero - Junio', $anioActual]
            : ['Agosto - Diciembre', $anioActual];
    }

    private function periodoYaExisteEnHistorial(string $periodo): bool
    {
        return HistorialGrupo::where('periodo', $periodo)->exists();
    }

    private function periodoYaExisteEnRegistros(string $periodo): bool
    {
        return HistorialGrupo::where('periodo', $periodo)->exists()
            || DB::table('asignaciones_academicas')->where('periodo', $periodo)->exists()
            || DB::table('calificaciones')->where('periodo', $periodo)->exists()
            || DB::table('documentos')->where('periodo', $periodo)->exists();
    }
}

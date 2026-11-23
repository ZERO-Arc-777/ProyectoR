<?php

namespace App\Http\Controllers\Alumno;

use App\Models\Alumno;
use App\Models\Calificacion;
use App\Models\Materia;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class MateriaAController
{
    public function index()
    {
        // Identificar al alumno autenticado.
        $alumno = Alumno::where('id_usuario', Auth::id())
            ->firstOrFail();

        $periodoActual = $this->obtenerPeriodoActual();

        // Consultar las materias activas del 1° al 6° semestre agrupadas por semestre.
        $materias = Materia::where('estado', 'ACTIVA')
            ->whereBetween('semestre', [1, 6])
            ->orderBy('nombre', 'asc')
            ->get()
            ->groupBy('semestre');

        // Todas las calificaciones del alumno con su materia relacionada.
        $registrosCalificaciones = Calificacion::with('materia')
            ->where('id_alumno', $alumno->id_alumno)
            ->orderBy('id_calificacion', 'asc')
            ->get();

        // Promedio final más reciente por materia para mostrar en las tarjetas del Kárdex.
        $calificaciones = [];
        foreach ($registrosCalificaciones as $registro) {
            $promedioFinal = $this->resolverPromedioFinal($registro);
            if ($promedioFinal !== null) {
                $calificaciones[$registro->id_materia] = $promedioFinal;
            }
        }

        // Tarjetas de boletas disponibles organizadas por periodo.
        $periodosDisponibles = $this->construirResumenPeriodos(
            $alumno,
            $materias,
            $registrosCalificaciones,
            $periodoActual
        );

        return view('alumno.materias.index', [
            'alumno' => $alumno,
            'materias' => $materias,
            'calificaciones' => $calificaciones,
            'periodoActual' => $periodoActual,
            'periodosDisponibles' => $periodosDisponibles,
        ]);
    }

    public function detalle(int $id_materia)
    {
        $alumno = Alumno::where('id_usuario', Auth::id())
            ->firstOrFail();

        $materia = Materia::where('id_materia', $id_materia)
            ->where('estado', 'ACTIVA')
            ->firstOrFail();

        $periodoActual = $this->obtenerPeriodoActual();

        $registros = Calificacion::where('id_alumno', $alumno->id_alumno)
            ->where('id_materia', $materia->id_materia)
            ->orderByDesc('id_calificacion')
            ->get();

        $filasDetalle = [];

        if ($registros->isNotEmpty()) {
            foreach ($registros as $registro) {
                $promedioFinal = $this->resolverPromedioFinal($registro);

                $filasDetalle[] = [
                    'periodo' => $registro->periodo ?: $periodoActual,
                    'calif_1' => $this->formatearNota($registro->calif_1),
                    'calif_2' => $this->formatearNota($registro->calif_2),
                    'calif_3' => $this->formatearNota($registro->calif_3),
                    'eval_final' => $this->formatearNota($registro->eval_final),
                    'promedio_semestral' => $this->formatearNota($promedioFinal),
                    'promedio_numerico' => $promedioFinal,
                ];
            }
        } else {
            // Si aún no tiene calificaciones capturadas, muestra la tabla vacía con "---".
            $filasDetalle[] = [
                'periodo' => $periodoActual,
                'calif_1' => '---',
                'calif_2' => '---',
                'calif_3' => '---',
                'eval_final' => '---',
                'promedio_semestral' => '---',
                'promedio_numerico' => null,
            ];
        }

        return view('alumno.materias.detalles', [
            'alumno' => $alumno,
            'materia' => $materia,
            'semestreTexto' => $this->nombreSemestre((int) $materia->semestre),
            'filasDetalle' => $filasDetalle,
        ]);
    }

    public function descargarBoleta(Request $request)
    {
        $alumno = Alumno::where('id_usuario', Auth::id())
            ->firstOrFail();

        $periodoActual = $this->obtenerPeriodoActual();
        $periodoSolicitado = trim((string) $request->query('periodo', $periodoActual));

        $materias = Materia::where('estado', 'ACTIVA')
            ->whereBetween('semestre', [1, 6])
            ->orderBy('nombre', 'asc')
            ->get()
            ->groupBy('semestre');

        $registrosCalificaciones = Calificacion::with('materia')
            ->where('id_alumno', $alumno->id_alumno)
            ->orderBy('id_calificacion', 'asc')
            ->get();

        $periodosValidos = collect(
            $this->construirResumenPeriodos($alumno, $materias, $registrosCalificaciones, $periodoActual)
        )->pluck('periodo')->all();

        if (!in_array($periodoSolicitado, $periodosValidos, true)) {
            abort(404, 'El periodo solicitado no cuenta con boleta disponible.');
        }

        $datosBoleta = $this->construirDatosBoleta(
            $alumno,
            $periodoSolicitado,
            $periodoActual,
            $materias,
            $registrosCalificaciones
        );

        $pdf = Pdf::loadView('alumno.materias.boleta', [
            'alumno' => $alumno,
            'periodo' => $periodoSolicitado,
            'esPeriodoActual' => $periodoSolicitado === $periodoActual,
            'filas' => $datosBoleta['filas'],
            'promedioGeneral' => $datosBoleta['promedio'],
            'semestreTexto' => $datosBoleta['semestre_texto'],
            'fechaEmisionTexto' => $this->fechaActualEnEspanol(),
        ])->setPaper('letter', 'portrait');

        $nombreArchivo = 'Boleta_' . $alumno->matricula . '_' . Str::slug($periodoSolicitado, '_') . '.pdf';

        if ($request->query('modo') === 'ver') {
            return $pdf->stream($nombreArchivo);
        }

        return $pdf->download($nombreArchivo);
    }

    private function obtenerPeriodoActual(): string
    {
        $periodo = DB::table('configuraciones')
            ->where('clave', 'periodo_actual')
            ->value('valor');

        return trim((string) ($periodo ?: 'Sin periodo definido'));
    }

    private function resolverPromedioFinal(?Calificacion $registro): ?float
    {
        if (!$registro) {
            return null;
        }

        if ($registro->calificacion !== null) {
            return (float) $registro->calificacion;
        }

        if ($registro->calif_1 !== null && $registro->calif_2 !== null && $registro->calif_3 !== null) {
            return round(((float) $registro->calif_1 + (float) $registro->calif_2 + (float) $registro->calif_3) / 3, 1);
        }

        return null;
    }

    private function construirResumenPeriodos(
        Alumno $alumno,
        $materiasPorSemestre,
        $registrosCalificaciones,
        string $periodoActual
    ): array {
        $agrupados = $registrosCalificaciones->groupBy('periodo');
        $listaPeriodos = [];

        // 1. Siempre colocar el periodo escolar actual primero.
        $listaPeriodos[] = $periodoActual;

        // 2. Añadir cualquier periodo histórico con calificaciones (del más reciente al más antiguo).
        $periodosHistoricos = $registrosCalificaciones
            ->sortByDesc('id_calificacion')
            ->pluck('periodo')
            ->filter(fn ($p) => trim((string) $p) !== '' && $p !== $periodoActual)
            ->unique()
            ->values();

        foreach ($periodosHistoricos as $periodoHist) {
            $listaPeriodos[] = $periodoHist;
        }

        $tarjetas = [];

        foreach ($listaPeriodos as $periodo) {
            $esActual = ($periodo === $periodoActual);
            $registrosPeriodo = $agrupados->get($periodo, collect());

            $semestreDetectado = $esActual
                ? (int) $alumno->semestre
                : (int) ($registrosPeriodo->first()?->materia?->semestre ?? $alumno->semestre);

            $totalMaterias = $esActual
                ? max(
                    $materiasPorSemestre->get($alumno->semestre, collect())->count(),
                    $registrosPeriodo->pluck('id_materia')->unique()->count()
                )
                : $registrosPeriodo->pluck('id_materia')->unique()->count();

            $tarjetas[] = [
                'periodo' => $periodo,
                'es_actual' => $esActual,
                'semestre' => $semestreDetectado,
                'semestre_etiqueta' => $semestreDetectado . '° Semestre',
                'total_materias' => $totalMaterias,
            ];
        }

        return $tarjetas;
    }

    private function construirDatosBoleta(
        Alumno $alumno,
        string $periodo,
        string $periodoActual,
        $materiasPorSemestre,
        $registrosCalificaciones
    ): array {
        $esActual = ($periodo === $periodoActual);

        $califsPeriodo = $registrosCalificaciones
            ->where('periodo', $periodo)
            ->keyBy('id_materia');

        $materiasBoleta = collect();

        if ($esActual) {
            foreach ($materiasPorSemestre->get($alumno->semestre, collect()) as $materia) {
                $materiasBoleta->put($materia->id_materia, $materia);
            }
        }

        foreach ($califsPeriodo as $idMateria => $registro) {
            if ($registro->materia && !$materiasBoleta->has($idMateria)) {
                $materiasBoleta->put($idMateria, $registro->materia);
            }
        }

        $materiasOrdenadas = $materiasBoleta->sortBy('nombre')->values();

        $filas = [];
        $promediosCalculables = [];

        foreach ($materiasOrdenadas as $materia) {
            $registro = $califsPeriodo->get($materia->id_materia);
            $promedioSemestral = $this->resolverPromedioFinal($registro);

            if ($promedioSemestral !== null) {
                $promediosCalculables[] = $promedioSemestral;
            }

            $filas[] = [
                'materia' => $materia->nombre,
                'semestre' => (int) $materia->semestre,
                'calif_1' => $this->formatearNota($registro?->calif_1),
                'calif_2' => $this->formatearNota($registro?->calif_2),
                'calif_3' => $this->formatearNota($registro?->calif_3),
                'eval_final' => $this->formatearNota($registro?->eval_final),
                'promedio_semestral' => $this->formatearNota($promedioSemestral),
                'promedio_numerico' => $promedioSemestral,
            ];
        }

        $promedio = count($promediosCalculables) > 0
            ? number_format(array_sum($promediosCalculables) / count($promediosCalculables), 1, '.', '')
            : null;

        $semestreNumero = $esActual
            ? (int) $alumno->semestre
            : (int) ($materiasOrdenadas->first()?->semestre ?? $alumno->semestre);

        return [
            'filas' => $filas,
            'promedio' => $promedio,
            'semestre_numero' => $semestreNumero,
            'semestre_texto' => $this->nombreSemestre($semestreNumero),
        ];
    }

    private function formatearNota($valor): string
    {
        if ($valor === null || $valor === '') {
            return '---';
        }

        return rtrim(rtrim(number_format((float) $valor, 2, '.', ''), '0'), '.');
    }

    private function nombreSemestre(int $semestre): string
    {
        return match ($semestre) {
            1 => 'Primer semestre',
            2 => 'Segundo semestre',
            3 => 'Tercer semestre',
            4 => 'Cuarto semestre',
            5 => 'Quinto semestre',
            6 => 'Sexto semestre',
            default => $semestre . '° semestre',
        };
    }

    private function fechaActualEnEspanol(): string
    {
        $meses = [
            1 => 'enero', 2 => 'febrero', 3 => 'marzo', 4 => 'abril',
            5 => 'mayo', 6 => 'junio', 7 => 'julio', 8 => 'agosto',
            9 => 'septiembre', 10 => 'octubre', 11 => 'noviembre', 12 => 'diciembre',
        ];

        $ahora = now();
        $mes = $meses[(int) $ahora->format('n')] ?? $ahora->format('F');

        return $ahora->format('d') . ' de ' . $mes . ' de ' . $ahora->format('Y');
    }
}

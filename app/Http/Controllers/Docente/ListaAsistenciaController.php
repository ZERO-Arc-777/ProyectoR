<?php

namespace App\Http\Controllers\Docente;

use App\Models\Docente;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ListaAsistenciaController
{
    public function descargar(Request $request, int $asignacion)
    {
        // Identificar al docente que inició sesión.
        $docente = Docente::where(
            'id_usuario',
            Auth::id()
        )->firstOrFail();

        // Identificar el origen de la asignación.
        $origen = $request->query('origen', 'anterior');

        // Permitir únicamente los orígenes conocidos.
        abort_unless(
            in_array($origen, ['anterior', 'administrador'], true),
            404
        );

        if ($origen === 'administrador') {

            // Comprobar que exista la nueva tabla.
            abort_unless(
                Schema::hasTable('asignaciones_academicas'),
                404
            );

            // Buscar únicamente una asignación del docente actual.
            $registro = DB::table('asignaciones_academicas as aa')
                ->join(
                    'materias as m',
                    'm.id_materia',
                    '=',
                    'aa.id_materia'
                )
                ->where('aa.id_asignacion', $asignacion)
                ->where('aa.id_docente', $docente->id_docente)
                ->select(
                    'aa.id_asignacion',
                    'aa.grupo',
                    'aa.periodo',
                    'm.semestre',
                    'm.nombre as nombre_materia'
                )
                ->first();

            // Impedir acceso a asignaciones inexistentes o ajenas.
            abort_unless($registro, 404);

            // Relacionar el periodo del Administrador
            // con el periodo escolar registrado.
            $periodo = DB::table('periodos_escolares')
                ->where('nombre', $registro->periodo)
                ->first(['id_periodo', 'nombre']);

            // Evitar generar listas con un periodo desconocido.
            abort_unless(
                $periodo,
                409,
                'No se puede generar la lista: el periodo de la asignación no coincide con un periodo escolar registrado.'
            );

            // Adaptar la nueva asignación al formato de la lista.
            $grupo = (object) [
                'id_asignacion' => $registro->id_asignacion,
                'semestre' => $registro->semestre,
                'grupo' => $registro->grupo,
                'id_periodo' => $periodo->id_periodo,
                'materia' => (object) [
                    'nombre' => $registro->nombre_materia,
                ],
                'periodo' => $periodo,
            ];

        } else {

            // Conservar el funcionamiento de las asignaciones anteriores.
            $grupo = $docente->asignacionesGrupos()
                ->with(['materia', 'periodo'])
                ->where('id_asignacion', $asignacion)
                ->firstOrFail();
        }

        // Obtener únicamente los alumnos del semestre,
        // grupo y periodo escolar de la asignación.
        $alumnos = DB::table('alumnos as a')
            ->where('a.semestre', $grupo->semestre)
            ->where('a.grupo', $grupo->grupo)
            ->where(function ($consulta) use ($grupo) {

                // Alumnos inscritos.
                $consulta->whereExists(
                    function ($subconsulta) use ($grupo) {
                        $subconsulta->selectRaw('1')
                            ->from('inscripciones as i')
                            ->whereColumn(
                                'i.id_alumno',
                                'a.id_alumno'
                            )
                            ->where(
                                'i.id_periodo',
                                $grupo->id_periodo
                            );
                    }
                );

                // Alumnos reinscritos.
                $consulta->orWhereExists(
                    function ($subconsulta) use ($grupo) {
                        $subconsulta->selectRaw('1')
                            ->from('reinscripciones as r')
                            ->whereColumn(
                                'r.id_alumno',
                                'a.id_alumno'
                            )
                            ->where(
                                'r.id_periodo',
                                $grupo->id_periodo
                            );
                    }
                );
            })
            ->select(
                'a.matricula',
                'a.nombres',
                'a.apellido_paterno',
                'a.apellido_materno'
            )
            ->orderBy('a.apellido_paterno')
            ->orderBy('a.apellido_materno')
            ->orderBy('a.nombres')
            ->get();

        // Construir el nombre completo del docente.
        $nombreDocente = trim(implode(' ', array_filter([
            $docente->nombres,
            $docente->apellido_paterno,
            $docente->apellido_materno
        ])));

        // Diferenciar los archivos según el origen.
        $nombreArchivo = 'lista_asistencia_'
            . $origen . '_'
            . $grupo->id_asignacion . '.csv';

        // Generar el archivo CSV compatible con Excel.
        return response()->streamDownload(
            function () use (
                $grupo,
                $alumnos,
                $nombreDocente
            ) {

                $salida = fopen('php://output', 'w');

                // Codificación UTF-8 para Excel.
                fwrite($salida, "\xEF\xBB\xBF");

                // Escribir filas de manera segura.
                $escribir = function (array $fila) use ($salida) {

                    $fila = array_map(function ($valor) {

                        $texto = preg_replace(
                            '/[\r\n\t]+/u',
                            ' ',
                            (string) ($valor ?? '')
                        );

                        // Evitar la ejecución de fórmulas en Excel.
                        if (preg_match('/^\s*[=+\-@]/u', $texto)) {
                            $texto = "'" . $texto;
                        }

                        return $texto;

                    }, $fila);

                    fputcsv($salida, $fila, ';', '"', '');
                };

                // Encabezado institucional.
                $escribir([
                    'ESCUELA PREPARATORIA NÚMERO 5 DEL ESTADO'
                ]);

                $escribir([
                    'LISTA DE ASISTENCIA'
                ]);

                $escribir([
                    'Docente',
                    $nombreDocente
                ]);

                $escribir([
                    'Materia',
                    $grupo->materia?->nombre ?? 'No registrada'
                ]);

                $escribir([
                    'Semestre',
                    $grupo->semestre
                ]);

                $escribir([
                    'Grupo',
                    $grupo->grupo
                ]);

                $escribir([
                    'Periodo escolar',
                    $grupo->periodo?->nombre ?? 'No registrado'
                ]);

                $escribir([
                    'Mes',
                    '________________'
                ]);

                $escribir([
                    'Año',
                    '________________'
                ]);

                $escribir([]);

                // Crear columnas para los 31 días del mes.
                $dias = [];

                for ($dia = 1; $dia <= 31; $dia++) {
                    $dias[] = sprintf('D%02d', $dia);
                }

                $encabezados = array_merge(
                    [
                        'No.',
                        'Matrícula',
                        'Nombre del alumno'
                    ],
                    $dias,
                    [
                        'Faltas',
                        'Observaciones'
                    ]
                );

                $escribir($encabezados);

                // Agregar los alumnos a la lista de asistencia.
                foreach ($alumnos as $indice => $alumno) {

                    $nombre = trim(implode(' ', array_filter([
                        $alumno->apellido_paterno,
                        $alumno->apellido_materno,
                        $alumno->nombres
                    ])));

                    $fila = array_merge(
                        [
                            $indice + 1,
                            $alumno->matricula,
                            $nombre
                        ],
                        array_fill(0, 31, ''),
                        [
                            '',
                            ''
                        ]
                    );

                    $escribir($fila);
                }

                fclose($salida);
            },
            $nombreArchivo,
            [
                'Content-Type' => 'text/csv; charset=UTF-8',
                'Cache-Control' => 'private, no-store, no-cache, must-revalidate',
                'Pragma' => 'no-cache',
                'Expires' => '0',
                'X-Content-Type-Options' => 'nosniff'
            ]
        );
    }
}

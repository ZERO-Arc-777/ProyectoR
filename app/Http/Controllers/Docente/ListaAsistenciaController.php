<?php

namespace App\Http\Controllers\Docente;

use App\Models\Docente;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ListaAsistenciaController
{
    public function descargar(int $asignacion)
    {
        // Identificar al docente que inició sesión.
        $docente = Docente::where(
            'id_usuario',
            Auth::id()
        )->firstOrFail();

        // Buscar únicamente grupos asignados a este docente.
        $grupo = $docente->asignacionesGrupos()
            ->with(['materia', 'periodo'])
            ->where('id_asignacion', $asignacion)
            ->firstOrFail();

        // Obtener alumnos del semestre y grupo correspondiente,
        // inscritos o reinscritos en el periodo escolar asignado.
        $alumnos = DB::table('alumnos as a')
            ->where('a.semestre', $grupo->semestre)
            ->where('a.grupo', $grupo->grupo)
            ->where(function ($consulta) use ($grupo) {

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

        // Nombre del archivo descargable.
        $nombreArchivo = 'lista_asistencia_'
            . $grupo->id_asignacion . '.csv';

        // Generar el archivo compatible con Excel.
        return response()->streamDownload(
            function () use (
                $grupo,
                $alumnos,
                $nombreDocente
            ) {
                $salida = fopen('php://output', 'w');

                // Codificación UTF-8 compatible con Excel.
                fwrite($salida, "\xEF\xBB\xBF");

                // Función para escribir filas de forma segura.
                $escribir = function (array $fila) use ($salida) {

                    $fila = array_map(function ($valor) {

                        $texto = preg_replace(
                            '/[\r\n\t]+/u',
                            ' ',
                            (string) ($valor ?? '')
                        );

                        // Evitar fórmulas al abrir el CSV.
                        if (preg_match('/^\s*[=+\-@]/u', $texto)) {
                            $texto = "'" . $texto;
                        }

                        return $texto;

                    }, $fila);

                    fputcsv($salida, $fila, ';');
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

                // Agregar los estudiantes del grupo.
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
                        ['', '']
                    );

                    $escribir($fila);
                }

                fclose($salida);
            },
            $nombreArchivo,
            [
                'Content-Type' => 'text/csv; charset=UTF-8'
            ]
        );
    }
}

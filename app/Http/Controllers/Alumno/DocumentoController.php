<?php

namespace App\Http\Controllers\Alumno;

use App\Models\Alumno;
use App\Models\Configuracion;
use App\Models\Documento;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class DocumentoController
{
    private function catalogoProcesos(): array
    {
        return [
            'INSCRIPCION' => [
                'titulo' => 'Nuevo Ingreso (Inscripción)',
                'descripcion' => 'Documentos iniciales de tu expediente. Disponibles hasta ser aprobados por Control Escolar.',
                'icono' => 'bi-person-plus-fill',
                'semestre_minimo' => 1,
                'etiqueta_bloqueo' => 'Disponible desde 1° semestre',
                'por_semestre' => false,
                'documentos' => [
                    'CURP',
                    'Acta de Nacimiento',
                    'Certificado de Secundaria',
                ],
            ],
            'REINSCRIPCION' => [
                'titulo' => 'Pase de Semestre (Reinscripción)',
                'descripcion' => 'Actualización semestral requerida de 2° a 6° semestre. Se reactiva automáticamente al avanzar de semestre.',
                'icono' => 'bi-arrow-repeat',
                'semestre_minimo' => 2,
                'etiqueta_bloqueo' => 'Disponible a partir de 2° semestre',
                'por_semestre' => true,
                'documentos' => [
                    'CURP actualizada',
                    'Acta de Nacimiento actualizada',
                ],
            ],
            'EGRESO' => [
                'titulo' => 'Trámites Finales (Egreso)',
                'descripcion' => 'Documentación de cierre habilitada al cursar 6° semestre.',
                'icono' => 'bi-mortarboard-fill',
                'semestre_minimo' => 6,
                'etiqueta_bloqueo' => 'Disponible en 6° semestre',
                'por_semestre' => false,
                'documentos' => [
                    'Certificado de Preparatoria',
                ],
            ],
        ];
    }

    public function index()
    {
        $alumno = Alumno::where('id_usuario', Auth::user()->id_usuario)
            ->firstOrFail();

        $semestreActual = (int) $alumno->semestre;

        $documentos = Documento::where('id_alumno', $alumno->id_alumno)
            ->orderByDesc('updated_at')
            ->orderByDesc('id_documento')
            ->get();

        $secciones = [];

        foreach ($this->catalogoProcesos() as $claveProceso => $config) {
            $habilitadoPorSemestre = $semestreActual >= $config['semestre_minimo'];
            $tarjetas = [];

            foreach ($config['documentos'] as $tipoDocumento) {
                $registro = $documentos->first(function ($doc) use ($claveProceso, $tipoDocumento, $config, $semestreActual) {
                    if ($doc->proceso !== $claveProceso || $doc->tipo_documento !== $tipoDocumento) {
                        return false;
                    }

                    if ($config['por_semestre']) {
                        return (int) $doc->semestre === $semestreActual;
                    }

                    return true;
                });

                if (!$habilitadoPorSemestre) {
                    $estadoTarjeta = 'BLOQUEADO';
                } elseif (!$registro) {
                    $estadoTarjeta = 'DISPONIBLE';
                } else {
                    $estadoTarjeta = $registro->estado_validacion; // PENDIENTE, APROBADO o RECHAZADO
                }

                $tarjetas[] = [
                    'proceso' => $claveProceso,
                    'tipo_documento' => $tipoDocumento,
                    'estado' => $estadoTarjeta,
                    'puede_subir' => in_array($estadoTarjeta, ['DISPONIBLE', 'RECHAZADO'], true),
                    'registro' => $registro,
                ];
            }

            $secciones[] = [
                'clave' => $claveProceso,
                'titulo' => $config['titulo'],
                'descripcion' => $config['descripcion'],
                'icono' => $config['icono'],
                'habilitado' => $habilitadoPorSemestre,
                'etiqueta_bloqueo' => $config['etiqueta_bloqueo'],
                'por_semestre' => $config['por_semestre'],
                'tarjetas' => $tarjetas,
            ];
        }

        return view('alumno.documentos.index', compact('alumno', 'documentos', 'secciones'));
    }

    public function store(Request $request)
    {
        $alumno = Alumno::where('id_usuario', Auth::user()->id_usuario)
            ->firstOrFail();

        $catalogo = $this->catalogoProcesos();

        $datos = $request->validate([
            'proceso' => ['required', 'string', Rule::in(array_keys($catalogo))],
            'tipo_documento' => ['required', 'string', 'max:100'],
            'archivo' => ['required', 'file', 'mimes:pdf', 'max:2048'],
        ], [
            'archivo.required' => 'Debes seleccionar un archivo PDF antes de enviar.',
            'archivo.mimes' => 'El documento debe estar en formato PDF.',
            'archivo.max' => 'El archivo no debe superar los 2 MB.',
        ]);

        $proceso = $datos['proceso'];
        $tipoDocumento = $datos['tipo_documento'];
        $config = $catalogo[$proceso];
        $semestreActual = (int) $alumno->semestre;
        $periodoActual = Configuracion::where('clave', 'periodo_actual')->value('valor') ?: 'No definido';

        // 1. Validar que el semestre del alumno permita este trámite
        if ($semestreActual < $config['semestre_minimo']) {
            return redirect()
                ->back()
                ->with('error', "El trámite {$config['titulo']} aún no está habilitado para tu semestre actual ({$semestreActual}°).");
        }

        // 2. Validar que el documento pertenezca al trámite seleccionado
        if (!in_array($tipoDocumento, $config['documentos'], true)) {
            return redirect()
                ->back()
                ->with('error', 'El tipo de documento seleccionado no corresponde a este trámite.');
        }

        // 3. Bloqueo anti-spam y protección de documentos pendientes o aprobados
        $consultaExistente = Documento::where('id_alumno', $alumno->id_alumno)
            ->where('proceso', $proceso)
            ->where('tipo_documento', $tipoDocumento);

        if ($config['por_semestre']) {
            $consultaExistente->where('semestre', $semestreActual);
        }

        $ultimoDocumento = $consultaExistente
            ->orderByDesc('updated_at')
            ->orderByDesc('id_documento')
            ->first();

        if ($ultimoDocumento) {
            if ($ultimoDocumento->estado_validacion === 'PENDIENTE') {
                return redirect()
                    ->back()
                    ->with('error', "Ya enviaste \"{$tipoDocumento}\" y se encuentra en revisión. Espera respuesta de Control Escolar.");
            }

            if ($ultimoDocumento->estado_validacion === 'APROBADO') {
                return redirect()
                    ->back()
                    ->with('error', "\"{$tipoDocumento}\" ya fue aprobado para este proceso.");
            }
        }

        // 4. Verificar si ya existe un registro con la misma llave única del periodo actual
        $documentoMismoPeriodo = Documento::where('id_alumno', $alumno->id_alumno)
            ->where('tipo_documento', $tipoDocumento)
            ->where('proceso', $proceso)
            ->where('periodo', $periodoActual)
            ->first();

        if ($documentoMismoPeriodo && in_array($documentoMismoPeriodo->estado_validacion, ['PENDIENTE', 'APROBADO'], true)) {
            return redirect()
                ->back()
                ->with('error', "Ya existe un registro activo de \"{$tipoDocumento}\" en el periodo {$periodoActual}.");
        }

        $rutaAnterior = $documentoMismoPeriodo?->ruta_archivo;

        // 5. Guardar el nuevo PDF y crear o actualizar respetando la restricción única
        $rutaArchivo = $request->file('archivo')->store('expedientes/' . $alumno->matricula, 'local');

        Documento::updateOrCreate(
            [
                'id_alumno' => $alumno->id_alumno,
                'tipo_documento' => $tipoDocumento,
                'proceso' => $proceso,
                'periodo' => $periodoActual,
            ],
            [
                'semestre' => $semestreActual,
                'ruta_archivo' => $rutaArchivo,
                'estado_validacion' => 'PENDIENTE',
                'observaciones' => null,
            ]
        );

        // 6. Eliminar del disco el PDF rechazado anterior para no acumular archivos huérfanos
        if ($rutaAnterior && $rutaAnterior !== $rutaArchivo && Storage::disk('local')->exists($rutaAnterior)) {
            Storage::disk('local')->delete($rutaAnterior);
        }

        return redirect()
            ->route('alumno.documentos')
            ->with('success', "El documento \"{$tipoDocumento}\" fue enviado correctamente a revisión.");
    }
}

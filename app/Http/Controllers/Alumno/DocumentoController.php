<?php

namespace App\Http\Controllers\Alumno;

use App\Models\Alumno;
use App\Models\Documento;
use Illuminate\Database\QueryException;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class DocumentoController
{
    // ==========================================
    // CONSULTAR DOCUMENTOS DEL ALUMNO
    // ==========================================

    public function index()
    {
        // Obtener únicamente el alumno autenticado.
        $alumno = Alumno::where(
            'id_usuario',
            Auth::id()
        )->firstOrFail();

        // Consultar sus documentos ordenados por fecha.
        $documentos = Documento::where(
            'id_alumno',
            $alumno->id_alumno
        )
            ->orderBy('created_at', 'desc')
            ->get();

        return view(
            'alumno.documentos.index',
            compact('alumno', 'documentos')
        );
    }

    // ==========================================
    // SUBIR O REEMPLAZAR DOCUMENTO
    // ==========================================

    public function store(Request $request)
    {
        // Validar los datos y el archivo recibido.
        $datos = $request->validate([
            'tipo_documento' => [
                'required',
                'string',
                'max:100',
            ],
            'proceso' => [
                'required',
                'in:INSCRIPCION,REINSCRIPCION,EGRESO',
            ],
            'archivo' => [
                'required',
                'mimes:pdf',
                'max:2048',
            ],
        ]);

        // Identificar al alumno autenticado.
        $alumno = Alumno::where(
            'id_usuario',
            Auth::id()
        )->firstOrFail();

        // Guardar el PDF en almacenamiento privado.
        $rutaNueva = $request->file('archivo')->store(
            'expedientes/' . $alumno->matricula,
            'local'
        );

        if (!$rutaNueva) {
            throw new \RuntimeException(
                'No se pudo almacenar el nuevo documento PDF.'
            );
        }

        try {
            // Proteger las operaciones mediante una transacción.
            $resultado = DB::transaction(
                function () use ($alumno, $datos, $rutaNueva) {

                    // Bloquear el registro mientras se consulta.
                    $documentoExistente = Documento::where(
                        'id_alumno',
                        $alumno->id_alumno
                    )
                        ->where(
                            'tipo_documento',
                            $datos['tipo_documento']
                        )
                        ->where(
                            'proceso',
                            $datos['proceso']
                        )
                        ->lockForUpdate()
                        ->first();

                    // No permitir reemplazar documentos
                    // pendientes o aprobados.
                    if (
                        $documentoExistente &&
                        in_array(
                            $documentoExistente->estado_validacion,
                            ['PENDIENTE', 'APROBADO'],
                            true
                        )
                    ) {
                        return [
                            'permitido' => false,
                            'ruta_anterior' => null,
                            'estado' =>
                                $documentoExistente->estado_validacion,
                        ];
                    }

                    $rutaAnterior = null;

                    if ($documentoExistente) {
                        // Conservar la ruta del archivo anterior.
                        $rutaAnterior =
                            $documentoExistente->ruta_archivo;

                        // Reemplazar únicamente el documento
                        // que anteriormente fue rechazado.
                        $documentoExistente->update([
                            'ruta_archivo' => $rutaNueva,
                            'estado_validacion' => 'PENDIENTE',
                            'observaciones' => null,
                        ]);
                    } else {
                        // Crear un registro nuevo.
                        // El índice único previene duplicados.
                        Documento::create([
                            'id_alumno' =>
                                $alumno->id_alumno,
                            'tipo_documento' =>
                                $datos['tipo_documento'],
                            'proceso' =>
                                $datos['proceso'],
                            'ruta_archivo' =>
                                $rutaNueva,
                            'estado_validacion' =>
                                'PENDIENTE',
                        ]);
                    }

                    return [
                        'permitido' => true,
                        'ruta_anterior' => $rutaAnterior,
                        'estado' => null,
                    ];
                },
                3
            );

        } catch (UniqueConstraintViolationException $e) {

            // Eliminar el archivo nuevo si el registro
            // no pudo completarse por un duplicado.
            Storage::disk('local')->delete($rutaNueva);

            $duplicado = Documento::where(
                'id_alumno',
                $alumno->id_alumno
            )
                ->where(
                    'tipo_documento',
                    $datos['tipo_documento']
                )
                ->where(
                    'proceso',
                    $datos['proceso']
                )
                ->exists();

            if ($duplicado) {
                return back()->withErrors([
                    'archivo' =>
                        'Este documento ya fue registrado. ' .
                        'Actualiza tu expediente antes de volver a intentarlo.',
                ]);
            }

            throw $e;

        } catch (QueryException $e) {

            // Eliminar el archivo nuevo si falla la transacción.
            Storage::disk('local')->delete($rutaNueva);

            // Identificar el error 1205 de MariaDB:
            // tiempo de espera del bloqueo.
            if (
                ($e->errorInfo[0] ?? null) === 'HY000' &&
                (int) ($e->errorInfo[1] ?? 0) === 1205
            ) {
                return back()->withErrors([
                    'archivo' =>
                        'El documento está siendo procesado ' .
                        'por otra solicitud. Intenta nuevamente ' .
                        'en unos momentos.',
                ]);
            }

            // No ocultar otros errores de base de datos.
            throw $e;

        } catch (\Throwable $e) {

            // Limpiar el archivo ante cualquier otro error.
            Storage::disk('local')->delete($rutaNueva);

            throw $e;
        }

        // ==========================================
        // COMPROBAR RESULTADO
        // ==========================================

        // Si el documento ya estaba pendiente o aprobado,
        // eliminar únicamente el archivo recién subido.
        if (!$resultado['permitido']) {
            Storage::disk('local')->delete($rutaNueva);

            return back()->withErrors([
                'archivo' =>
                    'Ya subiste este documento y se encuentra ' .
                    strtolower($resultado['estado']) .
                    '. No puedes subirlo de nuevo.',
            ]);
        }

        // Eliminar el archivo anterior únicamente
        // después de confirmar la transacción.
        $rutaAnterior = $resultado['ruta_anterior'];

        if (
            $rutaAnterior &&
            $rutaAnterior !== $rutaNueva
        ) {
            Storage::disk('local')->delete($rutaAnterior);
        }

        return back()->with(
            'success',
            'Documento subido correctamente. Esperando revisión.'
        );
    }
}

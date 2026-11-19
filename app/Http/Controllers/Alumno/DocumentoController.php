<?php

namespace App\Http\Controllers\Alumno;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\Models\Documento;
use App\Models\Alumno;
use Illuminate\Support\Facades\DB;

class DocumentoController
{
    public function index()
    {
        $alumno = Alumno::where('id_usuario', Auth::id())->firstOrFail();

        // Obtenemos los documentos ordenados por fecha
        $documentos = Documento::where('id_alumno', $alumno->id_alumno)
                               ->orderBy('created_at', 'desc')
                               ->get();

        return view('alumno.documentos.index', compact('alumno', 'documentos'));
    }



public function store(Request $request)
{
    $datos = $request->validate([
        'tipo_documento' => 'required|string|max:100',
        'proceso'        => 'required|in:INSCRIPCION,REINSCRIPCION,EGRESO',
        'archivo'        => 'required|mimes:pdf|max:2048',
    ]);

    $alumno = Alumno::where('id_usuario', Auth::id())
        ->firstOrFail();

    // Guardar primero el archivo nuevo en almacenamiento privado.
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
        // La transacción protege la consulta y actualización del registro.
        $resultado = DB::transaction(function () use ($alumno, $datos, $rutaNueva) {

            // Bloquear el documento mientras se comprueba su estado.
            $documentoExistente = Documento::where(
                'id_alumno',
                $alumno->id_alumno
            )
                ->where('tipo_documento', $datos['tipo_documento'])
                ->where('proceso', $datos['proceso'])
                ->lockForUpdate()
                ->first();

            // Rechazar si otra solicitud ya lo dejó pendiente o aprobado.
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
                    'estado' => $documentoExistente->estado_validacion,
                ];
            }

            $rutaAnterior = null;

            if ($documentoExistente) {
                // Conservar la ruta anterior para limpiarla después.
                $rutaAnterior = $documentoExistente->ruta_archivo;

                $documentoExistente->update([
                    'ruta_archivo'      => $rutaNueva,
                    'estado_validacion' => 'PENDIENTE',
                    'observaciones'     => null,
                ]);
            } else {
                // El índice único también protege las inserciones simultáneas.
                Documento::create([
                    'id_alumno'         => $alumno->id_alumno,
                    'tipo_documento'    => $datos['tipo_documento'],
                    'proceso'           => $datos['proceso'],
                    'ruta_archivo'      => $rutaNueva,
                    'estado_validacion' => 'PENDIENTE',
                ]);
            }

            return [
                'permitido' => true,
                'ruta_anterior' => $rutaAnterior,
                'estado' => null,
            ];
                }, 3);

    } catch (\Illuminate\Database\UniqueConstraintViolationException $e) {

        // Eliminar únicamente el archivo de la solicitud rechazada.
        Storage::disk('local')->delete($rutaNueva);

        // Confirmar que el conflicto corresponde al documento solicitado.
        $duplicado = Documento::where('id_alumno', $alumno->id_alumno)
            ->where('tipo_documento', $datos['tipo_documento'])
            ->where('proceso', $datos['proceso'])
            ->exists();

        if ($duplicado) {
            return back()->withErrors([
                'archivo' => 'Este documento ya fue registrado. '
                    . 'Actualiza tu expediente antes de volver a intentarlo.'
            ]);
        }

        throw $e;


    } catch (\Illuminate\Database\QueryException $e) {

        // Eliminar el archivo nuevo si falla la transacción.
        Storage::disk('local')->delete($rutaNueva);

        // Error 1205 de MariaDB: tiempo de espera del bloqueo.
        if (
    ($e->errorInfo[0] ?? null) === 'HY000' &&
    (int) ($e->errorInfo[1] ?? 0) === 1205
) {
            return back()->withErrors([
                'archivo' => 'El documento está siendo procesado '
                    . 'por otra solicitud. Intenta nuevamente '
                    . 'en unos momentos.'
            ]);
        }

        // No ocultar otros errores de la base de datos.
        throw $e;

    } catch (\Throwable $e) {

        // Limpiar el archivo nuevo ante cualquier otro error.
        Storage::disk('local')->delete($rutaNueva);

        throw $e;
    }


    // La transacción terminó. Comprobar si rechazó el reemplazo.
    if (!$resultado['permitido']) {
        Storage::disk('local')->delete($rutaNueva);

        return back()->withErrors([
            'archivo' => 'Ya subiste este documento y se encuentra ' .
                strtolower($resultado['estado']) .
                '. No puedes subirlo de nuevo.'
        ]);
    }

    // Eliminar el PDF antiguo solo después de confirmar el cambio.
    $rutaAnterior = $resultado['ruta_anterior'];

    if ($rutaAnterior && $rutaAnterior !== $rutaNueva) {
        Storage::disk('local')->delete($rutaAnterior);
    }

    return back()->with(
        'success',
        'Documento subido correctamente. Esperando revisión.'
    );
}
}

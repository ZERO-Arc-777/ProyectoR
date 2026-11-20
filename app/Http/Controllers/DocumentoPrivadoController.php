<?php

namespace App\Http\Controllers;

use App\Models\Alumno;
use App\Models\Documento;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class DocumentoPrivadoController
{
    public function ver($id_documento)
    {
        // Obtener el documento solicitado.
        $documento = Documento::findOrFail($id_documento);

        // Obtener el usuario autenticado.
        $usuario = Auth::user();

        // Impedir acceso a cuentas inexistentes o inactivas.
        abort_unless(
            $usuario && $usuario->estado === 'ACTIVO',
            403
        );

        // Comprobar el rol del usuario.
        $esAdministrador = (int) $usuario->id_rol === 1;
        $esAlumno = (int) $usuario->id_rol === 3;

        // Los docentes y otros roles no pueden consultar expedientes.
        abort_unless($esAdministrador || $esAlumno, 403);

        // Si es alumno, comprobar que el documento le pertenece.
        if ($esAlumno) {
            $alumno = Alumno::where(
                'id_usuario',
                $usuario->id_usuario
            )->first();

            abort_unless(
                $alumno &&
                (int) $documento->id_alumno === (int) $alumno->id_alumno,
                403
            );
        }

        // Comprobar que el PDF existe en almacenamiento privado.
        abort_unless(
            Storage::disk('local')->exists($documento->ruta_archivo),
            404
        );

        // Entregar el PDF sin convertirlo en un enlace público.
        return response()->file(
            Storage::disk('local')->path($documento->ruta_archivo),
            [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'inline',
                'X-Content-Type-Options' => 'nosniff',
                'Cache-Control' => 'private, no-store',
            ]
        );
    }
}

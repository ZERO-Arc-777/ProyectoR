<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Documento;

class ExpedienteController
{
    public function inscripciones(Request $request)
    {
        $buscar = $request->get('buscar');
        $documentos = $this->obtenerDocumentos('INSCRIPCION', $buscar);
        return view('admin.expedientes.revision', compact('documentos', 'buscar'))->with('titulo', 'Revisión de Inscripciones');
    }

    public function reinscripciones(Request $request)
    {
        $buscar = $request->get('buscar');
        $documentos = $this->obtenerDocumentos('REINSCRIPCION', $buscar);
        return view('admin.expedientes.revision', compact('documentos', 'buscar'))->with('titulo', 'Revisión de Reinscripciones');
    }

    public function egresos(Request $request)
    {
        $buscar = $request->get('buscar');
        $documentos = $this->obtenerDocumentos('EGRESO', $buscar);
        return view('admin.expedientes.revision', compact('documentos', 'buscar'))->with('titulo', 'Revisión de Egresos');
    }

    // Función privada para no repetir el código del buscador 3 veces
    private function obtenerDocumentos($proceso, $buscar)
    {
        return Documento::with('alumno')
            ->where('proceso', $proceso)
            ->when($buscar, function ($query, $buscar) {
                return $query->whereHas('alumno', function($q) use ($buscar) {
                    $q->where('matricula', 'LIKE', "%{$buscar}%")
                      ->orWhere('nombres', 'LIKE', "%{$buscar}%")
                      ->orWhere('apellido_paterno', 'LIKE', "%{$buscar}%");
                });
            })
            ->orderBy('created_at', 'asc') // Los más antiguos primero
            ->paginate(10);
    }

    public function updateEstado(Request $request, $id_documento)
    {
        $request->validate([
            'estado_validacion' => 'required|in:PENDIENTE,APROBADO,RECHAZADO',
            'observaciones'     => 'nullable|string'
        ]);

        $documento = Documento::findOrFail($id_documento);
        $documento->update([
            'estado_validacion' => $request->estado_validacion,
            'observaciones'     => $request->observaciones
        ]);

        return back()->with('success', 'Estado del documento actualizado a: ' . $request->estado_validacion);
    }
}

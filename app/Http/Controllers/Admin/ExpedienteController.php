<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Documento;

class ExpedienteController
{
    public function inscripciones()
    {
        $documentos = Documento::with('alumno')->where('proceso', 'INSCRIPCION')->get();
        return view('admin.expedientes.revision', compact('documentos'))->with('titulo', 'Revisión de Inscripciones');
    }

    public function reinscripciones()
    {
        $documentos = Documento::with('alumno')->where('proceso', 'REINSCRIPCION')->get();
        return view('admin.expedientes.revision', compact('documentos'))->with('titulo', 'Revisión de Reinscripciones');
    }

    public function egresos()
    {
        $documentos = Documento::with('alumno')->where('proceso', 'EGRESO')->get();
        return view('admin.expedientes.revision', compact('documentos'))->with('titulo', 'Revisión de Egresos');
    }

    public function updateEstado(Request $request, $id_documento)
    {
        $request->validate([
            'estado_validacion' => 'required|in:APROBADO,RECHAZADO',
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

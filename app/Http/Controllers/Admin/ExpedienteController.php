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
        return view('admin.expedientes.index', compact('documentos', 'buscar'))->with('titulo', 'Revisión de Inscripciones');
    }

    public function reinscripciones(Request $request)
    {
        $buscar = $request->get('buscar');
        $documentos = $this->obtenerDocumentos('REINSCRIPCION', $buscar);
        return view('admin.expedientes.index', compact('documentos', 'buscar'))->with('titulo', 'Revisión de Reinscripciones');
    }

    public function egresos(Request $request)
    {
        $buscar = $request->get('buscar');
        $documentos = $this->obtenerDocumentos('EGRESO', $buscar);
        return view('admin.expedientes.index', compact('documentos', 'buscar'))->with('titulo', 'Revisión de Egresos');
    }

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
            ->orderBy('created_at', 'asc')
            ->paginate(10);
    }

    public function updateEstado(Request $request, $id_documento)
    {
        $datos = $request->validate([
    'estado_validacion' => [
        'required',
        'in:PENDIENTE,APROBADO,RECHAZADO',
    ],
    'observaciones' => [
        'nullable',
        'string',
        'max:5000',
    ],
], [
    'estado_validacion.required' => 'Debes seleccionar un estado de validación.',
    'estado_validacion.in' => 'El estado seleccionado no es válido.',
    'observaciones.string' => 'Las observaciones deben contener texto.',
    'observaciones.max' => 'Las observaciones no deben superar los 5000 caracteres.',
]);

        $documento = Documento::findOrFail($id_documento);
        $documento->update($datos);

        return back()->with('success', 'Estado del documento actualizado a: ' . $request->estado_validacion);
    }
}

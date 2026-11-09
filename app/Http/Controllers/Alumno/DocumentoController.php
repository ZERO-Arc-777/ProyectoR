<?php

namespace App\Http\Controllers\Alumno;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\Models\Documento;
use App\Models\Alumno;

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
        $request->validate([
            'tipo_documento' => 'required|string',
            'proceso'        => 'required|in:INSCRIPCION,REINSCRIPCION,EGRESO',
            'archivo'        => 'required|mimes:pdf|max:2048'
        ]);

        $alumno = Alumno::where('id_usuario', Auth::id())->firstOrFail();

        $documentoExistente = Documento::where('id_alumno', $alumno->id_alumno)
                                       ->where('tipo_documento', $request->tipo_documento)
                                       ->where('proceso', $request->proceso)
                                       ->first();

        if ($documentoExistente) {
            if (in_array($documentoExistente->estado_validacion, ['PENDIENTE', 'APROBADO'])) {
                return back()->withErrors(['archivo' => 'Ya subiste este documento y se encuentra ' . strtolower($documentoExistente->estado_validacion) . '. No puedes subirlo de nuevo.']);
            }
            
            if (Storage::disk('public')->exists($documentoExistente->ruta_archivo)) {
                Storage::disk('public')->delete($documentoExistente->ruta_archivo);
            }
            
            $documentoExistente->delete();
        }

        if ($request->hasFile('archivo')) {
            $ruta = $request->file('archivo')->store('expedientes/' . $alumno->matricula, 'public');

            Documento::create([
                'id_alumno'         => $alumno->id_alumno,
                'tipo_documento'    => $request->tipo_documento,
                'proceso'           => $request->proceso,
                'ruta_archivo'      => $ruta,
                'estado_validacion' => 'PENDIENTE'
            ]);
        }

        return back()->with('success', 'Documento subido correctamente. Esperando revisión.');
    }
}

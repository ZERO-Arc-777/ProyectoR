<?php

namespace App\Http\Controllers\Alumno;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Documento;
use App\Models\Alumno;

class DocumentoController
{
    public function index()
    {
        $alumno = Alumno::where('id_usuario', Auth::id())->firstOrFail();
        $documentos = Documento::where('id_alumno', $alumno->id_alumno)->get();
        
        return view('alumno.documentos.index', compact('alumno', 'documentos'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'tipo_documento' => 'required|string',
            'proceso'        => 'required|in:INSCRIPCION,REINSCRIPCION,EGRESO',
            'archivo'        => 'required|mimes:pdf|max:2048' // Solo PDFs hasta 2MB
        ]);

        $alumno = Alumno::where('id_usuario', Auth::id())->firstOrFail();

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

        return back()->with('success', 'Documento subido correctamente. Esperando validación.');
    }
}

<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Models\Solicitud;

class PagoController
{
    public function index()
    {
        $solicitudes = Solicitud::with('alumno')->orderBy('created_at', 'desc')->get();
        
        return view('admin.pagos.index', compact('solicitudes'));
    }
    
    public function updateEstado(Request $request, $id_solicitud)
    {
        $request->validate([
            'estado' => 'required|in:PENDIENTE,PAGADA,CANCELADA'
        ]);

        $solicitud = Solicitud::findOrFail($id_solicitud);
        $solicitud->update(['estado' => $request->estado]);

        return redirect()->route('admin.pagos')
                         ->with('success', 'El estado del folio ' . $solicitud->folio . ' ha sido actualizado a ' . $request->estado . '.');
    }
}

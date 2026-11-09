<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Models\Solicitud;

class PagoController
{
    public function index(Request $request)
    {
        $buscar = $request->get('buscar');
        $estadoFiltro = $request->get('estado', 'PENDIENTE');

        $solicitudes = Solicitud::with(['alumno', 'detalles.servicio'])
            ->where('estado', $estadoFiltro)
            ->when($buscar, function ($query, $buscar) {
                return $query->where('folio', 'LIKE', "%{$buscar}%")
                             ->orWhereHas('alumno', function($q) use ($buscar) {
                                 $q->where('matricula', 'LIKE', "%{$buscar}%")
                                   ->orWhere('nombres', 'LIKE', "%{$buscar}%")
                                   ->orWhere('apellido_paterno', 'LIKE', "%{$buscar}%");
                             });
            })
            ->orderBy('created_at', 'desc')
            ->paginate(10); 
            
        return view('admin.pagos.index', compact('solicitudes', 'buscar', 'estadoFiltro'));
    }
    
    public function updateEstado(Request $request, $id_solicitud)
    {
        $request->validate([
            'estado' => 'required|in:PENDIENTE,PAGADA,CANCELADA'
        ]);

        $solicitud = Solicitud::findOrFail($id_solicitud);
        $solicitud->update(['estado' => $request->estado]);

        return redirect()->back()
                         ->with('success', 'El estado del folio ' . $solicitud->folio . ' ha sido actualizado a ' . $request->estado . '.');
    }
}

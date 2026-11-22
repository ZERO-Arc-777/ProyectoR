<?php

namespace App\Http\Controllers\Admin;

use App\Models\Solicitud;
use Illuminate\Http\Request;

class PagoController
{
    public function index(Request $request)
    {
        $buscar = trim((string) $request->get('buscar', ''));
        $estadoFiltro = $request->get('estado', 'PENDIENTE');

        $solicitudes = Solicitud::with(['alumno', 'detalles.servicio'])
            ->where('estado', $estadoFiltro)
            ->when($buscar !== '', function ($query) use ($buscar) {
                return $query->where(function ($q) use ($buscar) {
                    $q->where('folio', 'LIKE', "%{$buscar}%")
                        ->orWhereHas('alumno', function ($alumno) use ($buscar) {
                            $alumno->where(function ($datos) use ($buscar) {
                                $datos->where('matricula', 'LIKE', "%{$buscar}%")
                                    ->orWhere('nombres', 'LIKE', "%{$buscar}%")
                                    ->orWhere('apellido_paterno', 'LIKE', "%{$buscar}%");
                            });
                        });
                });
            })
            ->orderBy('created_at', 'desc')
            ->paginate(10)
            ->withQueryString();

        return view('admin.pagos.index', compact('solicitudes', 'buscar', 'estadoFiltro'));
    }

    public function updateEstado(Request $request, $id_solicitud)
    {
        $request->validate([
            'estado' => 'required|in:PENDIENTE,PAGADA,CANCELADA',
        ]);

        $solicitud = Solicitud::findOrFail($id_solicitud);
        $solicitud->update(['estado' => $request->estado]);

        return redirect()->back()
            ->with('success', 'El estado del folio ' . $solicitud->folio . ' ha sido actualizado a ' . $request->estado . '.');
    }
}

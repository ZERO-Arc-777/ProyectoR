<?php

namespace App\Http\Controllers\Admin;

use App\Models\Servicio;
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

    // ==========================================
    // CATÁLOGO DE PAGOS (SERVICIOS)
    // ==========================================

    public function catalogo(Request $request)
    {
        $buscar = trim((string) $request->get('buscar', ''));
        $editarId = $request->get('editar');
        $servicioEditar = is_numeric($editarId) ? Servicio::find((int) $editarId) : null;

        $servicios = Servicio::withCount('detalles')
            ->when($buscar !== '', function ($query) use ($buscar) {
                return $query->where(function ($q) use ($buscar) {
                    $q->where('articulo', 'LIKE', "%{$buscar}%")
                        ->orWhere('concepto', 'LIKE', "%{$buscar}%");
                });
            })
            ->orderBy('id_servicio', 'asc')
            ->paginate(10)
            ->withQueryString();

        return view('admin.pagos.editorP', compact('servicios', 'buscar', 'servicioEditar'));
    }

    public function storeServicio(Request $request)
    {
        $datos = $this->validarDatosServicio($request);

        if ($datos['total'] > 999999.99) {
            return redirect()->back()
                ->withInput()
                ->withErrors(['total' => 'La suma de costo del servicio y derechos no puede superar $999,999.99.']);
        }

        Servicio::create($datos);

        return redirect()->route('admin.pagos.catalogo')
            ->with('success', 'Concepto agregado correctamente al catálogo de pagos.');
    }

    public function updateServicio(Request $request, $id_servicio)
    {
        $servicio = Servicio::findOrFail($id_servicio);
        $datos = $this->validarDatosServicio($request);

        if ($datos['total'] > 999999.99) {
            return redirect()->back()
                ->withInput()
                ->withErrors(['total' => 'La suma de costo del servicio y derechos no puede superar $999,999.99.']);
        }

        $servicio->update($datos);

        return redirect()->route('admin.pagos.catalogo')
            ->with('success', 'El concepto "' . $servicio->concepto . '" ha sido actualizado.');
    }

    public function destroyServicio($id_servicio)
    {
        $servicio = Servicio::findOrFail($id_servicio);

        if ($servicio->detalles()->exists()) {
            return redirect()->route('admin.pagos.catalogo')
                ->with('error', 'No se puede eliminar "' . $servicio->concepto . '" porque ya tiene solicitudes registradas. Puedes editar sus datos si necesitas actualizarlo.');
        }

        $concepto = $servicio->concepto;
        $servicio->delete();

        return redirect()->route('admin.pagos.catalogo')
            ->with('success', 'El concepto "' . $concepto . '" fue eliminado del catálogo.');
    }

    private function validarDatosServicio(Request $request): array
    {
        $validated = $request->validate(
            [
                'articulo' => 'nullable|string|max:50',
                'concepto' => 'required|string|max:255',
                'costo_servicio' => 'required|numeric|min:0|max:999999.99',
                'derechos' => 'required|numeric|min:0|max:999999.99',
            ],
            [
                'articulo.max' => 'El campo artículo no puede exceder los 50 caracteres.',
                'concepto.required' => 'El concepto es obligatorio.',
                'concepto.max' => 'El concepto no puede exceder los 255 caracteres.',
                'costo_servicio.required' => 'El costo del servicio es obligatorio.',
                'costo_servicio.numeric' => 'El costo del servicio debe ser un número válido.',
                'costo_servicio.min' => 'El costo del servicio no puede ser negativo.',
                'costo_servicio.max' => 'El costo del servicio excede el límite permitido.',
                'derechos.required' => 'El monto de derechos es obligatorio.',
                'derechos.numeric' => 'El monto de derechos debe ser un número válido.',
                'derechos.min' => 'El monto de derechos no puede ser negativo.',
                'derechos.max' => 'El monto de derechos excede el límite permitido.',
            ]
        );

        $articulo = trim((string) ($validated['articulo'] ?? ''));
        $costoServicio = round((float) $validated['costo_servicio'], 2);
        $derechos = round((float) $validated['derechos'], 2);

        return [
            'articulo' => $articulo !== '' ? $articulo : null,
            'concepto' => trim($validated['concepto']),
            'costo_servicio' => $costoServicio,
            'derechos' => $derechos,
            'total' => round($costoServicio + $derechos, 2),
        ];
    }
}

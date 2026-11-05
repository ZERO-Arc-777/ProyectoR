<?php

namespace App\Http\Controllers\Alumno;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Servicio;
use App\Models\Solicitud;
use App\Models\SolicitudDetalle;
use App\Models\Alumno;
use Illuminate\Support\Str;

class SolicitudController
{
    public function index()
    {
        $alumno = Alumno::where('id_usuario', Auth::id())->first();
        
        if (!$alumno) {
            abort(403, 'Tu cuenta aún no tiene un expediente escolar vinculado. Acude a control escolar.');
        }

        $solicitudes = Solicitud::where('id_alumno', $alumno->id_alumno)
                                ->orderBy('created_at', 'desc')
                                ->get();

        return view('alumno.solicitudes.index', compact('solicitudes'));
    }

    public function create()
    {
        $servicios = Servicio::all();
        return view('alumno.solicitudes.create', compact('servicios'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'servicios' => 'required|array',
            'servicios.*' => 'exists:servicios,id_servicio'
        ]);

        $alumno = Alumno::where('id_usuario', Auth::id())->first();

        $serviciosElegidos = Servicio::whereIn('id_servicio', $request->servicios)->get();
        $total = $serviciosElegidos->sum('total');

        $folio = 'SOL-' . date('Y') . '-' . strtoupper(Str::random(5));

        $solicitud = Solicitud::create([
            'id_alumno' => $alumno->id_alumno,
            'folio' => $folio,
            'total_pagar' => $total,
            'estado' => 'PENDIENTE'
        ]);

        foreach ($serviciosElegidos as $servicio) {
            SolicitudDetalle::create([
                'id_solicitud' => $solicitud->id_solicitud,
                'id_servicio' => $servicio->id_servicio,
                'precio_cobrado' => $servicio->total
            ]);
        }

        return redirect()->route('alumno.solicitudes')->with('success', 'Solicitud generada con el folio: ' . $folio);
    }
}

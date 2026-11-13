<?php

namespace App\Http\Controllers\Docente;

use App\Models\Docente;
use Illuminate\Support\Facades\Auth;

class MateriaDController
{
    public function index()
    {
        // Identificar al docente que inicio sesion.
        $docente = Docente::where(
            'id_usuario',
            Auth::id()
        )->firstOrFail();

        // Consultar solamente sus materias asignadas.
        $materias = $docente->materias()
            ->orderBy('nombre')
            ->get();

        return view('docente.materias', compact('materias'));
    }
}

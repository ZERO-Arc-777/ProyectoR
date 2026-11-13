<?php

namespace App\Http\Controllers\Docente;

use App\Models\Docente;
use Illuminate\Support\Facades\Auth;

class GrupoController
{
    public function index()
    {
        // Identificar al docente autenticado.
        $docente = Docente::where(
            'id_usuario',
            Auth::id()
        )->firstOrFail();

        // Consultar solamente sus grupos asignados.
        $asignaciones = $docente->asignacionesGrupos()
            ->with(['materia', 'periodo'])
            ->orderBy('semestre')
            ->orderBy('grupo')
            ->get();

        return view(
            'docente.grupos',
            compact('asignaciones')
        );
    }
}

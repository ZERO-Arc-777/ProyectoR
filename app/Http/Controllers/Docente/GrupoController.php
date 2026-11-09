<?php

namespace App\Http\Controllers\Docente;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Models\Docente;

class GrupoController
{
    public function index()
    {
        $asignaciones = collect([
            (object) [
                'id_asignacion' => '1001',
                'semestre'      => 1,
                'grupo'         => 'A',
                'materia'       => (object) ['nombre' => 'Cultura Digital I. Ciudadanía digital'],
                'periodo'       => (object) ['nombre' => 'Febrero - Julio 2026']
            ]
        ]);

        return view('docente.grupos', compact('asignaciones'));
    }
}

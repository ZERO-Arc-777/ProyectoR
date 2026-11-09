<?php

namespace App\Http\Controllers\Docente;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Models\Docente;

class MateriaDController
{
    public function index()
    {
        $materias = collect([
            (object) [
                'id_materia' => '201',
                'nombre'     => 'Cultura Digital I. Ciudadanía digital',
                'semestre'   => 1,
                'clave'      => 'CD-101',
                'estado'     => 'ACTIVA'
            ]
        ]);

        return view('docente.materias', compact('materias'));
    }
}

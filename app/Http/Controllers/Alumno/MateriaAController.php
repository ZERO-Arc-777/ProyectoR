<?php

namespace App\Http\Controllers\Alumno;

use Illuminate\Support\Facades\Auth;
use App\Models\Alumno;
use App\Models\Materia;
use App\Models\Calificacion;

class MateriaAController
{
    public function index()
    {
        // 1. Identificamos al alumno logueado
        $alumno = Alumno::where('id_usuario', Auth::id())->firstOrFail();

        // 2. Traemos todas las materias del 1er al 6to semestre y las agrupamos mágicamente por semestre
        $materias = Materia::where('estado', 'ACTIVA')
            ->whereBetween('semestre', [1, 6])
            ->get()
            ->groupBy('semestre');

        // 3. Traemos solo las calificaciones de este alumno.
        $calificaciones = Calificacion::where('id_alumno', $alumno->id_alumno)
            ->pluck('calificacion', 'id_materia');

        return view('alumno.materias.index', compact('materias', 'calificaciones', 'alumno'));
    }
}

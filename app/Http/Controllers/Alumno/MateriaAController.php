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
        // Identificar al alumno autenticado.
        $alumno = Alumno::where('id_usuario', Auth::id())
            ->firstOrFail();

        // Consultar las materias activas del 1° al 6° semestre agrupadas por semestre.
        $materias = Materia::where('estado', 'ACTIVA')
            ->whereBetween('semestre', [1, 6])
            ->orderBy('nombre', 'asc')
            ->get()
            ->groupBy('semestre');

        // Obtener sus calificaciones indexadas por id_materia (conservando la más reciente).
        $calificaciones = Calificacion::where('id_alumno', $alumno->id_alumno)
            ->orderBy('id_calificacion', 'asc')
            ->pluck('calificacion', 'id_materia');

        return view(
            'alumno.materias.index',
            compact('alumno', 'materias', 'calificaciones')
        );
    }
}

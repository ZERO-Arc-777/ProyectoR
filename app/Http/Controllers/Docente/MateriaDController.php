<?php

namespace App\Http\Controllers\Docente;

use App\Models\Docente;
use App\Models\Materia;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;

class MateriaDController
{
    public function index()
    {
        // Identificar al docente que inició sesión.
        $docente = Docente::where(
            'id_usuario',
            Auth::id()
        )->firstOrFail();

        // Conservar las materias asignadas
        // mediante el sistema anterior.
        $materias = $docente->materias()->get();

        // Consultar las asignaciones del Administrador
        // solamente cuando exista la nueva tabla.
        if (Schema::hasTable('asignaciones_academicas')) {

            $materiasAdministrador = Materia::query()
                ->whereIn('id_materia', function ($consulta) use ($docente) {
                    $consulta->select('id_materia')
                        ->from('asignaciones_academicas')
                        ->where('id_docente', $docente->id_docente);
                })
                ->get();

            // Unir las materias de ambas estructuras.
            $materias = $materias->concat(
                $materiasAdministrador
            );
        }

        // Evitar materias duplicadas y ordenar.
        $materias = $materias
            ->unique('id_materia')
            ->sortBy('nombre')
            ->values();

        return view(
            'docente.materias',
            compact('materias')
        );
    }
}

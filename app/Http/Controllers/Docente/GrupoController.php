<?php

namespace App\Http\Controllers\Docente;

use App\Models\Docente;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class GrupoController
{
    public function index()
    {
        // Identificar al docente autenticado.
        $docente = Docente::where(
            'id_usuario',
            Auth::id()
        )->firstOrFail();

        // Recuperar las asignaciones anteriores.
        $asignacionesAnteriores = $docente
            ->asignacionesGrupos()
            ->with(['materia', 'periodo'])
            ->get()
            ->map(function ($grupo) {
                return (object) [
                    'id_asignacion' => $grupo->id_asignacion,
                    'id_materia' => $grupo->id_materia,
                    'semestre' => $grupo->semestre,
                    'grupo' => $grupo->grupo,
                    'materia' => $grupo->materia,
                    'periodo' => $grupo->periodo,
                    'origen' => 'anterior',
                ];
            });

        // Inicializar las asignaciones del Administrador.
        $asignacionesAdministrador = collect();

        // Evitar consultar una tabla que aún no existe.
        if (Schema::hasTable('asignaciones_academicas')) {

            $asignacionesAdministrador = DB::table(
                'asignaciones_academicas as aa'
            )
                ->join(
                    'materias as m',
                    'm.id_materia',
                    '=',
                    'aa.id_materia'
                )
                ->where(
                    'aa.id_docente',
                    $docente->id_docente
                )
                ->select(
                    'aa.id_asignacion',
                    'aa.id_materia',
                    'aa.grupo',
                    'aa.periodo',
                    'm.nombre as nombre_materia',
                    'm.semestre'
                )
                ->get()
                ->map(function ($registro) {
                    return (object) [
                        'id_asignacion' => $registro->id_asignacion,
                        'id_materia' => $registro->id_materia,
                        'semestre' => $registro->semestre,
                        'grupo' => $registro->grupo,

                        'materia' => (object) [
                            'nombre' => $registro->nombre_materia,
                        ],

                        'periodo' => (object) [
                            'nombre' => $registro->periodo,
                        ],

                        'origen' => 'administrador',
                    ];
                });
        }

        // Unir ambas fuentes de información.
        $asignaciones = $asignacionesAnteriores
            ->concat($asignacionesAdministrador)
            ->sortBy(function ($registro) {
                return sprintf(
                    '%02d-%s',
                    (int) $registro->semestre,
                    $registro->grupo
                );
            })
            ->values();

        return view(
            'docente.grupos',
            compact('asignaciones')
        );
    }
}

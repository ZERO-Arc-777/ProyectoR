<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AsignacionGrupo extends Model
{
    protected $table = 'asignaciones_grupos';

    protected $primaryKey = 'id_asignacion';

    protected $fillable = [
        'id_docente',
        'id_materia',
        'semestre',
        'grupo',
        'id_periodo',
    ];

    // Relación con el docente responsable
    public function docente()
    {
        return $this->belongsTo(
            Docente::class,
            'id_docente',
            'id_docente'
        );
    }

    // Relación con la materia asignada
    public function materia()
    {
        return $this->belongsTo(
            Materia::class,
            'id_materia',
            'id_materia'
        );
    }

    // Relación con el periodo escolar
    public function periodo()
    {
        return $this->belongsTo(
            PeriodoEscolar::class,
            'id_periodo',
            'id_periodo'
        );
    }
}

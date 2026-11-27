<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HistorialGrupo extends Model
{
    protected $table = 'historial_grupos';
    protected $primaryKey = 'id_historial_grupo';

    protected $fillable = [
        'id_alumno',
        'periodo',
        'semestre',
        'grupo',
        'estado_final',
    ];

    public function alumno()
    {
        return $this->belongsTo(Alumno::class, 'id_alumno', 'id_alumno');
    }
}

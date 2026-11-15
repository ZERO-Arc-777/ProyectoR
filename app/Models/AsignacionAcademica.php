<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AsignacionAcademica extends Model
{
    protected $table = 'asignaciones_academicas';
    protected $primaryKey = 'id_asignacion';
    
    protected $fillable = [
        'id_docente', 
        'id_materia', 
        'grupo', 
        'periodo'
    ];

    public function materia()
    {
        return $this->belongsTo(Materia::class, 'id_materia', 'id_materia');
    }

    public function docente()
    {
        return $this->belongsTo(Docente::class, 'id_docente', 'id_docente');
    }
}

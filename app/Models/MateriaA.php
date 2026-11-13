<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MateriaA extends Model
{
    protected $table = 'materias';
    protected $primaryKey = 'id_materia';
    public $timestamps = false;
    protected $fillable = ['nombre', 'semestre', 'estado'];

    public function docentesAsignados()
    {
        return $this->belongsToMany(Docente::class, 'asignaciones_academicas', 'id_materia', 'id_docente')
                    ->withPivot('grupo', 'periodo')
                    ->withTimestamps();
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Docente extends Model
{
    protected $table = 'docentes';

    protected $primaryKey = 'id_docente';

    protected $fillable = [
        'id_usuario',
        'numero_empleado',
        'nombres',
        'apellido_paterno',
        'apellido_materno',
        'curp',
        'cedula_profesional',
        'estado'
    ];

    // Relación con el usuario
    public function usuario()
    {
        return $this->belongsTo(
            User::class,
            'id_usuario',
            'id_usuario'
        );
    }

    // Relación con las materias asignadas
    public function materias()
    {
        return $this->belongsToMany(
            Materia::class,
            'docente_materia',
            'id_docente',
            'id_materia'
        );
    }

    // Relación con los grupos asignados
    public function asignacionesGrupos()
    {
        return $this->hasMany(
            AsignacionGrupo::class,
            'id_docente',
            'id_docente'
        );
    }
}

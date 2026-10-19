<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Alumno extends Model
{
    protected $table = 'alumnos';
    protected $primaryKey = 'id_alumno';

    protected $fillable = [
        'id_usuario',
        'matricula',
        'nombres',
        'apellido_paterno',
        'apellido_materno',
        'curp',
        'fecha_nacimiento',
        'correo',
        'telefono',
        'semestre',
        'grupo',
        'estado'
    ];

    public function usuario()
    {
        return $this->belongsTo(User::class, 'id_usuario', 'id_usuario');
    }
}

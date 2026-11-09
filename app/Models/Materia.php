<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Materia extends Model
{
    protected $table = 'materias';

    protected $primaryKey = 'id_materia';

    public $timestamps = false;

    protected $fillable = [
        'nombre',
        'semestre',
        'estado',
    ];
    
public function docentes()
{
    return $this->belongsToMany(
        Docente::class,
        'docente_materia',
        'id_materia',
        'id_docente'
    );
}

}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Documento extends Model
{
    protected $table = 'documentos';
    protected $primaryKey = 'id_documento';

    protected $fillable = [
        'id_alumno', 'tipo_documento', 'proceso', 
        'ruta_archivo', 'estado_validacion', 'observaciones'
    ];

    public function alumno()
    {
        return $this->belongsTo(Alumno::class, 'id_alumno', 'id_alumno');
    }
}

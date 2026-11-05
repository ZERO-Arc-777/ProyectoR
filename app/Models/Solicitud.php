<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Solicitud extends Model {
    protected $table = 'solicitudes';
    protected $primaryKey = 'id_solicitud';
    protected $fillable = ['id_alumno', 'folio', 'estado', 'total_pagar'];

    public function alumno() {
        return $this->belongsTo(Alumno::class, 'id_alumno', 'id_alumno');
    }

    public function detalles() {
        return $this->hasMany(SolicitudDetalle::class, 'id_solicitud', 'id_solicitud');
    }
}

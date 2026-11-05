<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class SolicitudDetalle extends Model {
    protected $table = 'solicitud_detalles';
    protected $primaryKey = 'id_detalle';
    protected $fillable = ['id_solicitud', 'id_servicio', 'precio_cobrado'];

    public function servicio() {
        return $this->belongsTo(Servicio::class, 'id_servicio', 'id_servicio');
    }
}

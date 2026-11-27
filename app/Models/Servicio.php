<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Servicio extends Model
{
    protected $table = 'servicios';
    protected $primaryKey = 'id_servicio';
    protected $fillable = ['articulo', 'concepto', 'costo_servicio', 'derechos', 'total'];

    public function detalles()
    {
        return $this->hasMany(SolicitudDetalle::class, 'id_servicio', 'id_servicio');
    }
}

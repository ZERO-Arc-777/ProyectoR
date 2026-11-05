<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('solicitud_detalles', function (Blueprint $table) {
            $table->id('id_detalle');
            $table->unsignedBigInteger('id_solicitud');
            $table->unsignedBigInteger('id_servicio');
            $table->decimal('precio_cobrado', 8, 2); 
            $table->timestamps();

            $table->foreign('id_solicitud')->references('id_solicitud')->on('solicitudes')->onDelete('cascade');
            $table->foreign('id_servicio')->references('id_servicio')->on('servicios');
        });
    }
    public function down(): void {
        Schema::dropIfExists('solicitud_detalles');
    }
};

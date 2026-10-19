<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('reinscripciones', function (Blueprint $table) {
            $table->id('id_reinscripcion');
            $table->unsignedBigInteger('id_alumno');
            $table->unsignedBigInteger('id_periodo');
            $table->date('fecha_reinscripcion');
            $table->enum('estado', ['PENDIENTE', 'REINSCRITO', 'CANCELADO'])->default('PENDIENTE');
            
            $table->foreign('id_alumno')->references('id_alumno')->on('alumnos');
            $table->foreign('id_periodo')->references('id_periodo')->on('periodos_escolares');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reinscripciones');
    }
};

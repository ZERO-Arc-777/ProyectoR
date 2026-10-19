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
        Schema::create('historial_academico', function (Blueprint $table) {
            $table->id('id_historial');
            $table->unsignedBigInteger('id_alumno');
            $table->unsignedBigInteger('id_materia');
            $table->unsignedBigInteger('id_periodo');
            $table->decimal('calificacion', 5, 2)->nullable();
            $table->enum('estado', ['CURSANDO', 'APROBADA', 'REPROBADA'])->default('CURSANDO');
            
            $table->foreign('id_alumno')->references('id_alumno')->on('alumnos');
            $table->foreign('id_materia')->references('id_materia')->on('materias');
            $table->foreign('id_periodo')->references('id_periodo')->on('periodos_escolares');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('historial_academico');
    }
};

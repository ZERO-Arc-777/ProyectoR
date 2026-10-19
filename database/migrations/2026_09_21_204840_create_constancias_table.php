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
        Schema::create('constancias', function (Blueprint $table) {
            $table->id('id_constancia');
            $table->unsignedBigInteger('id_alumno');
            $table->string('tipo_constancia', 100)->nullable();
            $table->date('fecha_solicitud');
            $table->enum('estado', ['SOLICITADA', 'GENERADA', 'ENTREGADA'])->default('SOLICITADA');
            
            $table->foreign('id_alumno')->references('id_alumno')->on('alumnos');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('constancias');
    }
};

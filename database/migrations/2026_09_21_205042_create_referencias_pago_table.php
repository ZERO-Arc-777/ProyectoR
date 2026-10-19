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
        Schema::create('referencias_pago', function (Blueprint $table) {
            $table->id('id_referencia');
            $table->unsignedBigInteger('id_alumno');
            $table->string('concepto', 150);
            $table->decimal('monto', 10, 2)->nullable();
            $table->string('referencia', 100)->unique()->nullable();
            $table->date('fecha_generacion');
            $table->enum('estado', ['GENERADA', 'PAGADA', 'CANCELADA'])->default('GENERADA');
            
            $table->foreign('id_alumno')->references('id_alumno')->on('alumnos');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('referencias_pago');
    }
};

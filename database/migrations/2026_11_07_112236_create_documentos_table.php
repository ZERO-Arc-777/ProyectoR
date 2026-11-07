<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('documentos', function (Blueprint $table) {
            $table->id('id_documento');
            $table->unsignedBigInteger('id_alumno');
            
            $table->string('tipo_documento', 100); 
            $table->enum('proceso', ['INSCRIPCION', 'REINSCRIPCION', 'EGRESO']);
            
            $table->string('ruta_archivo');
            $table->enum('estado_validacion', ['PENDIENTE', 'APROBADO', 'RECHAZADO'])->default('PENDIENTE');
            $table->text('observaciones')->nullable();
            
            $table->timestamps();

            $table->foreign('id_alumno')->references('id_alumno')->on('alumnos')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('documentos');
    }
};

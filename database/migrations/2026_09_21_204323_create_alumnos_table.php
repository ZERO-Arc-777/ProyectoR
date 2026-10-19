<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('alumnos', function (Blueprint $table) {
            $table->id('id_alumno');
            $table->unsignedBigInteger('id_usuario')->nullable()->unique();
            
            $table->string('matricula', 30)->unique();
            $table->string('nombres', 100);
            $table->string('apellido_paterno', 100);
            $table->string('apellido_materno', 100)->nullable();
            $table->string('curp', 18)->unique();
            $table->date('fecha_nacimiento')->nullable();
            $table->string('correo', 100)->nullable();
            $table->string('telefono', 20)->nullable();

            $table->unsignedTinyInteger('semestre');
            $table->char('grupo', 1);
            $table->enum('estado', ['ACTIVO', 'BAJA', 'EGRESADO'])->default('ACTIVO');
            $table->timestamps();
            $table->foreign('id_usuario')->references('id_usuario')->on('usuarios')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('alumnos');
    }
};

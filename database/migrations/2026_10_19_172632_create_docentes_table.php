<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('docentes', function (Blueprint $table) {
            $table->id('id_docente');
            $table->unsignedBigInteger('id_usuario')->nullable()->unique();
            
            $table->string('numero_empleado', 20)->unique();
            $table->string('nombres', 100);
            $table->string('apellido_paterno', 100);
            $table->string('apellido_materno', 100)->nullable();
            $table->string('curp', 18)->unique();
            $table->string('cedula_profesional', 50)->nullable();
            $table->enum('estado', ['ACTIVO', 'INACTIVO', 'BAJA'])->default('ACTIVO');
            
            $table->timestamps();
            $table->foreign('id_usuario')->references('id_usuario')->on('usuarios')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('docentes');
    }
};

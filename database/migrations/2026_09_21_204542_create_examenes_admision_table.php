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
        Schema::create('examenes_admision', function (Blueprint $table) {
            $table->id('id_examen');
            $table->unsignedBigInteger('id_aspirante');
            $table->date('fecha_examen')->nullable();
            $table->decimal('calificacion', 5, 2)->nullable();
            $table->enum('resultado', ['APROBADO', 'NO_APROBADO', 'PENDIENTE'])->default('PENDIENTE');
            
            $table->foreign('id_aspirante')->references('id_aspirante')->on('aspirantes');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('examenes_admision');
    }
};

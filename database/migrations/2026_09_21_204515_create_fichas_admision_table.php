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
        Schema::create('fichas_admision', function (Blueprint $table) {
            $table->id('id_ficha');
            $table->unsignedBigInteger('id_aspirante');
            $table->string('folio', 50)->unique();
            $table->date('fecha_generacion');
            $table->enum('estado', ['GENERADA', 'VALIDADA', 'CANCELADA'])->default('GENERADA');
            
            $table->foreign('id_aspirante')->references('id_aspirante')->on('aspirantes');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fichas_admision');
    }
};

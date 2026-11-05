<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('servicios', function (Blueprint $table) {
            $table->id('id_servicio');
            $table->string('articulo', 50)->nullable();
            $table->string('concepto', 255);
            $table->decimal('costo_servicio', 8, 2);
            $table->decimal('derechos', 8, 2);
            $table->decimal('total', 8, 2);
            $table->timestamps();
        });
    }
    public function down(): void {
        Schema::dropIfExists('servicios');
    }
};


<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('docente_materia', function (Blueprint $table) {

            $table->unsignedBigInteger('id_docente');
            $table->unsignedBigInteger('id_materia');

            // Relación con la tabla docentes
            $table->foreign('id_docente')
                ->references('id_docente')
                ->on('docentes')
                ->onDelete('cascade');

            // Relación con la tabla materias
            $table->foreign('id_materia')
                ->references('id_materia')
                ->on('materias')
                ->onDelete('cascade');

            // Evitar asignar dos veces la misma materia
            // al mismo docente
            $table->primary(['id_docente', 'id_materia']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('docente_materia');
    }
};

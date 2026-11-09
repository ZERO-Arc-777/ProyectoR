
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('asignaciones_grupos', function (Blueprint $table) {

            $table->id('id_asignacion');

            // Docente asignado
            $table->unsignedBigInteger('id_docente');

            // Materia que imparte
            $table->unsignedBigInteger('id_materia');

            // Semestre y grupo
            $table->unsignedTinyInteger('semestre');
            $table->char('grupo', 1);

            // Periodo escolar
            $table->unsignedBigInteger('id_periodo');

            $table->timestamps();

            // Relación con docentes
            $table->foreign('id_docente')
                ->references('id_docente')
                ->on('docentes')
                ->cascadeOnDelete();

            // Relación con materias
            $table->foreign('id_materia')
                ->references('id_materia')
                ->on('materias')
                ->cascadeOnDelete();

            // Relación con periodos escolares
            $table->foreign('id_periodo')
                ->references('id_periodo')
                ->on('periodos_escolares')
                ->restrictOnDelete();

            // Evitar asignaciones duplicadas
            $table->unique(
                [
                    'id_docente',
                    'id_materia',
                    'semestre',
                    'grupo',
                    'id_periodo'
                ],
                'asignacion_grupo_unica'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('asignaciones_grupos');
    }
};

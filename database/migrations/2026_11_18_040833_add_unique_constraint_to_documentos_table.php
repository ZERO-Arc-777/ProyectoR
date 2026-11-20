
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Agregar protección contra documentos duplicados.
     */
    public function up(): void
    {
        Schema::table('documentos', function (Blueprint $table) {
            $table->unique(
                ['id_alumno', 'tipo_documento', 'proceso'],
                'documentos_alumno_tipo_proceso_unique'
            );
        });
    }

    /**
     * Revertir únicamente la restricción agregada.
     */
    public function down(): void
    {
        Schema::table('documentos', function (Blueprint $table) {
            $table->dropUnique(
                'documentos_alumno_tipo_proceso_unique'
            );
        });
    }
};

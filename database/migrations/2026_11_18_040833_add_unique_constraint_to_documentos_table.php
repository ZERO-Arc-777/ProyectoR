<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Agregar columna periodo y protección contra documentos duplicados por periodo.
     */
    public function up(): void
    {
        // 1. Agregar la columna periodo si aún no existe.
        if (!Schema::hasColumn('documentos', 'periodo')) {
            Schema::table('documentos', function (Blueprint $table) {
                $table->string('periodo', 255)
                    ->default('No definido')
                    ->after('proceso');
            });
        }

        // 2. Asignar el periodo actual a los documentos ya registrados.
        $periodoActual = 'No definido';

        if (Schema::hasTable('configuraciones')) {
            $valor = trim((string) (DB::table('configuraciones')
                ->where('clave', 'periodo_actual')
                ->value('valor') ?? ''));

            if ($valor !== '') {
                $periodoActual = $valor;
            }
        }

        DB::table('documentos')
            ->where(function ($query) {
                $query->whereNull('periodo')
                    ->orWhere('periodo', '')
                    ->orWhere('periodo', 'No definido');
            })
            ->update(['periodo' => $periodoActual]);

        // 3. Limpiar duplicados previos (conservando el registro más reciente)
        // para evitar errores al aplicar el índice único.
        $duplicados = DB::table('documentos')
            ->select(
                'id_alumno',
                'tipo_documento',
                'proceso',
                'periodo',
                DB::raw('MAX(id_documento) as ultimo_id')
            )
            ->groupBy('id_alumno', 'tipo_documento', 'proceso', 'periodo')
            ->havingRaw('COUNT(*) > 1')
            ->get();

        foreach ($duplicados as $grupo) {
            DB::table('documentos')
                ->where('id_alumno', $grupo->id_alumno)
                ->where('tipo_documento', $grupo->tipo_documento)
                ->where('proceso', $grupo->proceso)
                ->where('periodo', $grupo->periodo)
                ->where('id_documento', '!=', $grupo->ultimo_id)
                ->delete();
        }

        // 4. Crear la restricción única incluyendo el periodo escolar.
        Schema::table('documentos', function (Blueprint $table) {
            $table->unique(
                ['id_alumno', 'tipo_documento', 'proceso', 'periodo'],
                'documentos_alumno_tipo_proceso_periodo_unique'
            );
        });
    }

    /**
     * Revertir la restricción y la columna periodo.
     */
    public function down(): void
    {
        Schema::table('documentos', function (Blueprint $table) {
            $table->dropUnique(
                'documentos_alumno_tipo_proceso_periodo_unique'
            );
        });

        if (Schema::hasColumn('documentos', 'periodo')) {
            Schema::table('documentos', function (Blueprint $table) {
                $table->dropColumn('periodo');
            });
        }
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Ampliar calificaciones: ligar a la asignación exacta y asegurar parciales + eval_final
        Schema::table('calificaciones', function (Blueprint $table) {
            if (!Schema::hasColumn('calificaciones', 'id_asignacion')) {
                $table->unsignedBigInteger('id_asignacion')
                    ->nullable()
                    ->after('id_materia');

                $table->foreign('id_asignacion', 'calificaciones_id_asignacion_foreign')
                    ->references('id_asignacion')
                    ->on('asignaciones_academicas')
                    ->nullOnDelete();
            }

            if (!Schema::hasColumn('calificaciones', 'calif_1')) {
                $table->unsignedTinyInteger('calif_1')->nullable()->after('periodo');
            }
            if (!Schema::hasColumn('calificaciones', 'calif_2')) {
                $table->unsignedTinyInteger('calif_2')->nullable()->after('calif_1');
            }
            if (!Schema::hasColumn('calificaciones', 'calif_3')) {
                $table->unsignedTinyInteger('calif_3')->nullable()->after('calif_2');
            }
            if (!Schema::hasColumn('calificaciones', 'eval_final')) {
                $table->unsignedTinyInteger('eval_final')->nullable()->after('calif_3');
            }
        });

        // 2. Blindar que dos docentes no puedan impartir la misma materia en el mismo grupo y periodo
        Schema::table('asignaciones_academicas', function (Blueprint $table) {
            $table->unique(
                ['id_materia', 'grupo', 'periodo'],
                'asignaciones_materia_grupo_periodo_unique'
            );
        });

        // 3. Crear foto histórica de alumnos por periodo (para historial de Mis Grupos y auditoría)
        if (!Schema::hasTable('historial_grupos')) {
            Schema::create('historial_grupos', function (Blueprint $table) {
                $table->id('id_historial_grupo');
                $table->unsignedBigInteger('id_alumno');
                $table->string('periodo', 50);
                $table->unsignedTinyInteger('semestre');
                $table->char('grupo', 1);
                $table->enum('estado_final', ['PROMOVIDO', 'EGRESADO', 'BAJA']);
                $table->timestamps();

                $table->foreign('id_alumno', 'historial_grupos_id_alumno_foreign')
                    ->references('id_alumno')
                    ->on('alumnos')
                    ->cascadeOnDelete();

                $table->unique(
                    ['id_alumno', 'periodo'],
                    'historial_grupos_alumno_periodo_unique'
                );
            });
        }

        // 4. Registrar el estado del ciclo escolar en configuraciones si no existe
        $existeEstado = DB::table('configuraciones')
            ->where('clave', 'periodo_estado')
            ->exists();

        if (!$existeEstado) {
            $tienePeriodo = DB::table('configuraciones')
                ->where('clave', 'periodo_actual')
                ->whereNotNull('valor')
                ->where('valor', '!=', '')
                ->exists();

            DB::table('configuraciones')->insert([
                'clave'      => 'periodo_estado',
                'valor'      => $tienePeriodo ? 'ABIERTO' : 'CERRADO',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        DB::table('configuraciones')
            ->where('clave', 'periodo_estado')
            ->delete();

        Schema::dropIfExists('historial_grupos');

        Schema::table('asignaciones_academicas', function (Blueprint $table) {
            $table->dropUnique('asignaciones_materia_grupo_periodo_unique');
        });

        Schema::table('calificaciones', function (Blueprint $table) {
            if (Schema::hasColumn('calificaciones', 'id_asignacion')) {
                $table->dropForeign('calificaciones_id_asignacion_foreign');
                $table->dropColumn('id_asignacion');
            }

            foreach (['eval_final', 'calif_3', 'calif_2', 'calif_1'] as $columna) {
                if (Schema::hasColumn('calificaciones', $columna)) {
                    $table->dropColumn($columna);
                }
            }
        });
    }
};

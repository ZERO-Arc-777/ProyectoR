<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Calificacion extends Model
{
    protected $table = 'calificaciones';
    protected $primaryKey = 'id_calificacion';

    protected $fillable = [
        'id_alumno',
        'id_materia',
        'id_asignacion',
        'periodo',
        'calif_1',
        'calif_2',
        'calif_3',
        'eval_final',
        'calificacion',
    ];

    public function alumno()
    {
        return $this->belongsTo(Alumno::class, 'id_alumno', 'id_alumno');
    }

    public function materia()
    {
        return $this->belongsTo(Materia::class, 'id_materia', 'id_materia');
    }

    public function asignacion()
    {
        return $this->belongsTo(AsignacionAcademica::class, 'id_asignacion', 'id_asignacion');
    }

    /**
     * Determina si el alumno debe presentar Evaluación Final:
     * - 2 o más parciales reprobados (< 6), o
     * - 3 parciales capturados con suma menor a 18 puntos.
     */
    public static function requiereEvaluacionFinal(?int $p1, ?int $p2, ?int $p3): bool
    {
        $parciales = array_values(array_filter(
            [$p1, $p2, $p3],
            static fn ($v) => $v !== null
        ));

        $reprobados = count(array_filter(
            $parciales,
            static fn ($v) => (int) $v < 6
        ));

        if ($reprobados >= 2) {
            return true;
        }

        if (count($parciales) === 3 && array_sum($parciales) < 18) {
            return true;
        }

        return false;
    }

    /**
     * Aplica la regla de redondeo institucional:
     * conserva 2 decimales si el segundo decimal es < 5;
     * redondea a 1 decimal hacia arriba si el segundo decimal es >= 5 (ej. 8.65 -> 8.7).
     */
    public static function aplicarReglaRedondeo(float $valor): float
    {
        $truncadoCent = (int) floor(($valor * 100) + 1e-9);
        $segundoDecimal = $truncadoCent % 10;

        if ($segundoDecimal >= 5) {
            return (floor($truncadoCent / 10) + 1) / 10;
        }

        return $truncadoCent / 100;
    }

    /**
     * Calcula el Promedio Final a guardar en la columna `calificacion`.
     */
    public static function calcularPromedioFinal(?int $p1, ?int $p2, ?int $p3, ?int $evalFinal): ?float
    {
        if (self::requiereEvaluacionFinal($p1, $p2,$p3)) {
            return $evalFinal !== null ? (float) $evalFinal : null;
        }

        $parciales = array_values(array_filter(
            [$p1, $p2,$p3],
            static fn ($v) =>$v !== null
        ));

        if (count($parciales) === 0) {
            return null;
        }

        return self::aplicarReglaRedondeo(array_sum($parciales) / count($parciales));
    }

    /**
     * Formatea el promedio para pantalla y PDF sin ceros innecesarios (8, 8.7, 8.33).
     */
    public static function formatearPromedio(?float $valor): string
    {
        if ($valor === null) {
            return '---';
        }

        $ajustado = self::aplicarReglaRedondeo($valor);

        return rtrim(rtrim(number_format($ajustado, 2, '.', ''), '0'), '.');
    }
}

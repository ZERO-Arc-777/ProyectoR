<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Servicio;

class ServicioSeeder extends Seeder
{
    public function run(): void
    {
        $servicios = [
            ['articulo' => 'ART. 17 FRACC. VII', 'concepto' => 'POR LA LEGALIZACION DE FIRMAS DE DOCUMENTOS OFICIALES, CON FIRMAS DE FUNCIONARIOS PUBLICOS', 'costo_servicio' => 231.00, 'derechos' => 14.00, 'total' => 245.00],
            ['articulo' => 'ART. 39 FRACC. VI a)', 'concepto' => 'POR AUTORIZACION DE REGISTRO DE INSCRIPCIÓN Y REINSCRIPCIÓN EN LOS NIVELES DE: MEDIO SUPERIOR POR CADA SEMESTRE O POR GRADO, POR ALUMNO', 'costo_servicio' => 21.00, 'derechos' => 1.00, 'total' => 22.00],
            ['articulo' => 'ART. 39 FRACC. XIV', 'concepto' => 'POR LA BUSQUEDA, COTEJO, CERTIFICACION Y EMISION DE CONSTANCIAS DE DOCUMENTOS DE ARCHIVO', 'costo_servicio' => 200.00, 'derechos' => 12.00, 'total' => 212.00],
            ['articulo' => 'ART. 39 FRACC. XV b)', 'concepto' => 'EXPEDICION Y LEGALIZACION DE CERTIFICADOS DE ESTUDIOS DE NIVEL MEDIO SUPERIOR', 'costo_servicio' => 95.00, 'derechos' => 6.00, 'total' => 101.00],
            ['articulo' => 'ART. 39 FRACC. XV e)', 'concepto' => 'EXAMEN DE REGULARIZACION EN LOS NIVELES BASICO, MEDIO SUPERIOR Y SUPERIOR', 'costo_servicio' => 95.00, 'derechos' => 6.00, 'total' => 101.00],
            ['articulo' => 'ART. 39 FRACC. XVI b)', 'concepto' => 'EXPEDICION DE REVALIDACION DE ESTUDIOS: TIPO MEDIO SUPERIOR', 'costo_servicio' => 399.00, 'derechos' => 24.00, 'total' => 423.00],
            ['articulo' => 'ART. 39 FRACC. XVII a)', 'concepto' => 'EXPEDICION DE EQUIVALENCIA DE ESTUDIOS: TIPO MEDIO SUPERIOR', 'costo_servicio' => 399.00, 'derechos' => 24.00, 'total' => 423.00],
            ['articulo' => 'ART. 39 FRACC. XXI b)', 'concepto' => 'CERTIFICACION O LEGALIZACION DE ACTAS DE EXAMEN PROFESIONAL SUPERIOR', 'costo_servicio' => 231.00, 'derechos' => 14.00, 'total' => 245.00],
            ['articulo' => 'ART. 39 FRACC. XXII', 'concepto' => 'POR LA REPOSICION DE LA RESOLUCION DE EQUIVALENCIA Y/O REVALIDACION DE ESTUDIOS', 'costo_servicio' => 200.00, 'derechos' => 12.00, 'total' => 212.00],
            ['articulo' => 'ART. 40 FRACC. II', 'concepto' => 'POR LA EXPEDICIÓN DE DUPLICADOS DE CERTIFICADOS DE ESTUDIOS TIPO BÁSICO, MEDIO SUPERIOR Y FORMACION PARA EL TRABAJO', 'costo_servicio' => 63.00, 'derechos' => 4.00, 'total' => 67.00],
            ['articulo' => 'ART. 40 FRACC. III b)', 'concepto' => 'POR LA EXPEDICIÓN DE: LEGALIZACIÓN DE FIRMAS EN TÍTULOS ELECTRONICOS PROFESIONALES O TÍTULOS ELECTRONICOS DE GRADO', 'costo_servicio' => 630.00, 'derechos' => 38.00, 'total' => 668.00],
            ['articulo' => 'ART. 40 FRACC. III c)', 'concepto' => 'POR LA EXPEDICIÓN DE ACTAS DE EXAMEN PROFESIONAL TIPO MEDIO SUPERIOR Y SUPERIOR', 'costo_servicio' => 399.00, 'derechos' => 24.00, 'total' => 423.00],
            ['articulo' => 'ART. 40 FRACC. V', 'concepto' => 'POR LA EXPEDICIÓN Y REGISTRO DE TÍTULOS PROFESIONALES ELECTRONICOS NIVEL SUPERIOR OFICIALES, CERTIFICACION DE TITULO PROFESIONAL, NIVEL MEDIO SUPERIOR Y SUPERIOR', 'costo_servicio' => 231.00, 'derechos' => 14.00, 'total' => 245.00],
            ['articulo' => 'ART. 40 FRACC. X', 'concepto' => 'POR LA VALIDACION DE CONSTANCIAS DE SERVICIO SOCIAL', 'costo_servicio' => 158.00, 'derechos' => 9.00, 'total' => 167.00],
        ];

        foreach ($servicios as $servicio) {
            Servicio::create($servicio);
        }
    }
}

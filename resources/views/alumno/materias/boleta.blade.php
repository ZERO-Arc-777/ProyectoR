<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Boleta de Calificaciones - {{ $alumno->matricula }}</title>
    <style>
        @page {
            margin: 1.5cm 1.6cm;
        }

        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 9.5pt;
            color: #111111;
            line-height: 1.35;
            background-color: #ffffff;
        }

        .text-center { text-align: center; }
        .text-left   { text-align: left; }
        .text-right  { text-align: right; }
        .bold        { font-weight: bold; }
        .uppercase   { text-transform: uppercase; }

        /* TÍTULO PRINCIPAL */
        .title-box {
            text-align: center;
            margin-bottom: 14px;
        }

        .title-main {
            font-size: 24pt;
            font-weight: bold;
            letter-spacing: 1.5px;
            line-height: 1.08;
            color: #111111;
            text-transform: uppercase;
            margin: 0;
        }

        .title-sub {
            font-size: 8.5pt;
            letter-spacing: 2px;
            color: #555555;
            text-transform: uppercase;
            margin-top: 6px;
        }

        /* SEPARADORES PUNTEADOS */
        .dotted-divider {
            border: 0;
            border-top: 1.5px dotted #222222;
            margin: 14px 0 18px 0;
        }

        /* DATOS DEL ALUMNO (2 COLUMNAS, 3 FILAS) */
        .student-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 22px;
        }

        .student-table td {
            padding: 6px 0;
            vertical-align: bottom;
        }

        .student-field {
            border-bottom: 1.2px solid #222222;
            padding-bottom: 4px;
            font-size: 9.2pt;
        }

        .student-label {
            font-weight: bold;
            font-size: 8.2pt;
            letter-spacing: 0.4px;
            color: #111111;
            text-transform: uppercase;
            margin-right: 6px;
        }

        .student-value {
            color: #222222;
        }

        .col-spacer {
            width: 4%;
        }

        /* TABLA PRINCIPAL DE CALIFICACIONES */
        .grades-table {
            width: 100%;
            border-collapse: collapse;
            border: 1.5px solid #111111;
            margin-bottom: 20px;
        }

        .grades-table th {
            color: #ffffff;
            font-size: 8.5pt;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            padding: 10px 6px;
            border: 1px solid #111111;
            text-align: center;
        }

        /* Encabezados alternados en escala de grises */
        .th-dark {
            background-color: #1a1a1a;
        }

        .th-mid {
            background-color: #4f4f4f;
        }

        .grades-table td {
            padding: 10px 8px;
            border: 1px solid #333333;
            font-size: 9.5pt;
            color: #111111;
            vertical-align: middle;
        }

        .materia-cell {
            text-align: center;
            font-weight: normal;
            color: #111111;
        }

        .nota-cell {
            text-align: center;
            font-weight: bold;
            color: #111111;
        }

        .nota-vacia {
            color: #777777;
            font-weight: normal;
        }

        .promedio-cell {
            text-align: center;
            font-weight: bold;
            background-color: #f3f3f3;
            color: #000000;
        }

        /* PIE DISCRETO */
        .footer-note {
            margin-top: 14px;
            text-align: center;
            font-size: 7.8pt;
            color: #555555;
        }
    </style>
</head>
<body>

    {{-- ENCABEZADO --}}
    <div class="title-box">
        <div class="title-main">BOLETA DE<br>CALIFICACIONES</div>
        <div class="title-sub">SGAE &middot; Escuela Preparatoria N&uacute;mero 5</div>
    </div>

    <hr class="dotted-divider">

    {{-- DATOS GENERALES --}}
    <table class="student-table">
        <tr>
            <td style="width: 48%;">
                <div class="student-field">
                    <span class="student-label">Nombre:</span>
                    <span class="student-value">
                        {{ $alumno->nombres }} {{ $alumno->apellido_paterno }} {{ $alumno->apellido_materno }}
                    </span>
                </div>
            </td>
            <td class="col-spacer"></td>
            <td style="width: 48%;">
                <div class="student-field">
                    <span class="student-label">Matr&iacute;cula:</span>
                    <span class="student-value">{{ $alumno->matricula }}</span>
                </div>
            </td>
        </tr>
        @if($esPeriodoActual)
        <tr>
            <td>
                <div class="student-field">
                    <span class="student-label">Grado:</span>
                    <span class="student-value">{{ $semestreTexto }}</span>
                </div>
            </td>
            <td class="col-spacer"></td>
            <td>
                <div class="student-field">
                    <span class="student-label">Grupo:</span>
                    <span class="student-value">{{ $alumno->grupo }}</span>
                </div>
            </td>
        </tr>
        @endif
        <tr>
            <td>
                <div class="student-field">
                    <span class="student-label">Periodo:</span>
                    <span class="student-value">{{ $periodo }}</span>
                </div>
            </td>
            <td class="col-spacer"></td>
            <td>
                <div class="student-field">
                    <span class="student-label">Fecha de emisi&oacute;n:</span>
                    <span class="student-value">{{ $fechaEmisionTexto }}</span>
                </div>
            </td>
        </tr>
    </table>

    {{-- TABLA DE CALIFICACIONES --}}
    <table class="grades-table">
        <thead>
            <tr>
                <th class="th-dark" style="width: 35%;">Materia</th>
                <th class="th-mid"  style="width: 13%;">Parcial 1</th>
                <th class="th-dark" style="width: 13%;">Parcial 2</th>
                <th class="th-mid"  style="width: 13%;">Parcial 3</th>
                <th class="th-dark" style="width: 13%;">Eval. Final</th>
                <th class="th-mid"  style="width: 13%;">Promedio</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($filas as $fila)
                <tr>
                    <td class="materia-cell">{{ $fila['materia'] }}</td>
                    <td class="nota-cell {{ $fila['calif_1'] === '---' ? 'nota-vacia' : '' }}">
                        {{ $fila['calif_1'] }}
                    </td>
                    <td class="nota-cell {{ $fila['calif_2'] === '---' ? 'nota-vacia' : '' }}">
                        {{ $fila['calif_2'] }}
                    </td>
                    <td class="nota-cell {{ $fila['calif_3'] === '---' ? 'nota-vacia' : '' }}">
                        {{ $fila['calif_3'] }}
                    </td>
                    <td class="nota-cell {{ $fila['eval_final'] === '---' ? 'nota-vacia' : '' }}">
                        {{ $fila['eval_final'] }}
                    </td>
                    <td class="promedio-cell {{ $fila['promedio_semestral'] === '---' ? 'nota-vacia' : '' }}">
                        {{ $fila['promedio_semestral'] }}
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="text-center nota-vacia" style="padding: 18px;">
                        Sin materias registradas para este periodo.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <hr class="dotted-divider">

    <div class="footer-note">
        Documento informativo generado por SGAE Prepa 5
    </div>

</body>
</html>
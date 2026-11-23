<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Control de Asistencia - {{ $asignacion->materia?->nombre ?? 'Grupo' }}</title>
    <style>
        @page {
            margin: 1.2cm 1.3cm;
        }

        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 8.5pt;
            color: #111111;
            line-height: 1.25;
            background-color: #ffffff;
        }

        /* ENCABEZADO PRINCIPAL */
        .header-wrap {
            text-align: center;
            border-bottom: 2px solid #111111;
            padding-bottom: 8px;
            margin-bottom: 12px;
        }

        .header-title {
            font-size: 16pt;
            font-weight: bold;
            letter-spacing: 2px;
            text-transform: uppercase;
            margin: 0;
            color: #111111;
        }

        .header-subtitle {
            font-size: 7.8pt;
            letter-spacing: 1.2px;
            text-transform: uppercase;
            color: #444444;
            margin-top: 3px;
        }

        /* DATOS GENERALES DEL GRUPO */
        .meta-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 12px;
        }

        .meta-table td {
            padding: 4px 0;
            vertical-align: bottom;
        }

        .meta-field {
            border-bottom: 1px solid #222222;
            padding-bottom: 3px;
            font-size: 8.5pt;
        }

        .meta-label {
            font-weight: bold;
            text-transform: uppercase;
            font-size: 7.8pt;
            color: #111111;
            margin-right: 5px;
        }

        .meta-value {
            color: #222222;
        }

        .meta-spacer {
            width: 4%;
        }

        /* TABLA DE CONTROL DE ASISTENCIA */
        .attendance-table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
            border: 1.4px solid #111111;
        }

        .attendance-table thead {
            display: table-header-group;
        }

        .attendance-table th {
            background-color: #1a1a1a;
            color: #ffffff;
            border: 1px solid #111111;
            font-size: 7.5pt;
            font-weight: bold;
            text-transform: uppercase;
            text-align: center;
            padding: 5px 3px;
        }

        .attendance-table .date-subhead th {
            background-color: #f3f3f3;
            color: #333333;
            height: 16px;
            padding: 2px 1px;
            font-size: 6.5pt;
            font-weight: normal;
        }

        .attendance-table td {
            border: 1px solid #333333;
            height: 19px;
            padding: 2px 4px;
            font-size: 8pt;
            vertical-align: middle;
        }

        .col-num {
            width: 4.5%;
            text-align: center;
            font-weight: bold;
            color: #222222;
        }

        .col-matricula {
            width: 11.5%;
            text-align: center;
            color: #111111;
        }

        .col-nombre {
            width: 34%;
            text-align: left;
            color: #111111;
            overflow: hidden;
            white-space: nowrap;
        }

        .col-dia {
            width: 4.16%;
            text-align: center;
        }

        /* PIE DE PÁGINA */
        .footer-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 14px;
            font-size: 7.5pt;
            color: #444444;
        }

        .firma-linea {
            border-top: 1px solid #111111;
            width: 180px;
            margin-left: auto;
            padding-top: 3px;
            text-align: center;
            color: #111111;
            font-weight: bold;
        }
    </style>
</head>
<body>

@php
    $totalFilas = max(26, $alumnos->count());
    $columnasAsistencia = 12;
@endphp

    {{-- ENCABEZADO --}}
    <div class="header-wrap">
        <div class="header-title">Control de Asistencia</div>
        <div class="header-subtitle">SGAE &middot; Escuela Preparatoria N&uacute;mero 5 del Estado</div>
    </div>

    {{-- DATOS DEL DOCENTE Y GRUPO --}}
    <table class="meta-table">
        <tr>
            <td style="width: 52%;">
                <div class="meta-field">
                    <span class="meta-label">Docente:</span>
                    <span class="meta-value">{{ $nombreDocente ?: 'No registrado' }}</span>
                </div>
            </td>
            <td class="meta-spacer"></td>
            <td style="width: 44%;">
                <div class="meta-field">
                    <span class="meta-label">Periodo:</span>
                    <span class="meta-value">{{ $asignacion->periodo ?? 'No registrado' }}</span>
                </div>
            </td>
        </tr>
        <tr>
            <td>
                <div class="meta-field">
                    <span class="meta-label">Asignatura:</span>
                    <span class="meta-value">{{ $asignacion->materia?->nombre ?? 'No registrada' }}</span>
                </div>
            </td>
            <td class="meta-spacer"></td>
            <td>
                <div class="meta-field">
                    <span class="meta-label">Grado y Grupo:</span>
                    <span class="meta-value">{{ $gradoGrupo }}</span>
                </div>
            </td>
        </tr>
    </table>

    {{-- TABLA DE ASISTENCIA --}}
    <table class="attendance-table">
        <thead>
            <tr>
                <th rowspan="2" class="col-num">No.</th>
                <th rowspan="2" class="col-matricula">Matr&iacute;cula</th>
                <th rowspan="2" class="col-nombre" style="text-align: left; padding-left: 6px;">Nombre ALUMNO/A</th>
                <th colspan="{{ $columnasAsistencia }}">Fechas / Asistencia</th>
            </tr>
            <tr class="date-subhead">
                @for ($d = 1; $d <= $columnasAsistencia; $d++)
                    <th class="col-dia">&nbsp;</th>
                @endfor
            </tr>
        </thead>
        <tbody>
            @for ($i = 0; $i < $totalFilas; $i++)
                @php
                    $alumno = $alumnos->get($i);
                    $nombreCompleto = $alumno
                        ? trim(implode(' ', array_filter([
                            $alumno->apellido_paterno,
                            $alumno->apellido_materno,
                            $alumno->nombres,
                        ])))
                        : '';
                @endphp
                <tr>
                    <td class="col-num">{{ $i + 1 }}.</td>
                    <td class="col-matricula">{{ $alumno?->matricula ?? '' }}</td>
                    <td class="col-nombre">{{ $nombreCompleto }}</td>
                    @for ($d = 1; $d <= $columnasAsistencia; $d++)
                        <td class="col-dia"></td>
                    @endfor
                </tr>
            @endfor
        </tbody>
    </table>

    {{-- PIE Y FIRMA --}}
    <table class="footer-table">
        <tr>
            <td style="width: 45%; vertical-align: bottom; text-align: right; padding-top: 14px;">
                <div class="firma-linea">Firma del Docente</div>
            </td>
        </tr>
    </table>

</body>
</html>
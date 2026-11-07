<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>{{ $titulo }}</title>
</head>
<body>

    <h1>{{ $titulo }}</h1>
    <a href="{{ route('admin.dashboard') }}">Volver al Dashboard</a>
    <hr>

    @if(session('success'))
        <div style="color: green; margin-bottom: 15px;">
            <b>Éxito:</b> {{ session('success') }}
        </div>
    @endif

    <table border="1" cellpadding="8" cellspacing="0" width="100%">
        <thead>
            <tr bgcolor="#f2f2f2">
                <th>Alumno (Matrícula)</th>
                <th>Tipo de Documento</th>
                <th>Archivo</th>
                <th>Estado Actual</th>
                <th>Acción (Validar)</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($documentos as $doc)
                <tr>
                    <td>
                        {{ $doc->alumno->apellido_paterno }} {{ $doc->alumno->apellido_materno }} {{ $doc->alumno->nombres }} 
                        <br>
                        <small><i>Matrícula: {{ $doc->alumno->matricula }}</i></small>
                    </td>
                    <td align="center"><b>{{ $doc->tipo_documento }}</b></td>
                    <td align="center">
                        <a href="{{ asset('storage/' . $doc->ruta_archivo) }}" target="_blank">
                            <button>📄 Ver PDF</button>
                        </a>
                    </td>
                    
                    <td align="center">
                        @if($doc->estado_validacion == 'PENDIENTE')
                            <span style="color: orange; font-weight: bold;">PENDIENTE</span>
                        @elseif($doc->estado_validacion == 'APROBADO')
                            <span style="color: green; font-weight: bold;">APROBADO</span>
                        @else
                            <span style="color: red; font-weight: bold;">RECHAZADO</span>
                            <br><small><i>{{ $doc->observaciones }}</i></small>
                        @endif
                    </td>

                    <td align="center">
                        <form action="{{ route('admin.documentos.estado', $doc->id_documento) }}" method="POST" style="margin: 0;">
                            @csrf
                            @method('PUT')
                            <select name="estado_validacion" required style="width: 100%; margin-bottom: 5px;">
                                <option value="PENDIENTE" {{ $doc->estado_validacion == 'PENDIENTE' ? 'selected' : '' }}>Pendiente</option>
                                <option value="APROBADO" {{ $doc->estado_validacion == 'APROBADO' ? 'selected' : '' }}>Aprobar</option>
                                <option value="RECHAZADO" {{ $doc->estado_validacion == 'RECHAZADO' ? 'selected' : '' }}>Rechazar</option>
                            </select>
                            <br>
                            <input type="text" name="observaciones" placeholder="Motivo de rechazo (opcional)" value="{{ $doc->observaciones }}" style="width: 100%; margin-bottom: 5px;">
                            <br>
                            <button type="submit">Actualizar</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" align="center">No hay documentos pendientes de revisión en esta bandeja.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

</body>
</html>
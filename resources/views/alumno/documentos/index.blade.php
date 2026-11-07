<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Mi Expediente Digital</title>
</head>
<body>

    <h1>Mi Expediente Digital</h1>
    <a href="{{ route('alumno.dashboard') }}">Volver al Dashboard</a>
    <hr>

    @if(session('success'))
        <div style="color: green; margin-bottom: 15px;">
            <b>Éxito:</b> {{ session('success') }}
        </div>
    @endif

    @if ($errors->any())
        <div style="color: red; margin-bottom: 15px;">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <h3>Subir Nuevo Documento</h3>
    <!-- El atributo enctype es vital para enviar archivos (PDFs) al servidor -->
    <form action="{{ route('alumno.documentos.store') }}" method="POST" enctype="multipart/form-data" style="background-color: #f9f9f9; padding: 15px; border: 1px solid #ccc;">
        @csrf
        
        <p>
            <label>Tipo de Documento:</label><br>
            <select name="tipo_documento" required>
                <option value="">-- Selecciona qué vas a subir --</option>
                <option value="Acta de Nacimiento">Acta de Nacimiento</option>
                <option value="CURP">CURP</option>
                <option value="Certificado de Secundaria">Certificado de Secundaria</option>
                <option value="Comprobante de Domicilio">Comprobante de Domicilio</option>
            </select>
        </p>

        <p>
            <label>Proceso al que corresponde:</label><br>
            <select name="proceso" required>
                <option value="INSCRIPCION">Nuevo Ingreso (Inscripción)</option>
                <option value="REINSCRIPCION">Pase de Semestre (Reinscripción)</option>
                <option value="EGRESO">Trámites Finales (Egreso)</option>
            </select>
        </p>

        <p>
            <label>Archivo PDF (Máximo 2MB):</label><br>
            <!-- accept=".pdf" restringe la ventana de búsqueda del usuario -->
            <input type="file" name="archivo" accept=".pdf" required>
        </p>

        <p>
            <button type="submit">Subir Documento</button>
        </p>
    </form>

    <hr>

    <h3>Historial de mis Documentos</h3>
    <table border="1" cellpadding="8" cellspacing="0" width="100%">
        <thead>
            <tr bgcolor="#f2f2f2">
                <th>Fecha de Subida</th>
                <th>Documento</th>
                <th>Proceso</th>
                <th>Estado de Revisión</th>
                <th>Observaciones</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($documentos as $doc)
                <tr>
                    <td align="center">{{ $doc->created_at->format('d/m/Y') }}</td>
                    <td><b>{{ $doc->tipo_documento }}</b> <br> <a href="{{ asset('storage/' . $doc->ruta_archivo) }}" target="_blank"><small>Ver mi PDF</small></a></td>
                    <td align="center">{{ $doc->proceso }}</td>
                    <td align="center">
                        @if($doc->estado_validacion == 'PENDIENTE')
                            <span style="color: orange;">En revisión</span>
                        @elseif($doc->estado_validacion == 'APROBADO')
                            <span style="color: green;">Aprobado</span>
                        @else
                            <span style="color: red;">Rechazado</span>
                        @endif
                    </td>
                    <td>{{ $doc->observaciones ?? 'Sin comentarios' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" align="center">Aún no has subido ningún documento a tu expediente.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

</body>
</html>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Control de Pagos y Solicitudes</title>
</head>
<body>

    <h1>Seguimiento de Pagos y Trámites</h1>
    <a href="{{ route('admin.dashboard') }}">Volver al Dashboard</a>
    <hr>

    @if(session('success'))
        <div style="color: green; font-size: 16px; margin-bottom: 15px;">
            <b>Éxito:</b> {{ session('success') }}
        </div>
    @endif

    <table border="1" cellpadding="8" cellspacing="0" width="100%">
        <thead>
            <tr bgcolor="#f2f2f2">
                <th>Folio</th>
                <th>Fecha</th>
                <th>Alumno (Matrícula)</th>
                <th>Total</th>
                <th>Estado Actual</th>
                <th>Acción (Actualizar Estado)</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($solicitudes as $solicitud)
                <tr>
                    <td align="center"><b>{{ $solicitud->folio }}</b></td>
                    <td align="center">{{ $solicitud->created_at->format('d/m/Y h:i A') }}</td>
                    <td>
                        {{ $solicitud->alumno->apellido_paterno }} {{ $solicitud->alumno->apellido_materno }} {{ $solicitud->alumno->nombres }} 
                        <br>
                        <small><i>Matrícula: {{ $solicitud->alumno->matricula }}</i></small>
                    </td>
                    <td align="right"><b>${{ number_format($solicitud->total_pagar, 2) }}</b></td>
                    
                    <td align="center">
                        @if($solicitud->estado == 'PENDIENTE')
                            <span style="color: orange; font-weight: bold;">PENDIENTE</span>
                        @elseif($solicitud->estado == 'PAGADA')
                            <span style="color: green; font-weight: bold;">PAGADA</span>
                        @else
                            <span style="color: red; font-weight: bold;">CANCELADA</span>
                        @endif
                    </td>

                    <td align="center">
                        <form action="{{ route('admin.pagos.estado', $solicitud->id_solicitud) }}" method="POST" style="margin: 0;">
                            @csrf
                            @method('PUT')
                            <select name="estado" required>
                                <option value="PENDIENTE" {{ $solicitud->estado == 'PENDIENTE' ? 'selected' : '' }}>Pendiente</option>
                                <option value="PAGADA" {{ $solicitud->estado == 'PAGADA' ? 'selected' : '' }}>Pagada</option>
                                <option value="CANCELADA" {{ $solicitud->estado == 'CANCELADA' ? 'selected' : '' }}>Cancelada</option>
                            </select>
                            <button type="submit">Actualizar</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" align="center">No hay solicitudes registradas en el sistema.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

</body>
</html>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Mis Solicitudes</title>
</head>
<body>

    <h1>Mis Solicitudes de Trámites</h1>
    <a href="{{ route('alumno.dashboard') }}">Volver al Dashboard</a>
    <hr>

    @if(session('success'))
        <div style="color: green; font-size: 18px; margin-bottom: 15px;">
            <b>Éxito:</b> {{ session('success') }}
        </div>
    @endif

    <p>
        <a href="{{ route('alumno.solicitudes.create') }}">
            <button>+ Generar Nueva Solicitud</button>
        </a>
    </p>

    <table border="1" cellpadding="8" cellspacing="0" width="100%">
        <thead>
            <tr bgcolor="#f2f2f2">
                <th>Folio</th>
                <th>Fecha de Solicitud</th>
                <th>Total a Pagar</th>
                <th>Estado</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($solicitudes as $solicitud)
                <tr>
                    <td align="center"><b>{{ $solicitud->folio }}</b></td>
                    <td align="center">{{ $solicitud->created_at->format('d/m/Y h:i A') }}</td>
                    <td align="right">${{ number_format($solicitud->total_pagar, 2) }}</td>
                    <td align="center">
                        @if($solicitud->estado == 'PENDIENTE')
                            <span style="color: orange;"><b>Pendiente de pago</b></span>
                        @elseif($solicitud->estado == 'PAGADA')
                            <span style="color: green;"><b>Pagada</b></span>
                        @else
                            <span style="color: red;"><b>Cancelada</b></span>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" align="center">No tienes solicitudes generadas.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

</body>
</html>
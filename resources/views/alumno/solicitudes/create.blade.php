<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Nueva Solicitud</title>
</head>
<body>

    <h1>Generar Nueva Solicitud de Pago</h1>
    <a href="{{ route('alumno.solicitudes') }}">Cancelar y volver</a>
    <hr>

    @if ($errors->any())
        <div style="color: red;">Debes seleccionar al menos un servicio.</div>
    @endif

    <form action="{{ route('alumno.solicitudes.store') }}" method="POST">
        @csrf
        <table border="1" cellpadding="8" cellspacing="0" width="100%">
            <thead>
                <tr bgcolor="#f2f2f2">
                    <th>Seleccionar</th>
                    <th>Artículo</th>
                    <th>Concepto</th>
                    <th>Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($servicios as $servicio)
                    <tr>
                        <td align="center">
                            <input type="checkbox" name="servicios[]" value="{{ $servicio->id_servicio }}">
                        </td>
                        <td>{{ $servicio->articulo }}</td>
                        <td>{{ $servicio->concepto }}</td>
                        <td align="right"><b>${{ number_format($servicio->total, 2) }}</b></td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        
        <br>
        <button type="submit" style="padding: 10px; font-size: 16px;">Generar Solicitud con los servicios marcados</button>
    </form>

</body>
</html>
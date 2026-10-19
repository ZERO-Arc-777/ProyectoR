<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Módulo de Docentes</title>
</head>
<body>

    <h1>Gestión de Docentes</h1>
    <a href="{{ route('admin.dashboard') }}">Volver al Dashboard</a>
    <hr>

    @if(session('success'))
        <div style="color: green; margin-bottom: 15px;">{{ session('success') }}</div>
    @endif

    <p>
        <a href="{{ route('admin.docentes.create') }}"><button>+ Dar de Alta Docente</button></a>
    </p>

    <table border="1" cellpadding="8" cellspacing="0" width="100%">
        <thead>
            <tr bgcolor="#f2f2f2">
                <th>No. Empleado</th>
                <th>Nombre Completo</th>
                <th>CURP</th>
                <th>Cédula</th>
                <th>Estado</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($docentes as $docente)
                <tr>
                    <td>{{ $docente->numero_empleado }}</td>
                    <td>{{ $docente->apellido_paterno }} {{ $docente->apellido_materno }} {{ $docente->nombres }}</td>
                    <td>{{ $docente->curp }}</td>
                    <td>{{ $docente->cedula_profesional ?? 'Pendiente' }}</td>
                    <td align="center">{{ $docente->estado }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" align="center">No hay docentes registrados.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

</body>
</html>
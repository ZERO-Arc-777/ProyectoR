<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Módulo de Alumnos</title>
</head>
<body>

    <h1>Gestión de Alumnos</h1>
    <a href="{{ route('admin.dashboard') }}">Volver al Dashboard</a>
    <hr>

    @if(session('success'))
        <div style="color: green; margin-bottom: 15px;">{{ session('success') }}</div>
    @endif

    <p>
        <a href="{{ route('admin.alumnos.create') }}"><button>+ Inscribir Nuevo Alumno</button></a>
    </p>

    <table border="1" cellpadding="8" cellspacing="0" width="100%">
        <thead>
            <tr bgcolor="#f2f2f2">
                <th>Matrícula</th>
                <th>Nombre Completo</th>
                <th>CURP</th>
                <th>Semestre y Grupo</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($alumnos as $alumno)
                <tr>
                    <td>{{ $alumno->matricula }}</td>
                    <td>{{ $alumno->apellido_paterno }} {{ $alumno->apellido_materno }} {{ $alumno->nombre }}</td>
                    <td>{{ $alumno->curp }}</td>
                    <td align="center">{{ $alumno->semestre }}° "{{ $alumno->grupo }}"</td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" align="center">No hay alumnos inscritos.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

</body>
</html>
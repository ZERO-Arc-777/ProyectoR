<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Alta de Docente</title>
</head>
<body>

    <h1>Dar de Alta Docente</h1>
    <a href="{{ route('admin.docentes') }}">Volver a la lista</a>
    <hr>

    @if ($errors->any())
        <div style="color: red; margin-bottom: 15px;">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('admin.docentes.store') }}" method="POST">
        @csrf

        <p>
            <label>Vincular a Cuenta de Usuario:</label><br>
            <select name="id_usuario" required>
                <option value="">-- Selecciona una cuenta --</option>
                @foreach ($usuariosDisponibles as $user)
                    <option value="{{ $user->id_usuario }}">{{ $user->correo }} (ID: {{ $user->id_usuario }})</option>
                @endforeach
            </select>
        </p>

        <p>
            <label>Número de Empleado:</label><br>
            <input type="text" name="numero_empleado" required maxlength="20">
        </p>

        <p>
            <label>CURP:</label><br>
            <input type="text" name="curp" required maxlength="18" style="text-transform: uppercase;">
        </p>

        <p>
            <label>Nombre(s):</label><br>
            <input type="text" name="nombres" required>
        </p>

        <p>
            <label>Apellido Paterno:</label><br>
            <input type="text" name="apellido_paterno" required>
        </p>

        <p>
            <label>Apellido Materno:</label><br>
            <input type="text" name="apellido_materno">
        </p>

        <p>
            <label>Cédula Profesional (Opcional):</label><br>
            <input type="text" name="cedula_profesional" maxlength="50">
        </p>

        <p>
            <button type="submit">Guardar Expediente Docente</button>
        </p>
    </form>

</body>
</html>
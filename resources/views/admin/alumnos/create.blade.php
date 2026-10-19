<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Inscribir Alumno</title>
</head>
<body>

    <h1>Crear Expediente de Alumno</h1>
    <a href="{{ route('admin.alumnos') }}">Volver a la lista</a>
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

    <form action="{{ route('admin.alumnos.store') }}" method="POST">
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
            <label>Matrícula Oficial:</label><br>
            <input type="text" name="matricula" required maxlength="20">
        </p>

        <p>
            <label>CURP:</label><br>
            <input type="text" name="curp" required maxlength="18" style="text-transform: uppercase;">
        </p>

        <p>
            <label>Nombre(s):</label><br>
            <input type="text" name="nombre" required>
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
            <label>Semestre:</label><br>
            <select name="semestre" required>
                <option value="1">1° Semestre</option>
                <option value="3">3° Semestre</option>
                <option value="5">5° Semestre</option>
            </select>
        </p>

        <p>
            <label>Grupo:</label><br>
            <select name="grupo" required>
                <option value="A">Grupo A</option>
                <option value="B">Grupo B</option>
            </select>
        </p>

        <p>
            <button type="submit">Guardar Expediente</button>
        </p>
    </form>

</body>
</html>
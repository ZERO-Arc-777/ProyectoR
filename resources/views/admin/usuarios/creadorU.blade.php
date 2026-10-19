<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Crear Usuario</title>
</head>
<body>

    <h1>Crear Nuevo Usuario</h1>
    
    <a href="{{ route('admin.usuarios') }}">Volver a la lista de Usuarios</a>
    <hr>

    <!-- Mostrar errores de validación si faltan datos o el correo ya existe -->
    @if ($errors->any())
        <div style="color: red; margin-bottom: 15px;">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('admin.usuarios.store') }}" method="POST">
        @csrf <!-- Token de seguridad obligatorio en Laravel -->

        <p>
            <label>Nombre Completo:</label><br>
            <input type="text" name="nombre" required>
        </p>

        <p>
            <label>Correo Electrónico:</label><br>
            <input type="email" name="correo" required>
        </p>

        <p>
            <label>Contraseña:</label><br>
            <input type="password" name="contrasena" required minlength="6">
        </p>

        <p>
            <label>Asignar Rol:</label><br>
            <select name="id_rol" required>
                <option value="">-- Selecciona un Rol --</option>
                @foreach ($roles as $rol)
                    <option value="{{ $rol->id_rol }}">{{ $rol->nombre }}</option>
                @endforeach
            </select>
        </p>

        <p>
            <button type="submit">Guardar Usuario</button>
        </p>
    </form>

</body>
</html>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Seleccionar rol</title>
</head>
<body>

    <h1>Selecciona tu tipo de acceso</h1>

    <hr>

    <ul>
        @foreach ($roles as $rol)
            <li>
                <a href="{{ route('login', ['rol' => $rol->id_rol]) }}">
                    <strong>{{ $rol->nombre }}</strong>
                </a> 
                - {{ $rol->descripcion }}
            </li>
        @endforeach
    </ul>

    <hr>

</body>
</html>
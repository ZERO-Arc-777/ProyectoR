<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Login</title>
</head>
<body>
    <h2>Login de prueba</h2>
    @if(session('error'))
        <div style="color: red; font-weight: bold; margin-bottom: 15px;">
            {{ session('error') }}
        </div>
    @endif
    <form method="POST" action="{{ route('login.post') }}">
        @csrf
        <label for="username">Usuario:</label>
        <input type="text" id="username" name="username"><br><br>

        <label for="password">Contraseña:</label>
        <input type="password" id="password" name="password"><br><br>

        <button type="submit">Ingresar</button>
    </form>

    @if ($errors->any())
        <div style="color:red;">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif
</body>
</html>

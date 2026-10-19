<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Panel del Alumno - Control Escolar</title>
</head>

<body>

    <h1>Panel de los alumnos</h1>

    <hr>

    <h3>Menú Principal</h3>

    <ul>
        <li><a href="{{ route('alumno.materias') }}"> Materias</a></li>
    </ul>

    <hr>

    <h2>Bienvenido Administrador</h2>

    <p>
        <a href="{{ route('logout') }}" style="color: red; font-weight: bold;">[ Cerrar Sesión ]</a>
    </p>

</body>

</html>

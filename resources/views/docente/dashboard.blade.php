
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Panel de Docente - Control Escolar</title>
</head>

<body>

    <h1>Panel de Docente</h1>

    <hr>

    <h3>Menú Principal</h3>

    <ul>
        <li>Inicio</li>
        <li>Mis Materias</li>
        <li>Mis Grupos</li>
        <li>Mi Perfil</li>
    </ul>

    <hr>

    <h2>Bienvenido Docente</h2>

    <p>
        Bienvenido al Sistema de Control Escolar
        de la Escuela Preparatoria Número 5 del Estado.
    </p>

    <p>
        Desde este panel podrás consultar las materias
        que impartes, los grupos que tienes asignados
        y tu información personal.
    </p>

    <hr>

    <p>
        <a href="{{ route('logout') }}"
           style="color: red; font-weight: bold;">
            [ Cerrar Sesión ]
        </a>
    </p>

</body>

</html>

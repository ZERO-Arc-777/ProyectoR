<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Panel de Administrador - Control Escolar</title>
</head>

<body>

    <h1>Panel de Administración</h1>

    <hr>

    <h3>Menú Principal</h3>

    <ul>
        <li><a href="{{ route('admin.dashboard') }}"> Inicio</a></li>
        <li><a href="{{ route('admin.usuarios') }}"> Usuarios</a></li>
        <li><a href="{{ route('admin.alumnos') }}"> Alumnos</a></li>
        <li><a href="{{ route('admin.docentes') }}"> Docentes</a></li>
        <li><a href="{{ route('admin.aspirantes') }}"> Aspirantes</a></li>
        <li><a href="{{ route('admin.inscripciones') }}"> Inscripciones</a></li>
        <li><a href="{{ route('admin.reinscripciones') }}"> Reinscripciones</a></li>
        <li><a href="{{ route('admin.materias') }}"> Materias</a></li>
        <li><a href="{{ route('admin.periodo') }}"> Periodos</a></li>
        <li><a href="{{ route('admin.historial') }}"> Historial Académico</a></li>
        <li><a href="{{ route('admin.pagos') }}"> Pagos</a></li>
        <li><a href="{{ route('admin.constancias') }}"> Constancias</a></li>
        <li><a href="{{ route('admin.expedientes') }}"> Expedientes</a></li>
        <li><a href="{{ route('admin.bajas') }}"> Bajas</a></li>
        <li><a href="{{ route('admin.egresados') }}"> Egresados</a></li>
    </ul>

    <hr>

    <h2>Bienvenido Administrador</h2>

    <p>
        <a href="{{ route('logout') }}" style="color: red; font-weight: bold;">[ Cerrar Sesión ]</a>
    </p>

</body>

</html>
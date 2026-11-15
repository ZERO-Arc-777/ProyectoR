@extends('layouts.app')

@section('titulo', 'Alumnos del Grupo')

@section('usuario_header')
    <span class="fs-6">
        <i class="bi bi-person-circle me-1"></i>
        {{ Auth::user()->nombre ?? 'Docente' }}
    </span>
@endsection

@section('contenido')

<style>
    /* VARIABLES DE ENTORNO DOCENTE */
    .alumnos-panel {
        min-height: 100vh;
        background-color: #f4f6f9; /* Fondo gris claro general */
        color: #212529;
    }

    /* SIDEBAR */
    .alumnos-sidebar {
        background: #212529; 
        border-right: 1px solid #343a40;
    }

    .alumnos-nav {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 13px 16px;
        border-radius: 12px;
        color: #adb5bd;
        text-decoration: none;
        transition: .2s;
    }

    .alumnos-nav:hover {
        background: rgba(255, 255, 255, .1);
        color: #ffffff;
    }

    .alumnos-nav.active {
        background: #fd7e14;
        color: #ffffff;
        font-weight: 600;
    }

    .alumnos-muted {
        color: #6c757d;
    }

    /* TARJETA BLANCA PARA TABLA */
    .alumnos-table-card {
        background: #ffffff;
        border: 1px solid #c0c4c5;
        border-radius: 20px;
        padding: 25px;
        box-shadow: 0 4px 6px rgba(0,0,0,0.02);
    }

    /* ESTILOS DE TABLA */
    .table-alumnos {
        width: 100%;
        margin-bottom: 0;
        border-collapse: separate;
        border-spacing: 0;
    }

    .table-alumnos th {
        background-color: transparent;
        color: #212529;
        font-weight: 700;
        text-transform: uppercase;
        font-size: 0.85rem;
        padding: 15px 10px;
        border-bottom: 2px solid #e9ecef;
        text-align: left;
    }

    .table-alumnos td {
        padding: 15px 10px;
        vertical-align: middle;
        border-bottom: 1px solid #f4f6f9;
        color: #495057;
    }

    .table-alumnos tr:last-child td {
        border-bottom: none;
    }

    .table-alumnos tr:hover td {
        background-color: #f8f9fa; /* Hover para las filas (table-hover) */
    }

    /* ETIQUETAS */
    .alumnos-etiqueta {
        display: inline-flex;
        align-items: center;
        padding: 4px 10px;
        border-radius: 30px;
        background: rgba(253, 126, 20, 0.15);
        color: #d95c00;
        font-size: .75rem;
        font-weight: 600;
        margin-left: 5px;
    }

    /* BOTÓN NARANJA INSTITUCIONAL (Acción importante) */
    .btn-docente-naranja {
        background-color: #fd7e14;
        color: #ffffff;
        border: 1px solid #fd7e14;
        transition: all 0.2s;
    }

    .btn-docente-naranja:hover {
        background-color: #e36a0b;
        border-color: #e36a0b;
        color: #ffffff;
    }

    /* ESTADO VACÍO */
    .alumnos-vacio {
        background: #ffffff;
        border: 1px dashed #c0c4c5;
        border-radius: 20px;
        padding: 55px 25px;
        text-align: center;
        color: #212529;
    }

    .alumnos-vacio-icon {
        width: 80px;
        height: 80px;
        border-radius: 22px;
        background: rgba(253, 126, 20, 0.15);
        color: #fd7e14;
        display: grid;
        place-items: center;
        margin: 0 auto 22px;
        font-size: 2.2rem;
    }
</style>

<div class="container-fluid alumnos-panel">

    <div class="row min-vh-100">

        <!-- MENÚ LATERAL -->
        <aside class="col-12 col-md-3 col-lg-2 alumnos-sidebar p-4">
            <div class="text-center mb-4">
                <i class="bi bi-mortarboard-fill fs-2 text-white"></i>
                <h5 class="fw-bold mt-2 mb-1 text-white">
                    PANEL DOCENTE
                </h5>
                <small class="alumnos-muted">
                    Gestión Académica
                </small>
            </div>

            <hr class="border-secondary my-4">

            <nav class="d-grid gap-2" aria-label="Menú del docente">
                <a href="{{ route('docente.dashboard') }}" class="alumnos-nav">
                    <i class="bi bi-grid-1x2-fill"></i> Inicio
                </a>
                <a href="{{ route('docente.materias') }}" class="alumnos-nav">
                    <i class="bi bi-journal-bookmark-fill"></i> Mis Materias
                </a>
                <!-- Mantengo activo "Mis Grupos" porque esta es una vista hija -->
                <a href="{{ route('docente.grupos') }}" class="alumnos-nav active">
                    <i class="bi bi-people-fill"></i> Mis Grupos
                </a>
                <a href="{{ route('docente.perfil') }}" class="alumnos-nav">
                    <i class="bi bi-person-badge-fill"></i> Mi Perfil
                </a>
                <hr class="border-secondary my-3">
                <a href="{{ route('logout') }}" class="alumnos-nav text-danger">
                    <i class="bi bi-box-arrow-right"></i> Cerrar sesión
                </a>
            </nav>
        </aside>

        <!-- CONTENIDO PRINCIPAL -->
        <main class="col-12 col-md-9 col-lg-10 p-4 p-lg-5">

            <!-- CABECERA LIMPIA CON BOTÓN DE VOLVER (Instrucción 2) -->
            <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
                <div>
                    <h2 class="text-dark fw-bold mb-2">
                        Alumnos del Grupo {{ $asignacion->materia->semestre ?? '' }}° {{ $asignacion->grupo ?? '' }}
                    </h2>
                    <p class="alumnos-muted mb-0">
                        Materia: <strong class="text-dark">{{ $asignacion->materia->nombre ?? '' }}</strong> | 
                        Periodo: <span class="alumnos-etiqueta">{{ $asignacion->periodo ?? '' }}</span>
                    </p>
                </div>
                
                <a href="{{ route('docente.grupos') }}" class="btn btn-sm btn-outline-secondary rounded-pill px-3 d-inline-flex align-items-center gap-1">
                    <i class="bi bi-arrow-left"></i> Volver a Mis Grupos
                </a>
            </div>

            <!-- BOTÓN GENERAR LISTAS (Instrucciones de diseño para el botón de listas) -->
            <div class="d-flex justify-content-end mb-4">
                <a href="#" class="btn btn-docente-naranja rounded-pill px-4 fw-medium d-inline-flex align-items-center gap-2">
                    <i class="bi bi-file-earmark-pdf-fill"></i> Generar Lista de Asistencia (PDF)
                </a>
            </div>

            <!-- TARJETA BLANCA PARA LA TABLA (Instrucciones de diseño para la tabla) -->
            <div class="alumnos-table-card">
                
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h5 class="fw-bold mb-0">
                        <i class="bi bi-card-checklist me-2 text-dark"></i>
                        Padrón de Alumnos Inscritos
                    </h5>
                </div>

                <div class="table-responsive">
                    <table class="table-alumnos">
                        <thead>
                            <tr>
                                <th style="width: 10%;">NÚM.</th>
                                <th style="width: 20%;">MATRÍCULA</th>
                                <th style="width: 55%;">NOMBRE DEL ALUMNO</th>
                                <th style="width: 15%; text-align: center;">ESTADO</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($alumnos as $index => $alumno)
                                <tr>
                                    <td>
                                        <span class="text-muted fw-bold">{{ $index + 1 }}</span>
                                    </td>
                                    <td>
                                        <span class="fw-medium">{{ $alumno->matricula }}</span>
                                    </td>
                                    <td>
                                        <span class="fw-bold text-dark">
                                            {{ $alumno->apellido_paterno }} {{ $alumno->apellido_materno }} {{ $alumno->nombres }}
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <!-- PÍLDORA VERDE DE ESTADO -->
                                        <span class="badge bg-success rounded-pill px-3 py-2 fw-medium">Activo</span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="p-0 border-0 pt-3">
                                        <div class="alumnos-vacio mt-2">
                                            <div class="alumnos-vacio-icon">
                                                <i class="bi bi-person-slash"></i>
                                            </div>
                                            <h4 class="fw-bold mb-2">Sin alumnos registrados</h4>
                                            <p class="alumnos-muted mb-0">
                                                No hay alumnos inscritos en este grupo todavía.
                                            </p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

            </div>

        </main>
    </div>
</div>

@endsection
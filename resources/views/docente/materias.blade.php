@extends('layouts.app')

@section('titulo', 'Mis Materias Asignadas')

@section('usuario_header')
    <span class="fs-6">
        <i class="bi bi-person-circle me-1"></i>
        {{ Auth::user()->nombre ?? 'Docente' }}
    </span>
@endsection

@section('contenido')

<style>
    /* VARIABLES DE ENTORNO DOCENTE */
    .materias-panel {
        min-height: 100vh;
        background-color: #f4f6f9; 
        color: #212529;
    }

    /* SIDEBAR */
    .materias-sidebar {
        background: #212529; 
        border-right: 1px solid #343a40;
    }

    .materias-nav {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 13px 16px;
        border-radius: 12px;
        color: #adb5bd;
        text-decoration: none;
        transition: .2s;
    }

    .materias-nav:hover {
        background: rgba(255, 255, 255, .1);
        color: #ffffff;
    }

    .materias-nav.active {
        background: #fd7e14;
        color: #ffffff;
        font-weight: 600;
    }

    .materias-muted {
        color: #6c757d;
    }

    /* ALERTA OSCURA / PERIODO ESCOLAR */
    .materias-heading {
        background: #212529;
        border: 1px solid #343a40;
        border-radius: 20px;
        padding: 25px 30px;
        color: #ffffff;
    }

    /* TARJETA BLANCA PARA TABLA */
    .materias-table-card {
        background: #ffffff;
        border: 1px solid #c0c4c5;
        border-radius: 20px;
        padding: 25px;
        box-shadow: 0 4px 6px rgba(0,0,0,0.02);
    }

    /* ESTILOS DE TABLA */
    .table-materias {
        width: 100%;
        margin-bottom: 0;
        border-collapse: separate;
        border-spacing: 0;
    }

    .table-materias th {
        background-color: transparent;
        color: #212529;
        font-weight: 700;
        text-transform: uppercase;
        font-size: 0.85rem;
        padding: 15px 10px;
        border-bottom: 2px solid #e9ecef;
        text-align: left;
    }

    .table-materias td {
        padding: 15px 10px;
        vertical-align: middle;
        border-bottom: 1px solid #f4f6f9;
        color: #495057;
    }

    .table-materias tr:last-child td {
        border-bottom: none;
    }

    .table-materias tr:hover td {
        background-color: #f8f9fa;
    }

    /* ETIQUETAS DE ACENTO NARANJA */
    .materias-etiqueta {
        display: inline-flex;
        align-items: center;
        padding: 5px 12px;
        border-radius: 30px;
        background: rgba(253, 126, 20, 0.15);
        color: #d95c00;
        font-size: .8rem;
        font-weight: 600;
    }

    /* BOTÓN NARANJA DOCENTE (Para acciones de navegación) */
    .btn-outline-docente {
        color: #fd7e14;
        border: 1px solid #fd7e14;
        background-color: transparent;
        transition: all 0.2s;
    }
    
    .btn-outline-docente:hover {
        background-color: #fd7e14;
        color: #ffffff;
        border-color: #fd7e14;
    }

    /* ESTADO VACÍO */
    .materias-vacio {
        background: #ffffff;
        border: 1px dashed #c0c4c5;
        border-radius: 20px;
        padding: 55px 25px;
        text-align: center;
        color: #212529;
    }

    .materias-vacio-icon {
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

<div class="container-fluid materias-panel">

    <div class="row min-vh-100">

        <!-- MENÚ LATERAL -->
        <aside class="col-12 col-md-3 col-lg-2 materias-sidebar p-4">

            <div class="text-center mb-4">
                <i class="bi bi-mortarboard-fill fs-2 text-white"></i>
                <h5 class="fw-bold mt-2 mb-1 text-white">
                    PANEL DOCENTE
                </h5>
                <small class="materias-muted">
                    Gestión Académica
                </small>
            </div>

            <hr class="border-secondary my-4">

            <nav class="d-grid gap-2" aria-label="Menú del docente">
                <a href="{{ route('docente.dashboard') }}" class="materias-nav">
                    <i class="bi bi-grid-1x2-fill"></i> Inicio
                </a>
                <a href="{{ route('docente.materias') }}" class="materias-nav active" aria-current="page">
                    <i class="bi bi-journal-bookmark-fill"></i> Mis Materias
                </a>
                <a href="{{ route('docente.grupos') }}" class="materias-nav">
                    <i class="bi bi-people-fill"></i> Mis Grupos
                </a>
                <a href="{{ route('docente.perfil') }}" class="materias-nav">
                    <i class="bi bi-person-badge-fill"></i> Mi Perfil
                </a>
                <hr class="border-secondary my-3">
                <!-- CERRAR SESIÓN SEGURO -->
                <form action="{{ route('logout.seguro') }}" method="POST" class="m-0">
                    @csrf

                    <button type="submit"
                            class="materias-nav text-danger"
                            style="background: none;
                                border: none;
                                cursor: pointer;
                                width: 100%;
                                text-align: left;
                                font: inherit;
                                display: flex;
                                align-items: center;
                                gap: 12px;
                                white-space: nowrap;">
                        <i class="bi bi-box-arrow-right"></i>
                        <span>Cerrar sesión</span>
                    </button>
                </form>
            </nav>

        </aside>

        <!-- CONTENIDO PRINCIPAL -->
        <main class="col-12 col-md-9 col-lg-10 p-4 p-lg-5">

            <!-- TÍTULO PRINCIPAL -->
            <div class="mb-4">
                <h2 class="text-dark fw-bold mb-2">
                    Mis Materias Asignadas
                </h2>
                <!-- Subtítulo actualizado según tus instrucciones -->
                <p class="materias-muted mb-0">
                    Administra las calificaciones de tus grupos.
                </p>
            </div>

            <!-- ALERTA / PERIODO ESCOLAR -->
            <section class="materias-heading mb-4 d-flex align-items-center gap-3">
                <i class="bi bi-calendar3 fs-2 text-white-50"></i>
                <div>
                    <p class="mb-1 text-white-50 small text-uppercase fw-bold">Periodo escolar actual</p>
                    <h4 class="fw-bold mb-0 text-white">
                        {{ $periodoActual ?? 'Periodo no definido' }}
                    </h4>
                </div>
            </section>

            <!-- TARJETA BLANCA - CONTENEDOR DE LA TABLA -->
            <section class="materias-table-card">
                
                <h5 class="fw-bold mb-4">
                    <i class="bi bi-journal-text me-2 text-dark"></i>
                    Materias del ciclo
                </h5>

                <div class="table-responsive">
                    <table class="table-materias">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Nombre de la materia</th>
                                <th>Semestre</th>
                                <th>Grupo</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($asignaciones as $asignacion)
                                <tr>
                                    <td>
                                        <span class="text-muted fw-bold">#{{ $asignacion->id_asignacion }}</span>
                                    </td>
                                    <td>
                                        <span class="fw-bold text-dark">{{ $asignacion->materia->nombre }}</span>
                                    </td>
                                    <td>
                                        <span class="materias-etiqueta">
                                            {{ $asignacion->materia->semestre }}° semestre
                                        </span>
                                    </td>
                                    <td>
                                        <span class="fw-medium">Grupo {{ $asignacion->grupo }}</span>
                                    </td>
                                    <td>
                                        <div class="d-flex gap-2">
                                            <!-- BOTÓN NARANJA (Sólo enlace de navegación) -->
                                            <a href="{{ route('docente.materias.calificar', $asignacion->id_asignacion) }}" 
                                               class="btn btn-sm btn-outline-docente rounded-pill fw-medium px-3 d-inline-flex align-items-center gap-1">
                                                <i class="bi bi-check2-square"></i> Calificar Grupo
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="p-0 border-0 pt-3">
                                        <div class="materias-vacio mt-2">
                                            <div class="materias-vacio-icon">
                                                <i class="bi bi-journal-x"></i>
                                            </div>
                                            <h4 class="fw-bold mb-2">No tienes materias asignadas</h4>
                                            <p class="materias-muted mb-0">
                                                Actualmente no cuentas con materias asignadas en este periodo escolar.
                                            </p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

            </section>

        </main>
    </div>
</div>

@endsection
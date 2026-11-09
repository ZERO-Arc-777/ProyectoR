
@extends('layouts.app')

@section('titulo', 'Mis Grupos')

@section('usuario_header')
    <span class="fs-6">
        <i class="bi bi-person-circle me-1"></i>
        {{ Auth::user()->nombre ?? 'Docente' }}
    </span>
@endsection

@section('contenido')

<style>
    .grupos-panel {
        min-height: 100vh;
        color: #f5f3ff;
    }

    .grupos-sidebar {
        background: #171323;
        border-right: 1px solid rgba(255,255,255,.09);
    }

    .grupos-nav {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 13px 16px;
        border-radius: 12px;
        color: #ccc6df;
        text-decoration: none;
        transition: .2s;
    }

    .grupos-nav:hover {
        background: rgba(151,91,255,.13);
        color: white;
    }

    .grupos-nav.active {
        background: #6934c5;
        color: white;
        font-weight: 600;
    }

    .grupos-muted {
        color: #b9b2c9;
    }

    .grupos-heading {
        background: linear-gradient(110deg, #44217c, #712fc3);
        border: 1px solid rgba(255,255,255,.1);
        border-radius: 20px;
        padding: 30px;
    }

    .grupos-card {
        background: #211a33;
        border: 1px solid rgba(255,255,255,.09);
        border-radius: 20px;
        padding: 32px;
    }

    .grupos-icon {
        width: 74px;
        height: 74px;
        background: rgba(157,92,255,.16);
        color: #c49bff;
        border-radius: 20px;
        display: grid;
        place-items: center;
        font-size: 2rem;
    }

    .grupos-status {
        display: inline-block;
        background: #49316a;
        color: #d6bbff;
        border-radius: 30px;
        padding: 9px 16px;
        font-size: .85rem;
        font-weight: 600;
    }

    .grupos-info {
        background: #211a33;
        border: 1px solid rgba(255,255,255,.09);
        border-radius: 18px;
        padding: 25px;
    }
</style>

<div class="container-fluid grupos-panel">
    <div class="row min-vh-100">

        <!-- MENÚ LATERAL -->
        <aside class="col-12 col-md-3 col-lg-2 grupos-sidebar p-4">

            <div class="text-center mb-4">

                <i class="bi bi-mortarboard-fill fs-2"></i>

                <h5 class="fw-bold mt-2 mb-1">
                    PANEL DOCENTE
                </h5>

                <small class="grupos-muted">
                    Gestión Académica
                </small>

            </div>

            <hr class="border-secondary my-4">

            <nav class="d-grid gap-2"
                 aria-label="Menú del docente">

                <!-- INICIO -->
                <a href="{{ route('docente.dashboard') }}"
                   class="grupos-nav">
                    <i class="bi bi-grid-1x2-fill"></i>
                    Inicio
                </a>

                <!-- MIS MATERIAS -->
                <a href="{{ route('docente.materias') }}"
                   class="grupos-nav">
                    <i class="bi bi-journal-bookmark-fill"></i>
                    Mis Materias
                </a>

                <!-- MIS GRUPOS -->
                <a href="{{ route('docente.grupos') }}"
                   class="grupos-nav active"
                   aria-current="page">
                    <i class="bi bi-people-fill"></i>
                    Mis Grupos
                </a>

                <!-- MI PERFIL -->
                <a href="{{ route('docente.perfil') }}"
                   class="grupos-nav">
                    <i class="bi bi-person-badge-fill"></i>
                    Mi Perfil
                </a>

                <hr class="border-secondary my-3">

                <!-- CERRAR SESIÓN -->
                <a href="{{ route('logout') }}"
                   class="grupos-nav text-danger">
                    <i class="bi bi-box-arrow-right"></i>
                    Cerrar sesión
                </a>

            </nav>

        </aside>

        <!-- CONTENIDO PRINCIPAL -->
        <main class="col-12 col-md-9 col-lg-10 p-4 p-lg-5"
              id="grupos-contenido">

            <!-- TÍTULO -->
            <div class="mb-4">

                <h2 class="fw-bold mb-2">
                    Mis Grupos
                </h2>

                <p class="grupos-muted mb-0">
                    Consulta la información de los grupos
                    que te sean asignados.
                </p>

            </div>

            <!-- ENCABEZADO MORADO -->
            <section class="grupos-heading mb-4">

                <div class="d-flex flex-wrap align-items-center gap-3">

                    <i class="bi bi-people-fill fs-1"></i>

                    <div>
                        <h4 class="fw-bold mb-2">
                            Gestión de Grupos
                        </h4>

                        <p class="mb-0 text-white-50">
                            Escuela Preparatoria Número 5 del Estado
                        </p>
                    </div>

                </div>

            </section>

            <!-- GRUPOS ASIGNADOS -->
            <section class="grupos-card text-center mb-4">

                <div class="grupos-icon mx-auto mb-4">
                    <i class="bi bi-person-lines-fill"></i>
                </div>

                <h4 class="fw-bold mb-3">
                    Grupos asignados
                </h4>

                <p class="grupos-muted mx-auto"
                   style="max-width: 580px;">
                    En esta sección podrás consultar los grupos
                    que tengas asignados, una vez que se implemente
                    la gestión de grupos y su relación
                    con los docentes.
                </p>

                <span class="grupos-status">
                    <i class="bi bi-clock me-1"></i>
                    Funcionalidad en desarrollo
                </span>

            </section>

            <!-- INFORMACIÓN GENERAL -->
            <section class="grupos-info">

                <div class="d-flex align-items-start gap-3">

                    <div class="grupos-icon flex-shrink-0"
                         style="width: 52px; height: 52px;
                                font-size: 1.4rem; border-radius: 14px;">
                        <i class="bi bi-info-circle-fill"></i>
                    </div>

                    <div>

                        <h5 class="fw-bold mb-2">
                            Información de grupos
                        </h5>

                        <p class="grupos-muted mb-0">
                            Este apartado permitirá al docente
                            consultar la información académica
                            de sus grupos asignados.

                            Las funciones estarán disponibles
                            conforme avance el desarrollo
                            del módulo docente.
                        </p>

                    </div>

                </div>

            </section>

        </main>

    </div>
</div>

@endsection

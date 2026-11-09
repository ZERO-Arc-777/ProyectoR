
@extends('layouts.app')

@section('titulo', 'Mis Materias')

@section('usuario_header')
    <span class="fs-6">
        <i class="bi bi-person-circle me-1"></i>
        {{ Auth::user()->nombre ?? 'Docente' }}
    </span>
@endsection

@section('contenido')

<style>
    .materias-panel {
        min-height: 100vh;
        color: #f5f3ff;
    }

    .materias-sidebar {
        background: #171323;
        border-right: 1px solid rgba(255,255,255,.09);
    }

    .materias-nav {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 13px 16px;
        border-radius: 12px;
        color: #ccc6df;
        text-decoration: none;
        transition: .2s;
    }

    .materias-nav:hover {
        background: rgba(151,91,255,.13);
        color: white;
    }

    .materias-nav.active {
        background: #6934c5;
        color: white;
        font-weight: 600;
    }

    .materias-card {
        background: #211a33;
        border: 1px solid rgba(255,255,255,.09);
        border-radius: 20px;
        padding: 32px;
    }

    .materias-icon {
        width: 74px;
        height: 74px;
        background: rgba(157,92,255,.16);
        color: #c49bff;
        border-radius: 20px;
        display: grid;
        place-items: center;
        font-size: 2rem;
    }

    .materias-muted {
        color: #b9b2c9;
    }

    .materias-heading {
        background: linear-gradient(110deg, #44217c, #712fc3);
        border-radius: 20px;
        padding: 30px;
    }
</style>

<div class="container-fluid materias-panel">
    <div class="row min-vh-100">

        <!-- MENÚ LATERAL -->
        <aside class="col-12 col-md-3 col-lg-2 materias-sidebar p-4">

            <div class="text-center mb-4">
                <i class="bi bi-mortarboard-fill fs-2"></i>

                <h5 class="fw-bold mt-2 mb-1">
                    PANEL DOCENTE
                </h5>

                <small class="materias-muted">
                    Gestión Académica
                </small>
            </div>

            <hr class="border-secondary my-4">

            <nav class="d-grid gap-2"
                 aria-label="Menú del docente">

                <!-- INICIO -->
                <a href="{{ route('docente.dashboard') }}"
                   class="materias-nav">
                    <i class="bi bi-grid-1x2-fill"></i>
                    Inicio
                </a>

                <!-- MIS MATERIAS -->
                <a href="{{ route('docente.materias') }}"
                   class="materias-nav active"
                   aria-current="page">
                    <i class="bi bi-journal-bookmark-fill"></i>
                    Mis Materias
                </a>

                <!-- MIS GRUPOS -->
                <a href="{{ route('docente.grupos') }}"
                   class="materias-nav">
                    <i class="bi bi-people-fill"></i>
                    Mis Grupos
                </a>

                <!-- MI PERFIL -->
                <a href="{{ route('docente.perfil') }}"
                   class="materias-nav">
                    <i class="bi bi-person-badge-fill"></i>
                    Mi Perfil
                </a>

                <hr class="border-secondary my-3">

                <!-- CERRAR SESIÓN -->
                <a href="{{ route('logout') }}"
                   class="materias-nav text-danger">
                    <i class="bi bi-box-arrow-right"></i>
                    Cerrar sesión
                </a>

            </nav>
        </aside>

        <!-- CONTENIDO PRINCIPAL -->
        <main class="col-12 col-md-9 col-lg-10 p-4 p-lg-5"
              id="materias-contenido">

            <div class="mb-4">
                <h2 class="fw-bold">
                    Mis Materias
                </h2>

                <p class="materias-muted">
                    Consulta la información de las materias
                    que te sean asignadas.
                </p>
            </div>

            <!-- ENCABEZADO -->
            <section class="materias-heading mb-4">

                <div class="d-flex align-items-center gap-3">

                    <i class="bi bi-journal-bookmark-fill fs-1"></i>

                    <div>
                        <h4 class="fw-bold mb-2">
                            Gestión de Materias
                        </h4>

                        <p class="mb-0 text-white-50">
                            Escuela Preparatoria Número 5 del Estado
                        </p>
                    </div>

                </div>

            </section>

            <!-- ESTADO DE LAS MATERIAS -->
            <section class="materias-card text-center">

                <div class="materias-icon mx-auto mb-4">
                    <i class="bi bi-journals"></i>
                </div>

                <h4 class="fw-bold mb-3">
                    Materias asignadas
                </h4>

                <p class="materias-muted mx-auto"
                   style="max-width: 560px;">
                    La consulta de materias estará disponible
                    cuando se implemente la asignación
                    de materias a docentes.
                </p>

                <span class="badge rounded-pill px-3 py-2"
                      style="background: #49316a; color: #d6bbff;">
                    <i class="bi bi-clock me-1"></i>
                    Funcionalidad en desarrollo
                </span>

            </section>

        </main>
    </div>
</div>

@endsection

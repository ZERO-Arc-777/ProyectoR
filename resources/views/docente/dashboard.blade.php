
@extends('layouts.app')

@section('titulo', 'Panel Docente')

@section('usuario_header')
    <span class="fs-6">
        <i class="bi bi-person-circle me-1"></i>
        {{ Auth::user()->nombre ?? 'Docente' }}
    </span>
@endsection

@section('contenido')

<style>
    .docente-panel {
        min-height: 100vh;
        background-color: #f4f6f9;
        color: #212529;
    }

    .docente-sidebar {
        background: #212529;
        border-right: 1px solid #343a40;
    }

    .docente-sidebar-title {
        font-weight: 800;
        letter-spacing: 1.2px;
        color: #ffffff;
    }

    .docente-nav {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 13px 16px;
        border-radius: 12px;
        color: #adb5bd;
        text-decoration: none;
        transition: background .2s, color .2s;
    }

    .docente-nav:hover {
        background: rgba(255, 255, 255, .1);
        color: #ffffff;
    }

    .docente-nav.active {
        background: #fd7e14;
        color: #ffffff;
        font-weight: 600;
    }

    .docente-nav i {
        font-size: 1.15rem;
    }

    .docente-main {
        background: transparent;
    }

    .docente-welcome {
        background: #212529;
        border: 1px solid #343a40;
        border-radius: 20px;
        padding: 32px;
        color: #ffffff;
    }

    .docente-welcome-icon {
        width: 66px;
        height: 66px;
        border-radius: 18px;
        background: rgba(253, 126, 20, 0.15);
        color: #fd7e14;
        display: grid;
        place-items: center;
        font-size: 2rem;
    }

    .docente-card {
        background: #d1d5d6;
        border: 1px solid #c0c4c5;
        border-radius: 18px;
        height: 100%;
        color: #212529;
        transition: transform .2s, box-shadow .2s, border-color .2s;
        display: block;
        text-decoration: none;
    }

    .docente-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 10px 20px rgba(0,0,0,0.1);
        border-color: #fd7e14;
        color: #212529;
    }

    .docente-card-icon {
        width: 52px;
        height: 52px;
        border-radius: 14px;
        display: grid;
        place-items: center;
        background: #212529;
        color: #fd7e14;
        font-size: 1.5rem;
    }

    .docente-muted {
        color: #6c757d;
    }

    .docente-status {
        display: inline-flex;
        align-items: center;
        background: rgba(253, 126, 20, 0.15);
        color: #d95c00;
        border-radius: 30px;
        padding: 6px 12px;
        font-size: .78rem;
        font-weight: 600;
    }

    .docente-info {
        background: #d1d5d6;
        border: 1px solid #c0c4c5;
        border-radius: 18px;
        color: #212529;
    }

    /* Boton seguro para cerrar sesion */
    .docente-nav-button {
        width: 100%;
        border: none;
        background: transparent;
        text-align: left;
        font: inherit;
        cursor: pointer;
    }

    .docente-logout {
        color: #ffb3b9;
    }

    .docente-logout:hover {
        color: #ffffff;
        background: rgba(220, 53, 69, .22);
    }
</style>

<div class="container-fluid docente-panel">
    <div class="row min-vh-100">

        <!-- MENU LATERAL -->
        <aside class="col-12 col-md-3 col-lg-2 docente-sidebar p-4">

            <div class="text-center mb-4">
                <div class="mb-2">
                    <i class="bi bi-mortarboard-fill fs-2 text-white"></i>
                </div>

                <h5 class="docente-sidebar-title mb-1">
                    PANEL DOCENTE
                </h5>

                <small class="docente-muted">
                    Gestión Académica
                </small>
            </div>

            <hr class="border-secondary mb-4">

            <nav class="d-grid gap-2"
                 aria-label="Menú del docente">

                <a href="{{ route('docente.dashboard') }}"
                   class="docente-nav active"
                   aria-current="page">
                    <i class="bi bi-grid-1x2-fill"></i>
                    Inicio
                </a>

                <a href="{{ route('docente.materias') }}"
                   class="docente-nav">
                    <i class="bi bi-journal-bookmark-fill"></i>
                    Mis Materias
                </a>

                <a href="{{ route('docente.grupos') }}"
                   class="docente-nav">
                    <i class="bi bi-people-fill"></i>
                    Mis Grupos
                </a>

                <a href="{{ route('docente.perfil') }}"
                   class="docente-nav">
                    <i class="bi bi-person-badge-fill"></i>
                    Mi Perfil
                </a>

                <hr class="border-secondary my-3">

                <!-- CERRAR SESION SEGURO -->
                <form action="{{ route('logout.seguro') }}"
                      method="POST"
                      class="m-0">

                    @csrf

                    <button type="submit"
                            class="docente-nav docente-logout docente-nav-button">
                        <i class="bi bi-box-arrow-right"></i>
                        Cerrar sesión
                    </button>
                </form>

            </nav>
        </aside>

        <!-- CONTENIDO PRINCIPAL -->
        <main class="col-12 col-md-9 col-lg-10 docente-main p-4 p-lg-5">

            <!-- ENCABEZADO -->
            <div class="d-flex flex-wrap justify-content-between
                        align-items-center gap-3 mb-4">

                <div>
                    <h2 class="fw-bold mb-1">
                        Panel de Docente
                    </h2>

                    <p class="docente-muted mb-0">
                        Sistema de Gestión Administrativa Escolar
                    </p>
                </div>

                <span class="docente-status">
                    <i class="bi bi-circle-fill me-1"
                       style="font-size: .5rem;"></i>
                    Módulo docente
                </span>
            </div>

            <!-- BIENVENIDA -->
            <section class="docente-welcome mb-5">
                <div class="d-flex flex-wrap align-items-center
                            justify-content-between gap-4">

                    <div>
                        <p class="mb-2 text-white-50">
                            Bienvenido al sistema
                        </p>

                        <h3 class="fw-bold mb-3">
                            ¡Hola, {{ Auth::user()->nombre ?? 'Docente' }}!
                        </h3>

                        <p class="mb-0 text-white-50">
                            Desde aquí podrás consultar tu información
                            académica y acceder a las herramientas
                            disponibles para docentes.
                        </p>
                    </div>

                    <div class="docente-welcome-icon">
                        <i class="bi bi-mortarboard-fill"></i>
                    </div>

                </div>
            </section>

            <!-- ACCESOS RAPIDOS -->
            <div class="mb-4">
                <h4 class="fw-bold mb-1">
                    Accesos rápidos
                </h4>

                <p class="docente-muted mb-0">
                    Explora las secciones de tu panel.
                </p>
            </div>

            <div class="row g-4">

                <!-- MATERIAS -->
                <div class="col-12 col-lg-4" id="mis-materias">
                    <article class="docente-card p-4">

                        <div class="docente-card-icon mb-4">
                            <i class="bi bi-journal-bookmark-fill"></i>
                        </div>

                        <h5 class="fw-bold mb-3">
                            Mis Materias
                        </h5>

                        <p class="docente-muted">
                            Consulta las materias que tienes
                            asignadas durante el ciclo escolar.
                        </p>

                        <span class="docente-status">
                            Próximamente
                        </span>

                    </article>
                </div>

                <!-- GRUPOS -->
                <div class="col-12 col-lg-4" id="mis-grupos">
                    <article class="docente-card p-4">

                        <div class="docente-card-icon mb-4">
                            <i class="bi bi-people-fill"></i>
                        </div>

                        <h5 class="fw-bold mb-3">
                            Mis Grupos
                        </h5>

                        <p class="docente-muted">
                            Visualiza los grupos que tienes
                            asignados y su información académica.
                        </p>

                        <span class="docente-status">
                            Próximamente
                        </span>

                    </article>
                </div>

                <!-- PERFIL -->
                <div class="col-12 col-lg-4" id="mi-perfil">
                    <article class="docente-card p-4">

                        <div class="docente-card-icon mb-4">
                            <i class="bi bi-person-vcard-fill"></i>
                        </div>

                        <h5 class="fw-bold mb-3">
                            Mi Perfil
                        </h5>

                        <p class="docente-muted">
                            Consulta tu información personal
                            registrada en el sistema escolar.
                        </p>

                        <span class="docente-status">
                            Próximamente
                        </span>

                    </article>
                </div>

            </div>

            <!-- INFORMACION GENERAL -->
            <section class="docente-info p-4 mt-5">
                <div class="d-flex align-items-start gap-3">

                    <div class="docente-card-icon flex-shrink-0">
                        <i class="bi bi-info-circle-fill"></i>
                    </div>

                    <div>
                        <h5 class="fw-bold mb-2">
                            Información del módulo
                        </h5>

                        <p class="docente-muted mb-0">
                            Este espacio está diseñado para facilitar
                            la consulta de materias, grupos y datos
                            personales de los docentes de la
                            Escuela Preparatoria Número 5 del Estado.
                            Las funciones académicas se habilitarán
                            conforme avancemos en su desarrollo.
                        </p>
                    </div>

                </div>
            </section>

        </main>
    </div>
</div>

@endsection

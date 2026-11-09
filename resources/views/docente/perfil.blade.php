
@extends('layouts.app')

@section('titulo', 'Mi Perfil')

@section('usuario_header')
    <span class="fs-6">
        <i class="bi bi-person-circle me-1"></i>
        {{ Auth::user()->nombre ?? 'Docente' }}
    </span>
@endsection

@section('contenido')

@php
    $usuario = Auth::user();
    $docente = $docente ?? null;
@endphp

<style>
    .perfil-panel {
        min-height: 100vh;
        background-color: #f4f6f9; 
        color: #212529; 
    }

    .perfil-sidebar {
        background: #212529; 
        border-right: 1px solid #343a40;
    }

    .perfil-nav {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 13px 16px;
        border-radius: 12px;
        color: #adb5bd;
        text-decoration: none;
        transition: background .2s, color .2s;
    }

    .perfil-nav:hover {
        background: rgba(255, 255, 255, .1);
        color: #ffffff;
    }

    .perfil-nav.active {
        background: #fd7e14; 
        color: #ffffff;
        font-weight: 600;
    }

    .perfil-muted {
        color: #6c757d; 
    }

    .perfil-heading {
        background: #212529;
        border: 1px solid #343a40;
        border-radius: 20px;
        padding: 30px;
        color: #ffffff;
    }

    .perfil-avatar {
        width: 82px;
        height: 82px;
        border-radius: 22px;
        background: rgba(253, 126, 20, 0.15);
        color: #fd7e14; 
        display: grid;
        place-items: center;
        font-size: 2.5rem;
    }

    .perfil-card {
        background: #d1d5d6; 
        border: 1px solid #c0c4c5;
        border-radius: 20px;
        padding: 30px;
        height: 100%;
        color: #212529;
    }

    .perfil-card-title {
        display: flex;
        align-items: center;
        gap: 12px;
        font-size: 1.25rem;
        font-weight: 700;
        margin-bottom: 25px;
        color: #212529;
    }

    .perfil-card-icon {
        width: 46px;
        height: 46px;
        border-radius: 13px;
        background: #212529;
        color: #fd7e14;
        display: grid;
        place-items: center;
        font-size: 1.3rem;
    }

    .perfil-field {
        background: #ffffff; 
        border: 1px solid #c0c4c5;
        border-radius: 12px;
        padding: 15px 18px;
        height: 100%;
    }

    .perfil-label {
        display: block;
        color: #6c757d; 
        font-size: .85rem;
        margin-bottom: 8px;
    }

    .perfil-value {
        display: block;
        color: #212529; 
        font-size: 1rem;
        font-weight: 600;
        overflow-wrap: anywhere;
    }

    .perfil-status {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        background: rgba(253, 126, 20, 0.15); 
        color: #d95c00; 
        padding: 8px 15px;
        border-radius: 30px;
        font-size: .85rem;
        font-weight: 600;
    }

    .perfil-info {
        background: #d1d5d6;
        border: 1px solid #c0c4c5;
        border-radius: 18px;
        padding: 25px;
        color: #212529;
    }

    @media (max-width: 767.98px) {
        .perfil-heading { padding: 24px; }
        .perfil-card { padding: 22px; }
    }
</style>

<div class="container-fluid perfil-panel">
    <div class="row min-vh-100">

        <!-- MENÚ LATERAL -->
        <aside class="col-12 col-md-3 col-lg-2 perfil-sidebar p-4">

            <div class="text-center mb-4">

                <i class="bi bi-mortarboard-fill fs-2"></i>

                <h5 class="fw-bold mt-2 mb-1">
                    PANEL DOCENTE
                </h5>

                <small class="perfil-muted">
                    Gestión Académica
                </small>

            </div>

            <hr class="border-secondary my-4">

            <nav class="d-grid gap-2"
                 aria-label="Menú del docente">

                <!-- INICIO -->
                <a href="{{ route('docente.dashboard') }}"
                   class="perfil-nav">
                    <i class="bi bi-grid-1x2-fill"></i>
                    Inicio
                </a>

                <!-- MIS MATERIAS -->
                <a href="{{ route('docente.materias') }}"
                   class="perfil-nav">
                    <i class="bi bi-journal-bookmark-fill"></i>
                    Mis Materias
                </a>

                <!-- MIS GRUPOS -->
                <a href="{{ route('docente.grupos') }}"
                   class="perfil-nav">
                    <i class="bi bi-people-fill"></i>
                    Mis Grupos
                </a>

                <!-- MI PERFIL -->
                <a href="{{ route('docente.perfil') }}"
                   class="perfil-nav active"
                   aria-current="page">
                    <i class="bi bi-person-badge-fill"></i>
                    Mi Perfil
                </a>

                <hr class="border-secondary my-3">

                <!-- CERRAR SESIÓN -->
                <a href="{{ route('logout') }}"
                   class="perfil-nav text-danger">
                    <i class="bi bi-box-arrow-right"></i>
                    Cerrar sesión
                </a>

            </nav>

        </aside>

        <!-- CONTENIDO PRINCIPAL -->
        <main class="col-12 col-md-9 col-lg-10 p-4 p-lg-5"
              id="perfil-contenido">

            <!-- TÍTULO -->
            <div class="mb-4">
                <h2 class="fw-bold mb-2">
                    Mi Perfil
                </h2>

                <p class="perfil-muted mb-0">
                    Consulta tu información personal y laboral
                    registrada en el sistema escolar.
                </p>
            </div>

            <!-- ENCABEZADO MORADO -->
            <section class="perfil-heading mb-4">

                <div class="d-flex flex-wrap align-items-center gap-4">

                    <div class="perfil-avatar">
                        <i class="bi bi-person-fill"></i>
                    </div>

                    <div class="flex-grow-1">

                        <p class="text-white-50 mb-2">
                            Perfil del docente
                        </p>

                        <h3 class="fw-bold mb-2">
                            {{ $usuario?->nombre ?? 'Docente' }}
                        </h3>

                        <p class="text-white-50 mb-0">
                            Escuela Preparatoria Número 5 del Estado
                        </p>

                    </div>

                    <span class="perfil-status">
                        <i class="bi bi-person-badge"></i>
                        Docente
                    </span>

                </div>

            </section>

            <!-- TARJETAS DE INFORMACIÓN -->
            <div class="row g-4">

                <!-- INFORMACIÓN PERSONAL -->
                <div class="col-12 col-xl-6">

                    <section class="perfil-card">

                        <h4 class="perfil-card-title">
                            <span class="perfil-card-icon">
                                <i class="bi bi-person-vcard-fill"></i>
                            </span>
                            Información Personal
                        </h4>

                        <div class="row g-3">

                            <div class="col-12">
                                <div class="perfil-field">
                                    <span class="perfil-label">
                                        Nombre(s)
                                    </span>

                                    <span class="perfil-value">
                                        {{ $docente?->nombres ?? $usuario?->nombre ?? 'No registrado' }}
                                    </span>
                                </div>
                            </div>

                            <div class="col-12 col-sm-6">
                                <div class="perfil-field">
                                    <span class="perfil-label">
                                        Apellido paterno
                                    </span>

                                    <span class="perfil-value">
                                        {{ $docente?->apellido_paterno ?? 'No registrado' }}
                                    </span>
                                </div>
                            </div>

                            <div class="col-12 col-sm-6">
                                <div class="perfil-field">
                                    <span class="perfil-label">
                                        Apellido materno
                                    </span>

                                    <span class="perfil-value">
                                        {{ $docente?->apellido_materno ?? 'No registrado' }}
                                    </span>
                                </div>
                            </div>

                            <div class="col-12">
                                <div class="perfil-field">
                                    <span class="perfil-label">
                                        CURP
                                    </span>

                                    <span class="perfil-value">
                                        {{ $docente?->curp ?? 'No registrado' }}
                                    </span>
                                </div>
                            </div>

                        </div>

                    </section>

                </div>

                <!-- INFORMACIÓN LABORAL -->
                <div class="col-12 col-xl-6">

                    <section class="perfil-card">

                        <h4 class="perfil-card-title">
                            <span class="perfil-card-icon">
                                <i class="bi bi-briefcase-fill"></i>
                            </span>
                            Información Laboral
                        </h4>

                        <div class="row g-3">

                            <div class="col-12 col-sm-6">
                                <div class="perfil-field">
                                    <span class="perfil-label">
                                        Número de empleado
                                    </span>

                                    <span class="perfil-value">
                                        {{ $docente?->numero_empleado ?? 'No registrado' }}
                                    </span>
                                </div>
                            </div>

                            <div class="col-12 col-sm-6">
                                <div class="perfil-field">
                                    <span class="perfil-label">
                                        Estado
                                    </span>

                                    <span class="perfil-value">
                                        {{ $docente?->estado ?? 'No registrado' }}
                                    </span>
                                </div>
                            </div>

                            <div class="col-12">
                                <div class="perfil-field">
                                    <span class="perfil-label">
                                        Cédula profesional
                                    </span>

                                    <span class="perfil-value">
                                        {{ $docente?->cedula_profesional ?? 'No registrada' }}
                                    </span>
                                </div>
                            </div>

                            <div class="col-12">
                                <div class="perfil-field">
                                    <span class="perfil-label">
                                        Correo electrónico
                                    </span>

                                    <span class="perfil-value">
                                        {{ $usuario?->correo ?? 'No registrado' }}
                                    </span>
                                </div>
                            </div>

                        </div>

                    </section>

                </div>

            </div>

            <!-- INFORMACIÓN ADICIONAL -->
            <section class="perfil-info mt-4">

                <div class="d-flex align-items-start gap-3">

                    <div class="perfil-card-icon flex-shrink-0">
                        <i class="bi bi-info-circle-fill"></i>
                    </div>

                    <div>
                        <h5 class="fw-bold mb-2">
                            Información de tu perfil
                        </h5>

                        <p class="perfil-muted mb-0">
                            Este apartado permite consultar los
                            datos personales y laborales del docente.
                            La información disponible dependerá de
                            los datos registrados y vinculados
                            a su cuenta en el sistema escolar.
                        </p>
                    </div>

                </div>

            </section>

        </main>

    </div>
</div>

@endsection

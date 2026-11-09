@extends('layouts.app')

@section('titulo', 'Panel del Alumno')

@section('usuario_header')
    <span class="fs-6">
        <i class="bi bi-person-circle me-1"></i>
        {{ Auth::user()->nombre ?? 'Alumno' }}
    </span>
@endsection

@section('contenido')

<style>
    .alumno-panel {
        min-height: 100vh;
        background-color: #f4f6f9; 
    }

    .alumno-sidebar {
        background: #212529;
        border-right: 1px solid #343a40;
    }

    .alumno-sidebar-title {
        font-weight: 800;
        letter-spacing: 1.2px;
        color: #ffffff;
    }

    .alumno-nav {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 13px 16px;
        border-radius: 12px;
        color: #adb5bd;
        text-decoration: none;
        transition: background .2s, color .2s;
    }

    .alumno-nav:hover {
        background: rgba(255, 255, 255, .1);
        color: #ffffff;
    }

    .alumno-nav.active {
        background: #0d6efd;
        color: #ffffff;
        font-weight: 600;
    }

    .alumno-nav i {
        font-size: 1.15rem;
    }

    .alumno-welcome {
        background: #212529;
        border-radius: 20px;
        padding: 32px;
        color: #ffffff;
    }

    .alumno-welcome-icon {
        width: 66px;
        height: 66px;
        border-radius: 18px;
        background: rgba(255, 255, 255, .14);
        display: grid;
        place-items: center;
        font-size: 2rem;
    }

    .alumno-card {
        background: #d1d5d6;
        border: 1px solid #c0c4c5;
        border-radius: 18px;
        height: 100%;
        color: #212529;
        text-decoration: none;
        transition: transform .2s, box-shadow .2s;
        display: block;
    }

    .alumno-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 10px 20px rgba(0,0,0,0.1);
        color: #212529;
    }

    .alumno-card-icon {
        width: 52px;
        height: 52px;
        border-radius: 14px;
        display: grid;
        place-items: center;
        background: #212529;
        color: #ffffff;
        font-size: 1.5rem;
    }

    .alumno-muted {
        color: #6c757d;
    }

    .alumno-status {
        display: inline-block;
        background: rgba(13, 110, 253, .15);
        color: #0d6efd;
        border-radius: 30px;
        padding: 6px 12px;
        font-size: .78rem;
        font-weight: 600;
    }

    .alumno-logout {
        color: #ffb3b9;
    }

    .alumno-logout:hover {
        color: #ffffff;
        background: rgba(220, 53, 69, .22);
    }
</style>

<div class="container-fluid alumno-panel">
    <div class="row min-vh-100">

        <aside class="col-12 col-md-3 col-lg-2 alumno-sidebar p-4">

            <div class="text-center mb-4">
                <div class="mb-2">
                    <i class="bi bi-mortarboard-fill fs-2 text-white"></i>
                </div>
                <h5 class="alumno-sidebar-title mb-1">
                    SGAE PREPA 5
                </h5>
                <small class="text-white-50">
                    Panel del Alumno
                </small>
            </div>

            <hr class="border-secondary mb-4">

            <nav class="d-grid gap-2" aria-label="Menú del alumno">

                <a href="#" class="alumno-nav active" aria-current="page">
                    <i class="bi bi-grid-1x2-fill"></i>
                    Inicio
                </a>

                <a href="{{ route('alumno.materias') }}" class="alumno-nav">
                    <i class="bi bi-journal-bookmark-fill"></i>
                    Materias
                </a>

                <a href="{{ route('alumno.documentos') }}" class="alumno-nav">
                    <i class="bi bi-folder-fill"></i>
                    Mi Expediente
                </a>

                <a href="{{ route('alumno.solicitudes') }}" class="alumno-nav">
                    <i class="bi bi-cash-coin"></i>
                    Trámites y Pagos
                </a>

                <hr class="border-secondary my-3">

                <a href="{{ route('logout') }}" class="alumno-nav alumno-logout">
                    <i class="bi bi-box-arrow-right"></i>
                    Cerrar sesión
                </a>

            </nav>

        </aside>

        <main class="col-12 col-md-9 col-lg-10 p-4 p-lg-5">

            <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
                <div>
                    <h2 class="fw-bold mb-1 text-dark">Panel de Control</h2>
                    <p class="alumno-muted mb-0">Sistema de Gestión Administrativa Escolar</p>
                </div>
                <span class="alumno-status">
                    <i class="bi bi-circle-fill me-1" style="font-size: .5rem;"></i>
                    Módulo Estudiantil
                </span>
            </div>

            <section class="alumno-welcome mb-5 shadow-sm">
                <div class="d-flex flex-wrap align-items-center justify-content-between gap-4">
                    <div>
                        <p class="mb-2 text-white-50">Bienvenido al sistema</p>
                        <h3 class="fw-bold mb-3">¡Hola, {{ Auth::user()->nombre ?? 'Alumno' }}!</h3>
                        <p class="mb-0 text-white-50">
                            Desde aquí podrás consultar tus calificaciones, subir tus documentos <br>
                            y generar tus solicitudes de pago para los trámites escolares.
                        </p>
                    </div>
                    <div class="alumno-welcome-icon">
                        <i class="bi bi-person-workspace"></i>
                    </div>
                </div>
            </section>

            <div class="mb-4">
                <h4 class="fw-bold mb-1 text-dark">Accesos rápidos</h4>
                <p class="alumno-muted mb-0">Explora las secciones de tu panel.</p>
            </div>

            <div class="row g-4">
                
                <div class="col-12 col-lg-4">
                    <a href="{{ route('alumno.materias') }}" class="alumno-card p-4">
                        <div class="alumno-card-icon mb-4">
                            <i class="bi bi-journal-bookmark-fill"></i>
                        </div>
                        <h5 class="fw-bold mb-3">Materias y Calificaciones</h5>
                        <p class="alumno-muted mb-0 text-dark">
                            Consulta tu tira de materias, horarios y calificaciones del semestre en curso.
                        </p>
                    </a>
                </div>

                <div class="col-12 col-lg-4">
                    <a href="{{ route('alumno.documentos') }}" class="alumno-card p-4">
                        <div class="alumno-card-icon mb-4">
                            <i class="bi bi-folder-fill"></i>
                        </div>
                        <h5 class="fw-bold mb-3">Mi Expediente Digital</h5>
                        <p class="alumno-muted mb-0 text-dark">
                            Sube tus documentos personales (Acta, CURP) para validación de control escolar.
                        </p>
                    </a>
                </div>

                <div class="col-12 col-lg-4">
                    <a href="{{ route('alumno.solicitudes') }}" class="alumno-card p-4">
                        <div class="alumno-card-icon mb-4">
                            <i class="bi bi-cash-coin"></i>
                        </div>
                        <h5 class="fw-bold mb-3">Trámites y Pagos</h5>
                        <p class="alumno-muted mb-0 text-dark">
                            Genera solicitudes para constancias, inscripciones o exámenes extraordinarios.
                        </p>
                    </a>
                </div>

            </div>

        </main>

    </div>
</div>

@endsection
@extends('layouts.app')

@section('titulo', 'Panel de Administrador')

@section('usuario_header')
    <span class="fs-6">
        <i class="bi bi-person-circle me-1"></i>
        {{ Auth::user()->nombre ?? 'Administrador' }}
    </span>
@endsection

@section('contenido')

<style>
    .admin-panel {
        min-height: 100vh;
        background-color: #f4f6f9; 
    }

    /* MENÚ LATERAL */
    .admin-sidebar {
        background: #212529;
        border-right: 1px solid #343a40;
    }

    .admin-sidebar-title {
        font-weight: 800;
        letter-spacing: 1.2px;
        color: #ffffff;
    }

    .admin-nav {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 13px 16px;
        border-radius: 12px;
        color: #adb5bd;
        text-decoration: none;
        transition: background .2s, color .2s;
    }

    .admin-nav:hover {
        background: rgba(255, 255, 255, .1);
        color: #ffffff;
    }

    .admin-nav.active {
        background: #198754;
        color: #ffffff;
        font-weight: 600;
    }

    .admin-nav-sub {
        font-size: 0.9rem;
        padding: 8px 16px 8px 40px;
        color: #8a939b;
        text-decoration: none;
        display: block;
        transition: color .2s;
    }

    .admin-nav-sub:hover {
        color: #ffffff;
    }

    .admin-nav i { font-size: 1.15rem; }

    /* ENCABEZADO Y TARJETAS */
    .admin-welcome {
        background: #212529;
        border-radius: 20px;
        padding: 32px;
        color: #ffffff;
    }

    .admin-welcome-icon {
        width: 66px;
        height: 66px;
        border-radius: 18px;
        background: rgba(25, 135, 84, 0.15); 
        color: #198754; 
        display: grid;
        place-items: center;
        font-size: 2rem;
    }

    .admin-card {
        background: #d1d5d6;
        border: 1px solid #c0c4c5;
        border-radius: 18px;
        height: 100%;
        color: #212529;
        text-decoration: none;
        transition: transform .2s, box-shadow .2s, border-color .2s;
        display: block;
    }

    .admin-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 10px 20px rgba(0,0,0,0.1);
        border-color: #198754; 
        color: #212529;
    }

    .admin-card-icon {
        width: 52px;
        height: 52px;
        border-radius: 14px;
        display: grid;
        place-items: center;
        background: #212529;
        color: #198754; 
        font-size: 1.5rem;
    }

    .admin-muted { color: #6c757d; }

    .admin-status {
        display: inline-block;
        background: rgba(25, 135, 84, .15);
        color: #198754;
        border-radius: 30px;
        padding: 6px 12px;
        font-size: .78rem;
        font-weight: 600;
    }

    .admin-logout { color: #ffb3b9; }
    .admin-logout:hover { color: #ffffff; background: rgba(220, 53, 69, .22); }
</style>

<div class="container-fluid admin-panel">
    <div class="row min-vh-100">

        <!-- MENÚ LATERAL COMPLETO -->
        <aside class="col-12 col-md-3 col-lg-2 admin-sidebar p-4">

            <div class="text-center mb-4">
                <div class="mb-2">
                    <i class="bi bi-shield-lock-fill fs-2 text-white"></i>
                </div>
                <h5 class="admin-sidebar-title mb-1">SGAE PREPA 5</h5>
                <small class="text-white-50">Panel Administrativo</small>
            </div>

            <hr class="border-secondary mb-4">

            <nav class="d-grid gap-2" aria-label="Menú del administrador">

                <a href="{{ route('admin.dashboard') }}" class="admin-nav active" aria-current="page">
                    <i class="bi bi-grid-1x2-fill"></i> Inicio
                </a>

                <a href="{{ route('admin.usuarios') }}" class="admin-nav">
                    <i class="bi bi-people-fill"></i> Usuarios
                </a>

                <a href="{{ route('admin.alumnos') }}" class="admin-nav">
                    <i class="bi bi-mortarboard-fill"></i> Alumnos
                </a>

                <a href="{{ route('admin.docentes') }}" class="admin-nav">
                    <i class="bi bi-person-workspace"></i> Docentes
                </a>

                <a href="{{ route('admin.materias') }}" class="admin-nav">
                    <i class="bi bi-journal-text"></i> Materias
                </a>

                <a href="{{ route('admin.pagos') }}" class="admin-nav">
                    <i class="bi bi-cash-coin"></i> Seg. de Pagos
                </a>

                <div class="mt-2 mb-1 ps-3 text-white-50 small fw-bold" style="letter-spacing: 0.5px;">REVISIONES</div>
                
                <a href="{{ route('admin.revision.inscripciones') }}" class="admin-nav-sub">
                    <i class="bi bi-arrow-return-right me-1"></i> Inscripciones
                </a>
                <a href="{{ route('admin.revision.reinscripciones') }}" class="admin-nav-sub">
                    <i class="bi bi-arrow-return-right me-1"></i> Reinscripciones
                </a>
                <a href="{{ route('admin.revision.egresos') }}" class="admin-nav-sub">
                    <i class="bi bi-arrow-return-right me-1"></i> Egresos
                </a>

                <hr class="border-secondary my-3">

                <a href="{{ route('logout') }}" class="admin-nav admin-logout">
                    <i class="bi bi-power"></i> Cerrar sesión
                </a>

            </nav>

        </aside>

        <main class="col-12 col-md-9 col-lg-10 p-4 p-lg-5">

            <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
                <div>
                    <h2 class="fw-bold mb-1 text-dark">Buenos días, {{ Auth::user()->nombre ?? 'ADMIN' }}</h2>
                    <p class="admin-muted mb-0">Bienvenido al Sistema de Gestión Escolar</p>
                </div>
                <span class="admin-status">
                    <i class="bi bi-circle-fill me-1" style="font-size: .5rem;"></i>
                    Módulo Administrativo
                </span>
            </div>

            <div class="row g-4 mb-5">
                
                <div class="col-12 col-md-6 col-lg-3">
                    <a href="{{ route('admin.usuarios') }}" class="admin-card p-4 text-center">
                        <div class="admin-card-icon mx-auto mb-3">
                            <i class="bi bi-people-fill"></i>
                        </div>
                        <h6 class="fw-bold mb-0">USUARIOS</h6>
                    </a>
                </div>

                <div class="col-12 col-md-6 col-lg-3">
                    <a href="{{ route('admin.alumnos') }}" class="admin-card p-4 text-center">
                        <div class="admin-card-icon mx-auto mb-3">
                            <i class="bi bi-mortarboard-fill"></i>
                        </div>
                        <h6 class="fw-bold mb-0">ALUMNOS</h6>
                    </a>
                </div>

                <div class="col-12 col-md-6 col-lg-3">
                    <a href="{{ route('admin.docentes') }}" class="admin-card p-4 text-center">
                        <div class="admin-card-icon mx-auto mb-3">
                            <i class="bi bi-person-workspace"></i>
                        </div>
                        <h6 class="fw-bold mb-0">DOCENTES</h6>
                    </a>
                </div>

                <div class="col-12 col-md-6 col-lg-3">
                    <a href="{{ route('admin.pagos') }}" class="admin-card p-4 text-center">
                        <div class="admin-card-icon mx-auto mb-3">
                            <i class="bi bi-cash-coin"></i>
                        </div>
                        <h6 class="fw-bold mb-0 text-uppercase" style="font-size: 0.9rem;">Seguimiento de Pagos</h6>
                    </a>
                </div>

                <div class="col-12 col-md-6">
                    <div class="card h-100 border-0 shadow-sm" style="background-color: #c0c4c5; border-radius: 18px;">
                        <div class="card-body text-center p-4">
                            <h6 class="fw-bold text-dark mb-3 border-bottom border-secondary pb-3">EXPEDIENTES Y DOCUMENTOS</h6>
                            <div class="d-flex flex-wrap justify-content-around mt-4 gap-2">
                                <a href="{{ route('admin.revision.inscripciones') }}" class="btn btn-dark fw-bold px-3">Inscripciones</a>
                                <a href="{{ route('admin.revision.reinscripciones') }}" class="btn btn-dark fw-bold px-3">Reinscripciones</a>
                                <a href="{{ route('admin.revision.egresos') }}" class="btn btn-dark fw-bold px-3">Egresos</a>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-md-6">
                    <a href="{{ route('admin.materias') }}" class="admin-card p-4 text-center d-flex flex-column justify-content-center">
                        <div class="admin-card-icon mx-auto mb-3">
                            <i class="bi bi-journal-text"></i>
                        </div>
                        <h5 class="fw-bold mb-0">MATERIAS</h5>
                    </a>
                </div>

            </div>

            <!-- ACTIVIDAD RECIENTE -->
            <div class="mt-4">
                <h6 class="fw-bold text-dark mb-3" style="border-bottom: 2px dashed #c0c4c5; padding-bottom: 8px; letter-spacing: 1px;">
                    ACTIVIDAD RECIENTE
                </h6>
                <div class="admin-muted small">
                    <i class="bi bi-check-circle-fill text-success me-1"></i>
                    <i>El sistema está funcionando correctamente. No hay alertas nuevas.</i>
                </div>
            </div>

        </main>
    </div>
</div>
@endsection
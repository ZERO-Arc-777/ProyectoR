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
    /* VARIABLES DE ENTORNO DOCENTE */
    .grupos-panel {
        min-height: 100vh;
        background-color: #f4f6f9; /* Fondo gris claro general de la app */
        color: #212529;
    }

    /* SIDEBAR */
    .grupos-sidebar {
        background: #212529; 
        border-right: 1px solid #343a40;
    }

    .grupos-nav {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 13px 16px;
        border-radius: 12px;
        color: #adb5bd;
        text-decoration: none;
        transition: .2s;
    }

    .grupos-nav:hover {
        background: rgba(255, 255, 255, .1);
        color: #ffffff;
    }

    .grupos-nav.active {
        background: #fd7e14;
        color: #ffffff;
        font-weight: 600;
    }

    .grupos-muted {
        color: #6c757d;
    }

    /* BANNER SUPERIOR (Resumen) */
    .grupos-resumen {
        background: #d1d5d6; /* Fondo gris claro */
        border: 1px solid #c0c4c5;
        border-radius: 20px; /* Bordes redondeados amplios */
        padding: 25px;
        color: #212529;
    }

    .grupos-resumen-icon {
        width: 56px;
        height: 56px;
        background: #212529; /* Ícono oscuro grande */
        color: #fd7e14;
        border-radius: 15px;
        display: grid;
        place-items: center;
        font-size: 1.7rem;
    }

    /* TARJETA DE GRUPO COMO ENLACE */
    a.grupos-card {
        display: block; /* Necesario para que el enlace abarque todo como tarjeta */
        text-decoration: none;
        background: #d1d5d6; /* Fondo gris claro sólido */
        border: 1px solid #c0c4c5;
        border-radius: 20px; /* Bordes muy redondeados */
        padding: 26px;
        height: 100%;
        color: #212529;
        transition: transform .2s, box-shadow .2s, border-color .2s;
    }

    a.grupos-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 10px 20px rgba(0,0,0,0.1);
        border-color: #fd7e14;
        color: #212529;
    }

    /* ÍCONO SUPERIOR IZQUIERDO */
    .grupos-icon {
        width: 62px;
        height: 62px;
        background: #212529; /* Cuadro gris muy oscuro */
        color: #fd7e14; /* Ícono naranja */
        border-radius: 17px;
        display: grid;
        place-items: center;
        font-size: 1.8rem;
    }

    /* ETIQUETA SEMESTRE SUPERIOR DERECHA */
    .grupos-etiqueta {
        display: inline-flex;
        align-items: center;
        padding: 7px 13px;
        border-radius: 30px; /* Bordes en forma de píldora */
        background: rgba(253, 126, 20, 0.15); /* Fondo naranja claro */
        color: #d95c00; /* Texto naranja oscuro */
        font-size: .8rem;
        font-weight: 700;
    }

    /* DETALLES DE LA TARJETA (Íconos pequeños) */
    .grupos-detalle {
        display: flex;
        align-items: center;
        gap: 12px;
        margin-top: 15px;
        color: #212529;
        font-size: 0.95rem;
    }

    .grupos-detalle i {
        color: #fd7e14; /* Íconos naranjas pequeños */
        font-size: 1.2rem;
    }

    /* ESTADO VACÍO */
    .grupos-vacio {
        background: #ffffff;
        border: 1px dashed #c0c4c5;
        border-radius: 20px;
        padding: 55px 25px;
        text-align: center;
        color: #212529;
    }

    .grupos-vacio-icon {
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

<div class="container-fluid grupos-panel">

    <div class="row min-vh-100">

        <!-- MENÚ LATERAL -->
        <aside class="col-12 col-md-3 col-lg-2 grupos-sidebar p-4">
            <div class="text-center mb-4">
                <i class="bi bi-mortarboard-fill fs-2 text-white"></i>
                <h5 class="fw-bold mt-2 mb-1 text-white">
                    PANEL DOCENTE
                </h5>
                <small class="grupos-muted">
                    Gestión Académica
                </small>
            </div>

            <hr class="border-secondary my-4">

            <nav class="d-grid gap-2" aria-label="Menú del docente">
                <a href="{{ route('docente.dashboard') }}" class="grupos-nav">
                    <i class="bi bi-grid-1x2-fill"></i> Inicio
                </a>
                <a href="{{ route('docente.materias') }}" class="grupos-nav">
                    <i class="bi bi-journal-bookmark-fill"></i> Mis Materias
                </a>
                <a href="{{ route('docente.grupos') }}" class="grupos-nav active" aria-current="page">
                    <i class="bi bi-people-fill"></i> Mis Grupos
                </a>
                <a href="{{ route('docente.perfil') }}" class="grupos-nav">
                    <i class="bi bi-person-badge-fill"></i> Mi Perfil
                </a>
                <hr class="border-secondary my-3">
                <a href="{{ route('logout') }}" class="grupos-nav text-danger">
                    <i class="bi bi-box-arrow-right"></i> Cerrar sesión
                </a>
            </nav>
        </aside>

        <!-- CONTENIDO PRINCIPAL -->
        <main class="col-12 col-md-9 col-lg-10 p-4 p-lg-5">

            <!-- BANNER SUPERIOR DE RESUMEN (Instrucción 2) -->
            <section class="grupos-resumen mb-5">
                <div class="d-flex align-items-center gap-3">
                    <div class="grupos-resumen-icon">
                        <i class="bi bi-people-fill"></i>
                    </div>
                    <div>
                        <p class="grupos-muted mb-1 text-uppercase fw-bold" style="font-size: 0.85rem;">
                            Total de asignaciones de grupos
                        </p>
                        <h2 class="fw-bold mb-0 text-dark">
                            {{ $asignaciones->count() }}
                        </h2>
                    </div>
                </div>
            </section>

            <!-- TÍTULO DE SECCIÓN -->
            <div class="mb-4">
                <h3 class="fw-bold text-dark mb-1">
                    Mis grupos asignados
                </h3>
                <p class="grupos-muted">Selecciona un grupo para ver el listado de alumnos y detalles.</p>
            </div>

            <!-- CUADRÍCULA DE TARJETAS (Instrucción 3: Grid Bootstrap) -->
            <div class="row row-cols-1 row-cols-md-2 row-cols-xl-3 g-4">
                
                @forelse($asignaciones as $asignacion)
                    <div class="col">
                        <!-- LA TARJETA ES EL ENLACE COMPLETO (Instrucción 4) -->
                        <a href="{{ route('docente.grupos.listas', $asignacion->id_asignacion) }}" class="grupos-card">
                            
                            <!-- ENCABEZADO DE LA TARJETA (Ícono y Etiqueta) -->
                            <div class="d-flex justify-content-between align-items-start gap-3 mb-4">
                                <div class="grupos-icon">
                                    <i class="bi bi-people-fill"></i>
                                </div>
                                <span class="grupos-etiqueta">
                                    Semestre {{ $asignacion->materia->semestre ?? 'N/A' }}
                                </span>
                            </div>

                            <!-- TÍTULO PRINCIPAL -->
                            <h4 class="fw-bold mb-2">
                                Grupo {{ $asignacion->materia->semestre ?? '' }}° {{ $asignacion->grupo }}
                            </h4>
                            <p class="grupos-muted mb-4">
                                Grupo académico asignado.
                            </p>

                            <!-- LÍNEA SEPARADORA TENUE (Instrucción 5) -->
                            <hr class="border-secondary opacity-25">

                            <!-- DETALLES CON ÍCONOS NARANJAS -->
                            <div class="grupos-detalle">
                                <i class="bi bi-journal-bookmark-fill"></i>
                                <span class="fw-medium">{{ $asignacion->materia->nombre ?? 'Materia no disponible' }}</span>
                            </div>

                            <div class="grupos-detalle">
                                <i class="bi bi-calendar3"></i>
                                <span>{{ $asignacion->periodo ?? 'Periodo no disponible' }}</span>
                            </div>

                            <div class="grupos-detalle">
                                <i class="bi bi-hash"></i>
                                <span>Asignación #{{ $asignacion->id_asignacion }}</span>
                            </div>
                            
                        </a>
                    </div>
                @empty
                    <!-- ESTADO VACÍO -->
                    <div class="col-12">
                        <div class="grupos-vacio">
                            <div class="grupos-vacio-icon">
                                <i class="bi bi-people"></i>
                            </div>
                            <h4 class="fw-bold mb-3">No tienes grupos asignados</h4>
                            <p class="grupos-muted mx-auto mb-0" style="max-width: 530px;">
                                Actualmente no cuentas con grupos asignados en este periodo escolar.
                            </p>
                        </div>
                    </div>
                @endforelse

            </div>

        </main>
    </div>
</div>

@endsection
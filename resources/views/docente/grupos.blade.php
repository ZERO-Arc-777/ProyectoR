
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
        background-color: #f4f6f9; 
        color: #212529;
    }

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

    .grupos-heading {
        background: #212529;
        border: 1px solid #343a40;
        border-radius: 20px;
        padding: 30px;
        color: #ffffff;
    }

    .grupos-resumen {
        background: #d1d5d6;
        border: 1px solid #c0c4c5;
        border-radius: 20px;
        padding: 25px;
        color: #212529;
    }

    .grupos-resumen-icon {
        width: 56px;
        height: 56px;
        background: #212529; 
        color: #fd7e14;
        border-radius: 15px;
        display: grid;
        place-items: center;
        font-size: 1.7rem;
    }

    .grupos-card {
        background: #d1d5d6;
        border: 1px solid #c0c4c5;
        border-radius: 20px;
        padding: 26px;
        height: 100%;
        color: #212529;
        transition: transform .2s, box-shadow .2s, border-color .2s;
    }

    .grupos-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 10px 20px rgba(0,0,0,0.1);
        border-color: #fd7e14;
    }

    .grupos-icon {
        width: 62px;
        height: 62px;
        background: #212529;
        color: #fd7e14;
        border-radius: 17px;
        display: grid;
        place-items: center;
        font-size: 1.8rem;
    }

    .grupos-etiqueta {
        display: inline-flex;
        align-items: center;
        padding: 7px 13px;
        border-radius: 30px;
        background: rgba(253, 126, 20, 0.15);
        color: #d95c00;
        font-size: .8rem;
        font-weight: 600;
    }

    .grupos-detalle {
        display: flex;
        align-items: center;
        gap: 12px;
        margin-top: 17px;
        color: #212529;
    }

    .grupos-detalle i {
        color: #fd7e14;
        font-size: 1.2rem;
    }

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

    .grupos-info {
        background: #d1d5d6;
        border: 1px solid #c0c4c5;
        border-radius: 18px;
        padding: 25px;
        color: #212529;
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
                    Consulta los grupos y las materias
                    que tienes asignados.
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

            <!-- RESUMEN -->
            <section class="grupos-resumen mb-4">

                <div class="d-flex align-items-center gap-3">

                    <div class="grupos-resumen-icon">
                        <i class="bi bi-people-fill"></i>
                    </div>

                    <div>
                        <p class="grupos-muted mb-1">
                            Total de asignaciones de grupos
                        </p>

                        <h3 class="fw-bold mb-0">
                            {{ $asignaciones->count() }}
                        </h3>
                    </div>

                </div>

            </section>

            <!-- GRUPOS ASIGNADOS -->
            <section aria-labelledby="titulo-grupos">

                <h4 class="fw-bold mb-4" id="titulo-grupos">
                    <i class="bi bi-collection-fill me-2"></i>
                    Mis grupos asignados
                </h4>

                <div class="row g-4">

                    @forelse ($asignaciones as $asignacion)

                        <div class="col-12 col-lg-6 col-xxl-4">

                            <article class="grupos-card">

                                <!-- ICONO Y SEMESTRE -->
                                <div class="d-flex justify-content-between
                                            align-items-start gap-3 mb-4">

                                    <div class="grupos-icon">
                                        <i class="bi bi-people-fill"></i>
                                    </div>

                                    <span class="grupos-etiqueta">
                                        Semestre {{ $asignacion->semestre }}
                                    </span>

                                </div>

                                <!-- NOMBRE DEL GRUPO -->
                                <h4 class="fw-bold mb-2">
                                    Grupo {{ $asignacion->semestre }}°
                                    {{ $asignacion->grupo }}
                                </h4>

                                <p class="grupos-muted mb-3">
                                    Grupo académico asignado.
                                </p>

                                <hr class="border-secondary">

                                <!-- MATERIA -->
                                <div class="grupos-detalle">
                                    <i class="bi bi-book-half"></i>

                                    <span>
                                        {{ $asignacion->materia?->nombre
                                           ?? 'Materia no disponible' }}
                                    </span>
                                </div>

                                <!-- PERIODO ESCOLAR -->
                                <div class="grupos-detalle">
                                    <i class="bi bi-calendar3"></i>

                                    <span>
                                        {{ $asignacion->periodo?->nombre
                                           ?? 'Periodo no disponible' }}
                                    </span>
                                </div>

                                <!-- IDENTIFICADOR -->
                                <div class="grupos-detalle">
                                    <i class="bi bi-hash"></i>

                                    <span>
                                        Asignación
                                        #{{ $asignacion->id_asignacion }}
                                    </span>
                                </div>

                            </article>

                        </div>

                    @empty

                        <!-- SIN GRUPOS ASIGNADOS -->
                        <div class="col-12">

                            <div class="grupos-vacio">

                                <div class="grupos-vacio-icon">
                                    <i class="bi bi-people"></i>
                                </div>

                                <h4 class="fw-bold mb-3">
                                    Aún no tienes grupos asignados
                                </h4>

                                <p class="grupos-muted mx-auto mb-0"
                                   style="max-width: 530px;">
                                    Cuando se registren grupos a tu nombre,
                                    podrás consultarlos aquí junto con
                                    su semestre, materia y periodo escolar.
                                </p>

                            </div>

                        </div>

                    @endforelse

                </div>

            </section>

            <!-- INFORMACIÓN GENERAL -->
            <section class="grupos-info mt-4">

                <div class="d-flex align-items-start gap-3">

                    <div class="grupos-resumen-icon flex-shrink-0">
                        <i class="bi bi-info-circle-fill"></i>
                    </div>

                    <div>

                        <h5 class="fw-bold mb-2">
                            Información de grupos
                        </h5>

                        <p class="grupos-muted mb-0">
                            Aquí se muestran las asignaciones académicas
                            asociadas a tu cuenta docente.
                            Si necesitas modificar tus grupos,
                            comunícate con el área administrativa.
                        </p>

                    </div>

                </div>

            </section>

        </main>

    </div>

</div>

@endsection

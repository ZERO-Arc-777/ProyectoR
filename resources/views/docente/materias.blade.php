
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

    /* MENÚ LATERAL */
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

    .materias-muted {
        color: #b9b2c9;
    }

    /* ENCABEZADO PRINCIPAL */
    .materias-heading {
        background: linear-gradient(110deg, #44217c, #712fc3);
        border-radius: 20px;
        padding: 30px;
    }

    /* RESUMEN */
    .materias-resumen {
        background: #211a33;
        border: 1px solid rgba(255,255,255,.09);
        border-radius: 20px;
        padding: 25px;
    }

    .materias-resumen-icon {
        width: 56px;
        height: 56px;
        border-radius: 15px;
        display: grid;
        place-items: center;
        background: rgba(157,92,255,.16);
        color: #c49bff;
        font-size: 1.7rem;
    }

    /* TARJETAS DE MATERIAS */
    .materias-card {
        background: #211a33;
        border: 1px solid rgba(255,255,255,.09);
        border-radius: 20px;
        padding: 26px;
        height: 100%;
        transition: transform .2s, border-color .2s;
    }

    .materias-card:hover {
        transform: translateY(-4px);
        border-color: rgba(167,115,255,.45);
    }

    .materias-icon {
        width: 60px;
        height: 60px;
        background: rgba(157,92,255,.16);
        color: #c49bff;
        border-radius: 16px;
        display: grid;
        place-items: center;
        font-size: 1.7rem;
    }

    .materias-nombre {
        color: #ffffff;
        font-weight: 700;
        margin-bottom: 8px;
    }

    .materias-detalle {
        display: flex;
        align-items: center;
        gap: 10px;
        color: #c8bfd8;
        margin-top: 14px;
    }

    .materias-detalle i {
        color: #b68aff;
    }

    /* ESTADOS */
    .materias-estado {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 7px 13px;
        border-radius: 30px;
        font-size: .8rem;
        font-weight: 600;
    }

    .materias-estado.activa {
        background: rgba(34,197,94,.14);
        color: #86efac;
    }

    .materias-estado.inactiva {
        background: rgba(245,158,11,.14);
        color: #fcd34d;
    }

    /* SIN MATERIAS */
    .materias-vacio {
        background: #211a33;
        border: 1px solid rgba(255,255,255,.09);
        border-radius: 20px;
        padding: 55px 25px;
        text-align: center;
    }

    .materias-vacio-icon {
        width: 80px;
        height: 80px;
        border-radius: 22px;
        display: grid;
        place-items: center;
        margin: 0 auto 22px;
        background: rgba(157,92,255,.16);
        color: #c49bff;
        font-size: 2.2rem;
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

            <!-- TÍTULO -->
            <div class="mb-4">
                <h2 class="fw-bold">
                    Mis Materias
                </h2>

                <p class="materias-muted">
                    Consulta las asignaturas que tienes asignadas.
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

            <!-- RESUMEN DE MATERIAS -->
            <section class="materias-resumen mb-4">

                <div class="d-flex align-items-center gap-3">

                    <div class="materias-resumen-icon">
                        <i class="bi bi-journals"></i>
                    </div>

                    <div>
                        <p class="materias-muted mb-1">
                            Total de materias asignadas
                        </p>

                        <h3 class="fw-bold mb-0">
                            {{ $materias->count() }}
                        </h3>
                    </div>

                </div>

            </section>

            <!-- LISTADO DE MATERIAS -->
            <section aria-labelledby="titulo-materias">

                <h4 class="fw-bold mb-4" id="titulo-materias">
                    <i class="bi bi-book-half me-2"></i>
                    Mis asignaturas
                </h4>

                <div class="row g-4">

                    @forelse ($materias as $materia)

                        <div class="col-12 col-lg-6 col-xxl-4">

                            <article class="materias-card">

                                <!-- ICONO Y ESTADO -->
                                <div class="d-flex justify-content-between
                                            align-items-start gap-3 mb-4">

                                    <div class="materias-icon">
                                        <i class="bi bi-book"></i>
                                    </div>

                                    @if ($materia->estado === 'ACTIVA')

                                        <span class="materias-estado activa">
                                            <i class="bi bi-check-circle-fill"></i>
                                            Activa
                                        </span>

                                    @else

                                        <span class="materias-estado inactiva">
                                            <i class="bi bi-dash-circle-fill"></i>
                                            Inactiva
                                        </span>

                                    @endif

                                </div>

                                <!-- NOMBRE DE LA MATERIA -->
                                <h5 class="materias-nombre">
                                    {{ $materia->nombre }}
                                </h5>

                                <p class="materias-muted mb-3">
                                    Asignatura registrada en el
                                    sistema académico.
                                </p>

                                <hr class="border-secondary">

                                <!-- SEMESTRE -->
                                <div class="materias-detalle">
                                    <i class="bi bi-calendar3"></i>

                                    <span>
                                        Semestre {{ $materia->semestre }}
                                    </span>
                                </div>

                                <!-- IDENTIFICADOR -->
                                <div class="materias-detalle">
                                    <i class="bi bi-hash"></i>

                                    <span>
                                        Materia #{{ $materia->id_materia }}
                                    </span>
                                </div>

                            </article>

                        </div>

                    @empty

                        <!-- SIN MATERIAS ASIGNADAS -->
                        <div class="col-12">

                            <div class="materias-vacio">

                                <div class="materias-vacio-icon">
                                    <i class="bi bi-journal-x"></i>
                                </div>

                                <h4 class="fw-bold mb-3">
                                    Aún no tienes materias asignadas
                                </h4>

                                <p class="materias-muted mx-auto mb-0"
                                   style="max-width: 520px;">
                                    Cuando se registren materias a tu nombre,
                                    podrás consultarlas aquí junto con su
                                    semestre y estado.
                                </p>

                            </div>

                        </div>

                    @endforelse

                </div>

            </section>

        </main>

    </div>
</div>

@endsection

@extends('layouts.app')

@section('titulo', 'Gestión de Docentes')

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

    .admin-nav-sub:hover { color: #ffffff; }
    .admin-nav i { font-size: 1.15rem; }

    .admin-muted { color: #6c757d; }

    .admin-logout { color: #ffb3b9; }
    .admin-logout:hover { color: #ffffff; background: rgba(220, 53, 69, .22); }

    /* TARJETA CON ENCABEZADO OSCURO */
    .admin-form-card {
        background: #d1d5d6;
        border: 1px solid #c0c4c5;
        border-radius: 18px;
        overflow: hidden;
    }

    .admin-form-card .card-header {
        border-radius: 0;
        border-bottom: 1px solid #343a40;
    }

    /* BARRA SUPERIOR Y BOTONES DE FILTRO */
    .docentes-search-form {
        flex: 1 1 250px;
        max-width: 360px;
        min-width: 210px;
    }

    .filter-pill {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        padding: 5px 12px;
        border-radius: 50rem;
        font-size: 0.82rem;
        font-weight: 700;
        white-space: nowrap;
        text-decoration: none;
        border: 2px solid transparent;
        background-color: #ffffff;
        transition: transform .15s, background-color .15s, color .15s, box-shadow .15s;
    }

    .filter-pill:hover {
        transform: translateY(-1px);
    }

    .filter-pill-success {
        border-color: #198754;
        color: #198754;
    }
    .filter-pill-success:hover,
    .filter-pill-success.active {
        background-color: #198754;
        color: #ffffff;
    }

    .filter-pill-danger {
        border-color: #dc3545;
        color: #dc3545;
    }
    .filter-pill-danger:hover,
    .filter-pill-danger.active {
        background-color: #dc3545;
        color: #ffffff;
    }
</style>

<div class="container-fluid admin-panel">
    <div class="row min-vh-100">

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

                <a href="{{ route('admin.dashboard') }}" class="admin-nav">
                    <i class="bi bi-grid-1x2-fill"></i> Inicio
                </a>

                <a href="{{ route('admin.usuarios') }}" class="admin-nav">
                    <i class="bi bi-people-fill"></i> Usuarios
                </a>

                <a href="{{ route('admin.alumnos') }}" class="admin-nav">
                    <i class="bi bi-mortarboard-fill"></i> Alumnos
                </a>

                <a href="{{ route('admin.docentes') }}" class="admin-nav active" aria-current="page">
                    <i class="bi bi-person-workspace"></i> Docentes
                </a>

                <a href="{{ route('admin.materias') }}" class="admin-nav">
                    <i class="bi bi-journal-text"></i> Materias
                </a>

                <a href="{{ route('admin.periodo.index') }}" class="admin-nav">
                    <i class="bi bi-calendar-range-fill"></i> Periodo Escolar
                </a>

                <a href="{{ route('admin.pagos') }}" class="admin-nav">
                    <i class="bi bi-cash-coin"></i> Pagos
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

                <!-- CERRAR SESIÓN SEGURO -->
                <form action="{{ route('logout.seguro') }}" method="POST" class="m-0">
                    @csrf

                    <button type="submit"
                            class="admin-nav admin-logout"
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
                        <i class="bi bi-power"></i>
                        <span>Cerrar sesión</span>
                    </button>
                </form>

            </nav>
        </aside>

        <main class="col-12 col-md-9 col-lg-10 p-4 p-lg-5">

            <div class="mb-4">
                <h2 class="fw-bold mb-1 text-dark">Gestión de Docentes</h2>
                <p class="admin-muted mb-0">Administra los perfiles y el estado de la plantilla docente.</p>
            </div>

            @if(session('success'))
                <div class="alert alert-success fw-bold rounded-3 shadow-sm border-0 mb-4">
                    <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
                </div>
            @endif

            @php
                $estadoActual = $estado ?? request('estado');
                $buscarActual = $buscar ?? request('buscar', '');

                $construirUrlFiltro = function ($nuevoEstado) use ($buscarActual) {
                    return route('admin.docentes', array_filter([
                        'buscar' => $buscarActual !== '' ? $buscarActual : null,
                        'estado' => $nuevoEstado,
                    ], fn ($valor) => $valor !== null && $valor !== ''));
                };
            @endphp

            <div class="card admin-form-card shadow-sm mb-4">

                <div class="card-header bg-dark text-white py-3">
                    <h6 class="fw-bold mb-0">
                        <i class="bi bi-person-workspace me-2"></i>
                        Docentes registrados
                    </h6>
                </div>

                <div class="card-body p-4">

                    <div class="d-flex flex-wrap flex-lg-nowrap justify-content-between align-items-center mb-4 gap-2">
                        <a href="{{ route('admin.docentes.create') }}"
                           class="btn btn-dark fw-bold px-3 text-nowrap flex-shrink-0 shadow-sm"
                           style="font-size: 0.88rem;">
                            <i class="bi bi-plus-circle me-1"></i> Registrar Nuevo Docente
                        </a>

                        <!-- BOTONES DE FILTRO POR ESTADO -->
                        <div class="d-flex flex-wrap align-items-center justify-content-center gap-2">
                            <a href="{{ $construirUrlFiltro($estadoActual === 'ACTIVO' ? null : 'ACTIVO') }}"
                               class="filter-pill filter-pill-success shadow-sm {{ $estadoActual === 'ACTIVO' ? 'active' : '' }}"
                               title="Filtrar por Docentes Activos">
                                <i class="bi bi-check-circle-fill"></i>
                                Activos
                            </a>

                            <a href="{{ $construirUrlFiltro($estadoActual === 'INACTIVO' ? null : 'INACTIVO') }}"
                               class="filter-pill filter-pill-danger shadow-sm {{ $estadoActual === 'INACTIVO' ? 'active' : '' }}"
                               title="Filtrar por Docentes Inactivos">
                                <i class="bi bi-x-circle-fill"></i>
                                Inactivos
                            </a>
                        </div>

                        <form action="{{ route('admin.docentes') }}" method="GET" class="d-flex docentes-search-form">
                            @if($estadoActual)
                                <input type="hidden" name="estado" value="{{ $estadoActual }}">
                            @endif

                            <div class="input-group shadow-sm">
                                <input type="text"
                                       name="buscar"
                                       class="form-control border-0"
                                       style="font-size: 0.88rem;"
                                       placeholder="Buscar por N° de empleado, nombre o CURP..."
                                       value="{{ $buscarActual }}">
                                <button type="submit" class="btn text-white fw-bold px-3" style="background-color: #198754;">
                                    <i class="bi bi-search"></i>
                                </button>
                                @if($buscarActual !== '')
                                    <a href="{{ route('admin.docentes', array_filter(['estado' => $estadoActual])) }}" class="btn btn-secondary px-2 text-white" title="Limpiar búsqueda">
                                        <i class="bi bi-x-lg"></i>
                                    </a>
                                @endif
                            </div>
                        </form>
                    </div>

                    <div class="table-responsive bg-white rounded-3 border border-secondary shadow-sm">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light text-muted small">
                                <tr>
                                    <th class="ps-4">NÚM. EMPLEADO</th>
                                    <th>NOMBRE COMPLETO</th>
                                    <th>CURP</th>
                                    <th class="text-center">ESTADO</th>
                                    <th class="text-center">ACCIONES</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($docentes as $docente)
                                    <tr>
                                        <td class="fw-bold text-dark ps-4">
                                            {{ $docente->numero_empleado }}
                                        </td>
                                        <td class="fw-bold text-secondary">{{ $docente->apellido_paterno }} {{ $docente->apellido_materno }} {{ $docente->nombres }}</td>
                                        <td class="text-muted text-uppercase">{{ $docente->curp }}</td>
                                        <td class="text-center">
                                            @if($docente->estado == 'ACTIVO')
                                                <span class="badge bg-success px-3 py-2 rounded-pill"><i class="bi bi-check-circle-fill me-1"></i> Activo</span>
                                            @elseif($docente->estado == 'INACTIVO')
                                                <span class="badge bg-warning text-dark px-3 py-2 rounded-pill"><i class="bi bi-exclamation-circle-fill me-1"></i> Inactivo</span>
                                            @else
                                                <span class="badge bg-danger px-3 py-2 rounded-pill"><i class="bi bi-x-circle-fill me-1"></i> Baja</span>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            <a href="{{ route('admin.docentes.edit', $docente->id_docente) }}" class="btn btn-sm btn-outline-dark fw-bold px-3 rounded-pill">
                                                <i class="bi bi-pencil-square me-1"></i> Editar
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center py-5">
                                            <i class="bi bi-search fs-1 text-muted opacity-50 d-block mb-2"></i>
                                            <span class="fw-bold text-muted">
                                                @if($buscarActual !== '' || $estadoActual)
                                                    No se encontraron docentes con los filtros aplicados.
                                                @else
                                                    Todavía no hay docentes registrados.
                                                @endif
                                            </span>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="d-flex justify-content-end mt-4">
                        {{ $docentes->appends(request()->query())->links() }}
                    </div>

                </div>
            </div>

        </main>
    </div>
</div>
@endsection
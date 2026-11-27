@extends('layouts.app')

@section('titulo', 'Gestión de Usuarios')

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

    .admin-card-content {
        background: #d1d5d6;
        border: 1px solid #c0c4c5;
        border-radius: 18px;
    }

    .admin-muted { color: #6c757d; }

    .admin-logout { color: #ffb3b9; }
    .admin-logout:hover { color: #ffffff; background: rgba(220, 53, 69, .22); }

    /* BARRA SUPERIOR Y BOTONES DE FILTRO */
    .users-search-form {
        flex: 1 1 200px;
        max-width: 260px;
        min-width: 175px;
    }

    .filter-pill {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        padding: 5px 10px;
        border-radius: 50rem;
        font-size: 0.8rem;
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

    .filter-pill-dark {
        border-color: #212529;
        color: #212529;
    }
    .filter-pill-dark:hover,
    .filter-pill-dark.active {
        background-color: #212529;
        color: #ffffff;
    }

    .filter-pill-warning {
        border-color: #ffc107;
        color: #212529;
    }
    .filter-pill-warning .filter-dot {
        color: #e0a800;
    }
    .filter-pill-warning:hover,
    .filter-pill-warning.active {
        background-color: #ffc107;
        color: #212529;
    }
    .filter-pill-warning:hover .filter-dot,
    .filter-pill-warning.active .filter-dot {
        color: #212529;
    }

    .filter-pill-primary {
        border-color: #0d6efd;
        color: #0d6efd;
    }
    .filter-pill-primary:hover,
    .filter-pill-primary.active {
        background-color: #0d6efd;
        color: #ffffff;
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

    .filter-divider {
        width: 1px;
        height: 22px;
        background-color: #9da3a8;
        margin: 0 2px;
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

                <a href="{{ route('admin.usuarios') }}" class="admin-nav active" aria-current="page">
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

            <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
                <div>
                    <h2 class="fw-bold mb-1 text-dark">Gestión de Usuarios</h2>
                    <p class="admin-muted mb-0">Administra las credenciales de acceso al sistema.</p>
                </div>
            </div>

            @if(session('success'))
                <div class="alert alert-success fw-bold rounded-3 shadow-sm border-0 mb-4">
                    <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
                </div>
            @endif

            @php
                $rolActual = $rol ?? (request()->filled('rol') ? (int) request('rol') : null);
                $estadoActual = $estado ?? request('estado');
                $buscarActual = $buscar ?? request('buscar', '');

                $construirUrlFiltro = function ($nuevoRol, $nuevoEstado) use ($buscarActual) {
                    return route('admin.usuarios', array_filter([
                        'buscar' => $buscarActual !== '' ? $buscarActual : null,
                        'rol' => $nuevoRol,
                        'estado' => $nuevoEstado,
                    ], fn ($valor) => $valor !== null && $valor !== ''));
                };
            @endphp

            <div class="card admin-card-content shadow-sm p-4">

                <!-- BARRA SUPERIOR EN UNA SOLA LÍNEA -->
                <div class="d-flex flex-wrap flex-lg-nowrap justify-content-between align-items-center mb-4 gap-2">
                    <a href="{{ route('admin.usuarios.create') }}"
                       class="btn btn-dark fw-bold px-3 text-nowrap flex-shrink-0 shadow-sm"
                       style="font-size: 0.85rem;">
                        <i class="bi bi-plus-circle me-1"></i> Crear Nuevo Usuario
                    </a>

                    <!-- BOTONES DE FILTRO POR ROL Y ESTADO -->
                    <div class="d-flex flex-wrap align-items-center justify-content-center gap-1">
                        <a href="{{ $construirUrlFiltro($rolActual === 1 ? null : 1, $estadoActual) }}"
                           class="filter-pill filter-pill-dark shadow-sm {{ $rolActual === 1 ? 'active' : '' }}"
                           title="Filtrar por Administradores">
                            <i class="bi {{ $rolActual === 1 ? 'bi-check-lg' : 'bi-shield-fill' }}"></i>
                            Admin
                        </a>

                        <a href="{{ $construirUrlFiltro($rolActual === 2 ? null : 2, $estadoActual) }}"
                           class="filter-pill filter-pill-warning shadow-sm {{ $rolActual === 2 ? 'active' : '' }}"
                           title="Filtrar por Docentes">
                            <i class="bi {{ $rolActual === 2 ? 'bi-check-lg' : 'bi-person-workspace' }} filter-dot"></i>
                            Docente
                        </a>

                        <a href="{{ $construirUrlFiltro($rolActual === 3 ? null : 3, $estadoActual) }}"
                           class="filter-pill filter-pill-primary shadow-sm {{ $rolActual === 3 ? 'active' : '' }}"
                           title="Filtrar por Alumnos">
                            <i class="bi {{ $rolActual === 3 ? 'bi-check-lg' : 'bi-mortarboard-fill' }}"></i>
                            Alumno
                        </a>

                        <span class="filter-divider d-none d-sm-inline-block" aria-hidden="true"></span>

                        <a href="{{ $construirUrlFiltro($rolActual, $estadoActual === 'ACTIVO' ? null : 'ACTIVO') }}"
                           class="filter-pill filter-pill-success shadow-sm {{ $estadoActual === 'ACTIVO' ? 'active' : '' }}"
                           title="Filtrar por Usuarios Activos">
                            <i class="bi bi-check-circle-fill"></i>
                            Activos
                        </a>

                        <a href="{{ $construirUrlFiltro($rolActual, $estadoActual === 'INACTIVO' ? null : 'INACTIVO') }}"
                           class="filter-pill filter-pill-danger shadow-sm {{ $estadoActual === 'INACTIVO' ? 'active' : '' }}"
                           title="Filtrar por Usuarios Inactivos">
                            <i class="bi bi-x-circle-fill"></i>
                            Inactivos
                        </a>
                    </div>

                    <form action="{{ route('admin.usuarios') }}" method="GET" class="d-flex users-search-form">
                        @if($rolActual !== null)
                            <input type="hidden" name="rol" value="{{ $rolActual }}">
                        @endif
                        @if($estadoActual)
                            <input type="hidden" name="estado" value="{{ $estadoActual }}">
                        @endif

                        <div class="input-group shadow-sm">
                            <input type="text"
                                   name="buscar"
                                   class="form-control border-0"
                                   style="font-size: 0.85rem;"
                                   placeholder="Buscar nombre o correo..."
                                   value="{{ $buscarActual }}">
                            <button type="submit" class="btn text-white fw-bold px-3" style="background-color: #198754;">
                                <i class="bi bi-search"></i>
                            </button>
                            @if($buscarActual !== '')
                                <a href="{{ route('admin.usuarios', array_filter(['rol' => $rolActual, 'estado' => $estadoActual])) }}" class="btn btn-secondary px-2 text-white" title="Limpiar búsqueda">
                                    <i class="bi bi-x-lg"></i>
                                </a>
                            @endif
                        </div>
                    </form>
                </div>

                <!-- TABLA DE USUARIOS -->
                <div class="table-responsive bg-white rounded-3 border border-secondary shadow-sm">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light text-muted small">
                            <tr>
                                <th class="ps-4">ID</th>
                                <th>NOMBRE</th>
                                <th>CORREO</th>
                                <th class="text-center">ROL</th>
                                <th class="text-center">ESTADO</th>
                                <th class="text-center">ACCIONES</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($usuarios as $usuario)
                                @php
                                    $nombreRol = $usuario->rol?->nombre
                                        ?? $usuario->rol?->nombre_rol
                                        ?? match((int) $usuario->id_rol) {
                                            1 => 'Administrador',
                                            2 => 'Docente',
                                            3 => 'Alumno',
                                            default => 'Sin rol',
                                        };

                                    $claseRol = match((int) $usuario->id_rol) {
                                        1 => 'bg-dark',
                                        2 => 'bg-warning text-dark',
                                        3 => 'bg-primary',
                                        default => 'bg-secondary',
                                    };
                                @endphp
                                <tr>
                                    <td class="fw-bold text-dark ps-4">#{{ $usuario->id_usuario }}</td>
                                    <td class="fw-bold text-dark">{{ $usuario->nombre }}</td>
                                    <td class="text-muted"><i class="bi bi-envelope me-1"></i>{{ $usuario->correo }}</td>
                                    <td class="text-center">
                                        <span class="badge {{ $claseRol }} px-3 py-2 rounded-pill">
                                            {{ $nombreRol }}
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        @if($usuario->estado == 'ACTIVO')
                                            <span class="badge bg-success px-3 py-2 rounded-pill">
                                                <i class="bi bi-check-circle-fill me-1"></i> Activo
                                            </span>
                                        @else
                                            <span class="badge bg-danger px-3 py-2 rounded-pill">
                                                <i class="bi bi-x-circle-fill me-1"></i> Inactivo
                                            </span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        <a href="{{ route('admin.usuarios.edit', $usuario->id_usuario) }}" class="btn btn-sm btn-outline-dark fw-bold px-3 rounded-pill">
                                            <i class="bi bi-pencil-square me-1"></i> Editar
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center py-5">
                                        <i class="bi bi-search fs-1 text-muted opacity-50 d-block mb-2"></i>
                                        <span class="fw-bold text-muted">No se encontraron usuarios con los filtros aplicados.</span>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="d-flex justify-content-end mt-4">
                    {{ $usuarios->appends(request()->query())->links() }}
                </div>

            </div>

        </main>
    </div>
</div>
@endsection
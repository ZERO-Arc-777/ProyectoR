@extends('layouts.app')

@section('titulo', 'Gestión de Alumnos')

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

                <a href="{{ route('admin.alumnos') }}" class="admin-nav active" aria-current="page">
                    <i class="bi bi-mortarboard-fill"></i> Alumnos
                </a>

                <a href="{{ route('admin.docentes') }}" class="admin-nav">
                    <i class="bi bi-person-workspace"></i> Docentes
                </a>

                <a href="{{ route('admin.materias') }}" class="admin-nav">
                    <i class="bi bi-journal-text"></i> Materias / Asig.
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

            <div class="mb-4">
                <h2 class="fw-bold mb-1 text-dark">Gestión de Alumnos</h2>
                <p class="admin-muted mb-0">Inscribe, edita y administra los registros de los estudiantes.</p>
            </div>

            @if(session('success'))
                <div class="alert alert-success fw-bold rounded-3 shadow-sm border-0 mb-4">
                    <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
                </div>
            @endif

            <div class="card admin-card-content shadow-sm p-4">
                
                <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
                    <a href="{{ route('admin.alumnos.create') }}" class="btn btn-dark fw-bold px-4">
                        <i class="bi bi-plus-circle me-1"></i> Inscribir Nuevo Alumno
                    </a>
                    
                    <form action="{{ route('admin.alumnos') }}" method="GET" class="d-flex" style="width: 380px; max-width: 100%;">
                        <div class="input-group shadow-sm">
                            <input type="text" name="buscar" class="form-control border-0" placeholder="Buscar por matrícula, nombre o CURP..." value="{{ $buscar ?? '' }}">
                            <button type="submit" class="btn text-white fw-bold px-3" style="background-color: #198754;">
                                <i class="bi bi-search"></i>
                            </button>
                            @if(request('buscar'))
                                <a href="{{ route('admin.alumnos') }}" class="btn btn-secondary px-3 text-white" title="Limpiar búsqueda">
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
                                <th class="ps-4">MATRÍCULA</th>
                                <th>NOMBRE COMPLETO</th>
                                <th>CURP</th>
                                <th class="text-center">SEMESTRE / GRUPO</th>
                                <th class="text-center">ACCIONES</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($alumnos as $alumno)
                                <tr>
                                    <td class="fw-bold text-dark ps-4">{{ $alumno->matricula }}</td>
                                    <td class="fw-bold text-secondary">{{ $alumno->apellido_paterno }} {{ $alumno->apellido_materno }} {{ $alumno->nombres }}</td>
                                    <td class="text-muted">{{ $alumno->curp }}</td>
                                    <td class="text-center">
                                        <span class="badge bg-light text-dark border border-secondary px-3 py-2 rounded-pill">
                                            {{ $alumno->semestre }}° "{{ $alumno->grupo }}"
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <a href="{{ route('admin.alumnos.edit', $alumno->matricula) }}" class="btn btn-sm btn-outline-dark fw-bold px-3 rounded-pill">
                                            <i class="bi bi-pencil-square me-1"></i> Editar
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center py-5">
                                        <i class="bi bi-search fs-1 text-muted opacity-50 d-block mb-2"></i>
                                        <span class="fw-bold text-muted">No se encontraron alumnos con esa búsqueda.</span>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="d-flex justify-content-end mt-4">
                    {{ $alumnos->appends(request()->query())->links() }}
                </div>

            </div>

        </main>
    </div>
</div>
@endsection
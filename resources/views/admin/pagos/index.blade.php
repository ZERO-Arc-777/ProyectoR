@extends('layouts.app')

@section('titulo', 'Seguimiento de Pagos')

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

                <a href="{{ route('admin.docentes') }}" class="admin-nav">
                    <i class="bi bi-person-workspace"></i> Docentes
                </a>

                <a href="{{ route('admin.materias') }}" class="admin-nav">
                    <i class="bi bi-journal-text"></i> Materias / Asig.
                </a>

                <a href="{{ route('admin.pagos') }}" class="admin-nav active" aria-current="page">
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

            <!-- ENCABEZADO -->
            <div class="mb-4">
                <h2 class="fw-bold mb-1 text-dark">Seguimiento de Pagos</h2>
                <p class="admin-muted mb-0">Gestiona las solicitudes de pago de los estudiantes.</p>
            </div>

            @if(session('success'))
                <div class="alert alert-success fw-bold rounded-3 shadow-sm border-0 mb-4">
                    <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
                </div>
            @endif

            <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
                <ul class="nav nav-pills bg-white p-1 rounded-3 shadow-sm border border-secondary">
                    <li class="nav-item">
                        <a class="nav-link px-4 {{ $estadoFiltro == 'PENDIENTE' ? 'active fw-bold' : 'text-dark' }}" 
                           style="{{ $estadoFiltro == 'PENDIENTE' ? 'background-color: #212529; color: white;' : '' }}"
                           href="{{ route('admin.pagos', ['estado' => 'PENDIENTE']) }}">
                            <i class="bi bi-clock-history me-1"></i> Pendientes
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link px-4 {{ $estadoFiltro == 'PAGADA' ? 'active fw-bold' : 'text-dark' }}" 
                           style="{{ $estadoFiltro == 'PAGADA' ? 'background-color: #198754; color: white;' : '' }}"
                           href="{{ route('admin.pagos', ['estado' => 'PAGADA']) }}">
                            <i class="bi bi-check-circle me-1"></i> Pagadas
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link px-4 {{ $estadoFiltro == 'CANCELADA' ? 'active fw-bold' : 'text-dark' }}" 
                           style="{{ $estadoFiltro == 'CANCELADA' ? 'background-color: #dc3545; color: white;' : '' }}"
                           href="{{ route('admin.pagos', ['estado' => 'CANCELADA']) }}">
                            <i class="bi bi-x-circle me-1"></i> Canceladas
                        </a>
                    </li>
                </ul>

                <form action="{{ route('admin.pagos') }}" method="GET" class="d-flex" style="width: 380px; max-width: 100%;">
                    <input type="hidden" name="estado" value="{{ $estadoFiltro }}">
                    <div class="input-group shadow-sm">
                        <input type="text" name="buscar" class="form-control border-0" placeholder="Buscar folio, matrícula o nombre..." value="{{ $buscar ?? '' }}">
                        <button type="submit" class="btn text-white fw-bold px-3" style="background-color: #198754;">
                            <i class="bi bi-search"></i>
                        </button>
                        @if(request('buscar'))
                            <a href="{{ route('admin.pagos', ['estado' => $estadoFiltro]) }}" class="btn btn-secondary px-3 text-white" title="Limpiar búsqueda">
                                <i class="bi bi-x-lg"></i>
                            </a>
                        @endif
                    </div>
                </form>
            </div>

            <div class="card admin-card-content shadow-sm p-4">
                
                <div class="table-responsive bg-white rounded-3 border border-secondary shadow-sm">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light text-muted small">
                            <tr>
                                <th class="ps-4">FOLIO</th>
                                <th>MATRÍCULA</th>
                                <th>NOMBRE DEL ALUMNO</th>
                                <th>CONCEPTOS SOLICITADOS</th>
                                <th>TOTAL</th>
                                <th class="text-center">ESTADO</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($solicitudes as $solicitud)
                                <tr>
                                    <td class="fw-bold text-dark ps-4">{{ $solicitud->folio }}</td>
                                    <td class="fw-bold text-secondary">{{ $solicitud->alumno->matricula }}</td>
                                    <td class="fw-bold text-dark">{{ $solicitud->alumno->apellido_paterno }} {{ $solicitud->alumno->apellido_materno }} {{ $solicitud->alumno->nombres }}</td>
                                    <td>
                                        <ul class="mb-0 ps-3 list-unstyled">
                                            @foreach($solicitud->detalles as $detalle)
                                                <li>
                                                    <i class="bi bi-dot text-success"></i> 
                                                    {{ $detalle->servicio->concepto ?? 'Servicio no encontrado' }} 
                                                    <strong class="ms-1">${{ number_format($detalle->precio_cobrado, 2) }}</strong>
                                                </li>
                                            @endforeach
                                        </ul>
                                    </td>
                                    <td class="fw-bold text-success fs-6">${{ number_format($solicitud->total_pagar, 2) }}</td>
                                    <td class="text-center px-3" style="width: 220px;">
                                        <form action="{{ route('admin.pagos.updateEstado', $solicitud->id_solicitud) }}" method="POST" class="d-flex align-items-center bg-light rounded-pill p-1 border">
                                            @csrf
                                            @method('PUT')
                                            <select name="estado" class="form-select form-select-sm border-0 bg-transparent fw-bold shadow-none
                                                {{ $solicitud->estado == 'PENDIENTE' ? 'text-warning' : ($solicitud->estado == 'PAGADA' ? 'text-success' : 'text-danger') }}">
                                                <option class="text-dark" value="PENDIENTE" {{ $solicitud->estado == 'PENDIENTE' ? 'selected' : '' }}>Pendiente</option>
                                                <option class="text-dark" value="PAGADA" {{ $solicitud->estado == 'PAGADA' ? 'selected' : '' }}>Pagada</option>
                                                <option class="text-dark" value="CANCELADA" {{ $solicitud->estado == 'CANCELADA' ? 'selected' : '' }}>Cancelada</option>
                                            </select>
                                            <button type="submit" class="btn btn-sm text-white rounded-pill px-3" style="background-color: #212529;">
                                                <i class="bi bi-check-lg"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center py-5">
                                        <i class="bi bi-file-earmark-text fs-1 text-muted opacity-50 d-block mb-2"></i>
                                        <span class="fw-bold text-muted">No hay solicitudes en estado {{ $estadoFiltro }}.</span>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="d-flex justify-content-end mt-4">
                    {{ $solicitudes->appends(request()->query())->links() }}
                </div>

            </div>

        </main>
    </div>
</div>
@endsection
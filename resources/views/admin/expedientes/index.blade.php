@extends('layouts.app')

@section('titulo', $titulo)

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
    
    .admin-nav-sub.active { 
        color: #198754; 
        font-weight: bold;
    }

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

                <a href="{{ route('admin.pagos') }}" class="admin-nav">
                    <i class="bi bi-cash-coin"></i> Seg. de Pagos
                </a>

                <div class="mt-2 mb-1 ps-3 text-white-50 small fw-bold" style="letter-spacing: 0.5px;">REVISIONES</div>
                
                <a href="{{ route('admin.revision.inscripciones') }}" class="admin-nav-sub {{ request()->routeIs('admin.revision.inscripciones') ? 'active' : '' }}">
                    <i class="bi bi-arrow-return-right me-1"></i> Inscripciones
                </a>
                <a href="{{ route('admin.revision.reinscripciones') }}" class="admin-nav-sub {{ request()->routeIs('admin.revision.reinscripciones') ? 'active' : '' }}">
                    <i class="bi bi-arrow-return-right me-1"></i> Reinscripciones
                </a>
                <a href="{{ route('admin.revision.egresos') }}" class="admin-nav-sub {{ request()->routeIs('admin.revision.egresos') ? 'active' : '' }}">
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
                <h2 class="fw-bold mb-1 text-dark">{{ $titulo }}</h2>
                <p class="admin-muted mb-0">Revisión y validación de documentos estudiantiles.</p>
            </div>

            @if(session('success'))
                <div class="alert alert-success fw-bold rounded-3 shadow-sm border-0 mb-4">
                    <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
                </div>
            @endif

            <div class="card admin-card-content shadow-sm p-4">
                
                <div class="d-flex flex-wrap justify-content-end align-items-center mb-4 gap-3">
                    <form action="{{ url()->current() }}" method="GET" class="d-flex" style="width: 380px; max-width: 100%;">
                        <div class="input-group shadow-sm">
                            <input type="text" name="buscar" class="form-control border-0" placeholder="Buscar por matrícula o nombre..." value="{{ $buscar ?? '' }}">
                            <button type="submit" class="btn text-white fw-bold px-3" style="background-color: #198754;">
                                <i class="bi bi-search"></i>
                            </button>
                            @if(request('buscar'))
                                <a href="{{ url()->current() }}" class="btn btn-secondary px-3 text-white" title="Limpiar búsqueda">
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
                                <th>NOMBRE DEL ALUMNO</th>
                                <th>DOCUMENTO SUBIDO</th>
                                <th class="text-center">ESTADO ACTUAL</th>
                                <th class="text-center">ACCIÓN (VALIDAR)</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($documentos as $doc)
                                <tr>
                                    <td class="fw-bold text-dark ps-4">{{ $doc->alumno->matricula }}</td>
                                    <td class="fw-bold text-secondary">
                                        {{ $doc->alumno->apellido_paterno }} {{ $doc->alumno->apellido_materno }} {{ $doc->alumno->nombres }}
                                    </td>
                                    <td>
                                        <span class="d-block fw-bold text-dark mb-2">{{ $doc->tipo_documento }}</span>
                                        <a href="{{ asset('storage/' . $doc->ruta_archivo) }}" target="_blank" class="btn btn-sm btn-outline-dark fw-bold rounded-pill px-3">
                                            <i class="bi bi-file-earmark-pdf-fill me-1 text-danger"></i> Ver PDF
                                        </a>
                                    </td>
                                    <td class="text-center">
                                        @if($doc->estado_validacion == 'PENDIENTE')
                                            <span class="badge bg-warning text-dark px-3 py-2 rounded-pill"><i class="bi bi-clock-history me-1"></i> Pendiente</span>
                                        @elseif($doc->estado_validacion == 'APROBADO')
                                            <span class="badge bg-success px-3 py-2 rounded-pill"><i class="bi bi-check-circle-fill me-1"></i> Aprobado</span>
                                        @else
                                            <span class="badge bg-danger px-3 py-2 rounded-pill mb-1"><i class="bi bi-x-circle-fill me-1"></i> Rechazado</span>
                                            <br><small class="text-muted fst-italic">{{ $doc->observaciones }}</small>
                                        @endif
                                    </td>
                                    <td class="text-center px-3" style="width: 250px;">
                                        <form action="{{ route('admin.documentos.estado', $doc->id_documento) }}" method="POST" class="m-0 bg-light rounded-3 p-2 border">
                                            @csrf
                                            @method('PUT')
                                            <select name="estado_validacion" class="form-select form-select-sm mb-2 fw-bold border-0 shadow-sm" required>
                                                <option value="PENDIENTE" {{ $doc->estado_validacion == 'PENDIENTE' ? 'selected' : '' }}>Pendiente</option>
                                                <option value="APROBADO" {{ $doc->estado_validacion == 'APROBADO' ? 'selected' : '' }}>Aprobar</option>
                                                <option value="RECHAZADO" {{ $doc->estado_validacion == 'RECHAZADO' ? 'selected' : '' }}>Rechazar</option>
                                            </select>
                                            <input type="text" name="observaciones" class="form-control form-control-sm mb-2 border-0 shadow-sm" placeholder="Motivo de rechazo (opcional)" value="{{ $doc->observaciones }}">
                                            <button type="submit" class="btn btn-sm text-white fw-bold w-100 rounded-pill" style="background-color: #212529;">
                                                Actualizar
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center py-5">
                                        <i class="bi bi-folder2-open fs-1 text-muted opacity-50 d-block mb-2"></i>
                                        <span class="fw-bold text-muted">No hay documentos pendientes de revisión en esta bandeja.</span>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="d-flex justify-content-end mt-4">
                    {{ $documentos->appends(request()->query())->links() }}
                </div>

            </div>

        </main>
    </div>
</div>
@endsection
@extends('layouts.app')

@section('titulo', 'Crear Expediente de Alumno')

@section('usuario_header')
    <span class="fs-6">
        <i class="bi bi-person-circle me-1"></i>
        {{ Auth::user()->nombre ?? 'Administrador' }}
    </span>
@endsection

@section('contenido')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

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

    .admin-logout { color: #ffb3b9; }
    .admin-logout:hover { color: #ffffff; background: rgba(220, 53, 69, .22); }
    
    .select2-container--default .select2-selection--single {
        background-color: #fff;
        border: 0 !important;
        border-radius: 0.375rem !important;
        height: calc(3.5rem + 2px) !important;
        padding: 0.75rem 0.75rem !important;
        box-shadow: 0 .125rem .25rem rgba(0,0,0,.075) !important;
    }
    
    .select2-container--default .select2-selection--single .select2-selection__rendered {
        color: #212529 !important;
        line-height: 1.5 !important;
    }
    
    .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: calc(3.5rem + 2px) !important;
        right: 10px !important;
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

            <div class="card shadow-sm border-0 rounded-4 w-100" style="max-width: 750px; background-color: #d1d5d6;">
                <div class="card-header bg-dark text-white text-center py-3" style="border-top-left-radius: 15px; border-top-right-radius: 15px;">
                    <h5 class="mb-0 fw-bold"><i class="bi bi-person-plus-fill me-2"></i>NUEVO EXPEDIENTE DE ALUMNO</h5>
                </div>

                <div class="card-body p-4 p-md-5">
                    
                    @if ($errors->any())
                        <div class="alert alert-danger fw-bold rounded-3 shadow-sm border-0 mb-4">
                            <div class="d-flex align-items-center mb-2">
                                <i class="bi bi-exclamation-triangle-fill me-2 fs-5"></i>
                                <span>Por favor corrige los siguientes errores:</span>
                            </div>
                            <ul class="mb-0 ps-4">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form action="{{ route('admin.alumnos.store') }}" method="POST">
                        @csrf

                        <div class="row">
                            <div class="col-12 mb-3 text-start">
                                <label class="fw-bold text-dark mb-1 ms-1">Vincular a Cuenta de Usuario</label>
                                <select name="id_usuario" id="buscador-usuarios" class="form-select border-0 shadow-sm" required>
                                    <option value="">-- Selecciona una cuenta --</option>
                                    @foreach($usuarios as $user)
                                        <option value="{{ $user->id_usuario }}" {{ old('id_usuario') == $user->id_usuario ? 'selected' : '' }}>
                                            {{ $user->nombre }} ({{ $user->correo }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-12 mb-3 text-start">
                                <label class="fw-bold text-dark mb-1 ms-1">CURP</label>
                                <input type="text" name="curp" class="form-control border-0 shadow-sm text-uppercase fw-bold" maxlength="18" value="{{ old('curp') }}" required>
                            </div>

                            <div class="col-md-12 mb-3 text-start">
                                <label class="fw-bold text-dark mb-1 ms-1">Nombre(s)</label>
                                <input type="text" name="nombres" class="form-control border-0 shadow-sm" value="{{ old('nombres') }}" required>
                            </div>

                            <div class="col-md-6 mb-3 text-start">
                                <label class="fw-bold text-dark mb-1 ms-1">Apellido Paterno</label>
                                <input type="text" name="apellido_paterno" class="form-control border-0 shadow-sm" value="{{ old('apellido_paterno') }}" required>
                            </div>

                            <div class="col-md-6 mb-3 text-start">
                                <label class="fw-bold text-dark mb-1 ms-1">Apellido Materno</label>
                                <input type="text" name="apellido_materno" class="form-control border-0 shadow-sm" value="{{ old('apellido_materno') }}">
                            </div>

                            <div class="col-md-6 mb-4 text-start">
                                <label class="fw-bold text-dark mb-1 ms-1">Semestre</label>
                                <select name="semestre" class="form-select border-0 shadow-sm fw-bold" required>
                                    <option value="1" {{ old('semestre') == 1 ? 'selected' : '' }}>1° Semestre</option>
                                    <option value="2" {{ old('semestre') == 2 ? 'selected' : '' }}>2° Semestre</option>
                                    <option value="3" {{ old('semestre') == 3 ? 'selected' : '' }}>3° Semestre</option>
                                    <option value="4" {{ old('semestre') == 4 ? 'selected' : '' }}>4° Semestre</option>
                                    <option value="5" {{ old('semestre') == 5 ? 'selected' : '' }}>5° Semestre</option>
                                    <option value="6" {{ old('semestre') == 6 ? 'selected' : '' }}>6° Semestre</option>
                                </select>
                            </div>

                            <div class="col-md-6 mb-4 text-start">
                                <label class="fw-bold text-dark mb-1 ms-1">Grupo</label>
                                <select name="grupo" class="form-select border-0 shadow-sm fw-bold" required>
                                    <option value="A" {{ old('grupo') == 'A' ? 'selected' : '' }}>Grupo A</option>
                                    <option value="B" {{ old('grupo') == 'B' ? 'selected' : '' }}>Grupo B</option>
                                </select>
                            </div>
                        </div>

                        <div class="d-flex justify-content-end mt-2 gap-3">
                            <a href="{{ route('admin.alumnos') }}" class="btn btn-outline-dark fw-bold px-4 shadow-sm">Cancelar</a>
                            <button type="submit" class="btn text-white fw-bold px-4 shadow-sm" style="background-color: #198754;">
                                <i class="bi bi-save me-1"></i> Guardar Expediente
                            </button>
                        </div>
                    </form>
                </div>
            </div>

        </main>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
    $(document).ready(function() {
        $('#buscador-usuarios').select2({
            placeholder: "-- Escribe el nombre o correo del usuario --",
            allowClear: true,
            width: '100%'
        });
    });
</script>
@endsection
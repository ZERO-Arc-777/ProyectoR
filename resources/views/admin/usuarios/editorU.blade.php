@extends('layouts.app')

@section('titulo', 'Editar Usuario')

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
                    <h5 class="mb-0 fw-bold"><i class="bi bi-person-gear me-2"></i>EDITAR USUARIO</h5>
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

                    <form action="{{ route('admin.usuarios.update', $usuario->id_usuario) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="row">
                            <div class="col-12 mb-3 text-start">
                                <label class="fw-bold text-dark mb-1 ms-1">Nombre Completo</label>
                                <input type="text" name="nombre" class="form-control border-0 shadow-sm" value="{{ old('nombre', $usuario->nombre) }}" required>
                            </div>

                            <div class="col-12 mb-3 text-start">
                                <label class="fw-bold text-dark mb-1 ms-1">Correo Electrónico</label>
                                <input type="email" name="correo" class="form-control border-0 shadow-sm" value="{{ old('correo', $usuario->correo) }}" required>
                            </div>

                            <div class="col-md-6 mb-3 text-start">
                                <label class="fw-bold text-dark mb-1 ms-1">Rol en el Sistema</label>
                                <select name="id_rol" class="form-select border-0 shadow-sm" required>
                                    @foreach($roles as $rol)
                                        <option value="{{ $rol->id_rol }}" {{ old('id_rol', $usuario->id_rol) == $rol->id_rol ? 'selected' : '' }}>
                                            {{ $rol->nombre }} 
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-6 mb-3 text-start">
                                <label class="fw-bold text-dark mb-1 ms-1">Estado (Baja lógica)</label>
                                <select name="estado" class="form-select border-0 shadow-sm" required>
                                    <option value="ACTIVO" {{ old('estado', $usuario->estado) == 'ACTIVO' ? 'selected' : '' }}>Activo</option>
                                    <option value="INACTIVO" {{ old('estado', $usuario->estado) == 'INACTIVO' ? 'selected' : '' }}>Inactivo</option>
                                </select>
                            </div>

                            <div class="col-12 mb-4 text-start">
                                <label class="fw-bold text-dark mb-1 ms-1">Nueva Contraseña <small class="text-danger fw-normal">(Opcional)</small></label>
                                <input type="password" name="password" class="form-control border-0 shadow-sm" placeholder="Dejar en blanco para mantener la actual">
                            </div>
                        </div>

                        <div class="d-flex justify-content-end mt-2 gap-3">
                            <a href="{{ route('admin.usuarios') }}" class="btn btn-outline-dark fw-bold px-4 shadow-sm">Cancelar</a>
                            <button type="submit" class="btn text-white fw-bold px-4 shadow-sm" style="background-color: #198754;">
                                <i class="bi bi-save me-1"></i> Guardar Cambios
                            </button>
                        </div>
                    </form>
                </div>
            </div>

        </main>
    </div>
</div>
@endsection
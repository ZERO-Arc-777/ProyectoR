@extends('layouts.app')

@section('titulo', 'EDITAR MATERIA')

@section('usuario_header')
    <span class="fs-6">
        <i class="bi bi-person-circle me-1"></i>
        {{ Auth::user()->nombre ?? 'Administrador' }}
    </span>
@endsection

@section('contenido')

<style>
    .admin-panel { min-height: 100vh; background-color: #f4f6f9; }
    .admin-sidebar { background: #212529; border-right: 1px solid #343a40; }
    .admin-sidebar-title { font-weight: 800; letter-spacing: 1.2px; color: #fff; }
    .admin-nav { display:flex; align-items:center; gap:12px; padding:13px 16px; border-radius:12px; color:#adb5bd; text-decoration:none; transition:background .2s,color .2s; }
    .admin-nav:hover { background:rgba(255,255,255,.1); color:#fff; }
    .admin-nav.active { background:#198754; color:#fff; font-weight:600; }
    .admin-nav-sub { font-size:.9rem; padding:8px 16px 8px 40px; color:#8a939b; text-decoration:none; display:block; transition:color .2s; }
    .admin-nav-sub:hover { color:#fff; }
    .admin-nav i { font-size:1.15rem; }
    .admin-card-content { background:#d1d5d6; border:1px solid #c0c4c5; border-radius:18px; }
    .admin-muted { color:#6c757d; }
    .admin-logout { color:#ffb3b9; }
    .admin-logout:hover { color:#fff; background:rgba(220,53,69,.22); }
    .admin-form-card { background:#d1d5d6; border:1px solid #c0c4c5; border-radius:18px; overflow:hidden; }
    .admin-form-card .card-header { border-radius:0; }
</style>

<div class="container-fluid admin-panel">
    <div class="row min-vh-100">

<aside class="col-12 col-md-3 col-lg-2 admin-sidebar p-4">
    <div class="text-center mb-4">
        <div class="mb-2"><i class="bi bi-shield-lock-fill fs-2 text-white"></i></div>
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
        <a href="{{ route('admin.materias') }}" class="admin-nav active" aria-current="page">
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
                <h2 class="fw-bold mb-1 text-dark">Editar Materia</h2>
                <p class="admin-muted mb-0">Actualiza la información y el estado de la materia seleccionada.</p>
            </div>

            <div class="card admin-form-card shadow-sm w-100" style="max-width:750px;">
                <div class="card-header bg-dark text-white text-center py-3">
                    <h5 class="mb-0 fw-bold">
                        <i class="bi bi-pencil-square me-2"></i>MODIFICAR MATERIA
                    </h5>
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

                    <form action="{{ route('admin.materias.update', $materia->id_materia) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="row">
                            <div class="col-12 mb-3 text-start">
                                <label for="nombre" class="fw-bold text-dark mb-1 ms-1">Nombre de la materia</label>
                                <input type="text" id="nombre" name="nombre"
                                       class="form-control border-0 shadow-sm @error('nombre') is-invalid @enderror"
                                       value="{{ old('nombre', $materia->nombre) }}"
                                       placeholder="Ej. MATEMÁTICAS" maxlength="255" required>
                                @error('nombre')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-4 text-start">
                                <label for="semestre" class="fw-bold text-dark mb-1 ms-1">Semestre</label>
                                <select id="semestre" name="semestre"
                                        class="form-select border-0 shadow-sm @error('semestre') is-invalid @enderror" required>
                                    <option value="">-- Selecciona el semestre --</option>
                                    @for($semestre = 1; $semestre <= 6; $semestre++)
                                        <option value="{{ $semestre }}"
                                            {{ old('semestre', $materia->semestre) == $semestre ? 'selected' : '' }}>
                                            {{ $semestre }}° semestre
                                        </option>
                                    @endfor
                                </select>
                                @error('semestre')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-4 text-start">
                                <label for="estado" class="fw-bold text-dark mb-1 ms-1">Estado de la materia</label>
                                <select id="estado" name="estado"
                                        class="form-select border-0 shadow-sm @error('estado') is-invalid @enderror" required>
                                    <option value="ACTIVA" {{ old('estado', $materia->estado) === 'ACTIVA' ? 'selected' : '' }}>
                                        ACTIVA
                                    </option>
                                    <option value="INACTIVA" {{ old('estado', $materia->estado) === 'INACTIVA' ? 'selected' : '' }}>
                                        INACTIVA
                                    </option>
                                </select>
                                @error('estado')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="d-flex flex-wrap justify-content-end mt-2 gap-3">
                            <a href="{{ route('admin.materias') }}" class="btn btn-outline-dark fw-bold px-4 shadow-sm">
                                Cancelar
                            </a>
                            <button type="submit" class="btn text-white fw-bold px-4 shadow-sm" style="background-color:#198754;">
                                <i class="bi bi-save me-1"></i> Actualizar Materia
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </main>
    </div>
</div>
@endsection

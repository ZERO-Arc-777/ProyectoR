@extends('layouts.app')

@section('titulo', 'PERIODO ESCOLAR')

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
        color: #fff;
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
        color: #fff;
    }

    .admin-nav.active {
        background: #198754;
        color: #fff;
        font-weight: 600;
    }

    .admin-nav-sub {
        font-size: .9rem;
        padding: 8px 16px 8px 40px;
        color: #8a939b;
        text-decoration: none;
        display: block;
        transition: color .2s;
    }

    .admin-nav-sub:hover {
        color: #fff;
    }

    .admin-nav i {
        font-size: 1.15rem;
    }

    .admin-muted {
        color: #6c757d;
    }

    .admin-logout {
        color: #ffb3b9;
    }

    .admin-logout:hover {
        color: #fff;
        background: rgba(220, 53, 69, .22);
    }

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

    .periodo-pill-abierto {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        background: rgba(25, 135, 84, .22);
        border: 1px solid rgba(25, 135, 84, .6);
        color: #d1f7e5;
        border-radius: 999px;
        padding: 4px 12px;
        font-size: .82rem;
        font-weight: 600;
    }

    .periodo-pill-cerrado {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        background: rgba(255, 193, 7, .2);
        border: 1px solid rgba(255, 193, 7, .6);
        color: #ffe69c;
        border-radius: 999px;
        padding: 4px 12px;
        font-size: .82rem;
        font-weight: 600;
    }

    .kpi-box {
        background: #ffffff;
        border-radius: 14px;
        border: 1px solid #dee2e6;
        padding: 1rem 1.15rem;
        height: 100%;
    }

    .kpi-number {
        font-size: 1.75rem;
        font-weight: 800;
        line-height: 1.1;
    }

    .section-disabled {
        opacity: 0.72;
    }
</style>

<div class="container-fluid admin-panel">
    <div class="row min-vh-100">

        {{-- MENÚ LATERAL --}}
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
                    <i class="bi bi-grid-1x2-fill"></i>
                    Inicio
                </a>

                <a href="{{ route('admin.usuarios') }}" class="admin-nav">
                    <i class="bi bi-people-fill"></i>
                    Usuarios
                </a>

                <a href="{{ route('admin.alumnos') }}" class="admin-nav">
                    <i class="bi bi-mortarboard-fill"></i>
                    Alumnos
                </a>

                <a href="{{ route('admin.docentes') }}" class="admin-nav">
                    <i class="bi bi-person-workspace"></i>
                    Docentes
                </a>

                <a href="{{ route('admin.materias') }}" class="admin-nav">
                    <i class="bi bi-journal-text"></i>
                    Materias
                </a>

                <a href="{{ route('admin.periodo.index') }}"
                   class="admin-nav active"
                   aria-current="page">
                    <i class="bi bi-calendar-range-fill"></i>
                    Periodo Escolar
                </a>

                <a href="{{ route('admin.pagos') }}" class="admin-nav">
                    <i class="bi bi-cash-coin"></i>
                    Pagos
                </a>

                <div class="mt-2 mb-1 ps-3 text-white-50 small fw-bold" style="letter-spacing: 0.5px;">
                    REVISIONES
                </div>

                <a href="{{ route('admin.revision.inscripciones') }}" class="admin-nav-sub">
                    <i class="bi bi-arrow-return-right me-1"></i>
                    Inscripciones
                </a>

                <a href="{{ route('admin.revision.reinscripciones') }}" class="admin-nav-sub">
                    <i class="bi bi-arrow-return-right me-1"></i>
                    Reinscripciones
                </a>

                <a href="{{ route('admin.revision.egresos') }}" class="admin-nav-sub">
                    <i class="bi bi-arrow-return-right me-1"></i>
                    Egresos
                </a>

                <hr class="border-secondary my-3">

                <form action="{{ route('logout.seguro') }}" method="POST" class="m-0">
                    @csrf
                    <button type="submit"
                            class="admin-nav admin-logout"
                            style="background: none; border: none; cursor: pointer; width: 100%; text-align: left; font: inherit; display: flex; align-items: center; gap: 12px; white-space: nowrap;">
                        <i class="bi bi-power"></i>
                        <span>Cerrar sesión</span>
                    </button>
                </form>

            </nav>
        </aside>

        {{-- CONTENIDO PRINCIPAL --}}
        <main class="col-12 col-md-9 col-lg-10 p-4 p-lg-5">

            {{-- ENCABEZADO --}}
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
                <div>
                    <h2 class="fw-bold mb-1 text-dark">Periodo Escolar</h2>
                    <p class="admin-muted mb-0">
                        Controla la apertura, corrección y cierre oficial de ciclos semestrales.
                    </p>
                </div>

                <div class="d-flex align-items-center gap-2">
                    @if($periodoEstado === 'ABIERTO')
                        <span class="badge bg-dark px-3 py-2 rounded-pill fs-6">
                            <i class="bi bi-calendar-check text-success me-1"></i>
                            {{ $periodoActual }}
                        </span>
                        <span class="badge bg-success px-3 py-2 rounded-pill">
                            CICLO ABIERTO
                        </span>
                    @else
                        <span class="badge bg-warning text-dark px-3 py-2 rounded-pill fw-bold">
                            <i class="bi bi-lock-fill me-1"></i>
                            CICLO CERRADO · LISTO PARA APERTURA
                        </span>
                    @endif
                </div>
            </div>

            {{-- ALERTAS --}}
            @if(session('success'))
                <div class="alert alert-success fw-bold rounded-3 shadow-sm border-0 mb-4">
                    <i class="bi bi-check-circle-fill me-2"></i>
                    {{ session('success') }}
                </div>
            @endif

            @if($errors->any())
                <div class="alert alert-danger fw-bold rounded-3 shadow-sm border-0 mb-4">
                    <div class="d-flex align-items-center mb-2">
                        <i class="bi bi-exclamation-triangle-fill me-2 fs-5"></i>
                        <span>No se pudo completar la operación:</span>
                    </div>
                    <ul class="mb-0 ps-4">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            {{-- ================================================= --}}
            {{-- 1. ABRIR NUEVO CICLO                              --}}
            {{-- ================================================= --}}
            <div class="card admin-form-card shadow-sm mb-4 {{ $periodoEstado === 'ABIERTO' ? 'section-disabled' : '' }}">
                <div class="card-header bg-dark text-white py-3 d-flex flex-wrap justify-content-between align-items-center gap-2">
                    <h6 class="fw-bold mb-0">
                        <i class="bi bi-plus-circle-fill text-success me-2"></i>
                        1. Abrir Nuevo Ciclo Escolar
                    </h6>

                    @if($periodoEstado === 'CERRADO')
                        <span class="periodo-pill-abierto">
                            <i class="bi bi-unlock-fill"></i> Disponible
                        </span>
                    @else
                        <span class="periodo-pill-cerrado">
                            <i class="bi bi-lock-fill"></i> Requiere cerrar el ciclo actual
                        </span>
                    @endif
                </div>

                <div class="card-body p-4">
                    @if($periodoEstado === 'ABIERTO')
                        <p class="small text-dark mb-0">
                            <i class="bi bi-info-circle-fill text-secondary me-1"></i>
                            Actualmente el ciclo <strong>{{ $periodoActual }}</strong> está abierto. Para inaugurar un nuevo periodo escolar primero debes completar la sección <strong>3. Cierre de Ciclo</strong>.
                        </p>
                    @else
                        <form action="{{ route('admin.periodo.abrir') }}" method="POST" id="formAbrirCiclo">
                            @csrf
                            <div class="row g-3 align-items-end">
                                <div class="col-12 col-md-5">
                                    <label for="abrir_ciclo" class="form-label fw-bold text-dark small mb-1">
                                        Semestre / Ciclo
                                    </label>
                                    <select name="ciclo" id="abrir_ciclo" class="form-select border-0 shadow-sm" required>
                                        <option value="Enero - Junio" {{ old('ciclo', $cicloSugerido) === 'Enero - Junio' ? 'selected' : '' }}>
                                            Enero - Junio
                                        </option>
                                        <option value="Agosto - Diciembre" {{ old('ciclo', $cicloSugerido) === 'Agosto - Diciembre' ? 'selected' : '' }}>
                                            Agosto - Diciembre
                                        </option>
                                    </select>
                                </div>

                                <div class="col-12 col-md-4">
                                    <label for="abrir_anio" class="form-label fw-bold text-dark small mb-1">
                                        Año
                                    </label>
                                    <select name="anio" id="abrir_anio" class="form-select border-0 shadow-sm" required>
                                        @foreach($aniosDisponibles as $anio)
                                            <option value="{{ $anio }}" {{ (int) old('anio', $anioSugerido) === $anio ? 'selected' : '' }}>
                                                {{ $anio }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-12 col-md-3">
                                    <button type="submit" class="btn btn-success fw-bold w-100 shadow-sm">
                                        <i class="bi bi-calendar-plus me-1"></i>
                                        Abrir Ciclo
                                    </button>
                                </div>
                            </div>
                        </form>
                    @endif
                </div>
            </div>

            {{-- ================================================= --}}
            {{-- 2. CORREGIR CICLO ACTUAL                          --}}
            {{-- ================================================= --}}
            <div class="card admin-form-card shadow-sm mb-4 {{ $periodoEstado !== 'ABIERTO' ? 'section-disabled' : '' }}">
                <div class="card-header bg-dark text-white py-3 d-flex flex-wrap justify-content-between align-items-center gap-2">
                    <h6 class="fw-bold mb-0">
                        <i class="bi bi-pencil-square text-warning me-2"></i>
                        2. Corregir Ciclo Actual
                    </h6>

                    @if($periodoEstado === 'ABIERTO')
                        <span class="periodo-pill-abierto">
                            <i class="bi bi-shield-check"></i> Sin promoción ni bajas
                        </span>
                    @else
                        <span class="periodo-pill-cerrado">
                            <i class="bi bi-lock-fill"></i> Sin ciclo abierto
                        </span>
                    @endif
                </div>

                <div class="card-body p-4">
                    @if($periodoEstado !== 'ABIERTO')
                        <p class="small text-dark mb-0">
                            <i class="bi bi-info-circle-fill text-secondary me-1"></i>
                            Esta opción se habilita únicamente cuando existe un periodo escolar abierto.
                        </p>
                    @else
                        <p class="small text-dark mb-3">
                            Usa esta sección solo si cometiste un error al nombrar el periodo vigente (<strong>{{ $periodoActual }}</strong>). Renombrará automáticamente el periodo en configuraciones, asignaciones, calificaciones y documentos del ciclo en curso <strong>sin cambiar semestres ni dar de baja alumnos</strong>.
                        </p>

                        <form action="{{ route('admin.periodo.corregir') }}" method="POST" id="formCorregirCiclo">
                            @csrf
                            <div class="row g-3 align-items-end">
                                <div class="col-12 col-md-5">
                                    <label for="corregir_ciclo" class="form-label fw-bold text-dark small mb-1">
                                        Ciclo corregido
                                    </label>
                                    <select name="ciclo" id="corregir_ciclo" class="form-select border-0 shadow-sm" required>
                                        <option value="Enero - Junio" {{ $cicloActualSel === 'Enero - Junio' ? 'selected' : '' }}>
                                            Enero - Junio
                                        </option>
                                        <option value="Agosto - Diciembre" {{ $cicloActualSel === 'Agosto - Diciembre' ? 'selected' : '' }}>
                                            Agosto - Diciembre
                                        </option>
                                    </select>
                                </div>

                                <div class="col-12 col-md-4">
                                    <label for="corregir_anio" class="form-label fw-bold text-dark small mb-1">
                                        Año corregido
                                    </label>
                                    <select name="anio" id="corregir_anio" class="form-select border-0 shadow-sm" required>
                                        @foreach($aniosDisponibles as $anio)
                                            <option value="{{ $anio }}" {{ $anioActualSel === $anio ? 'selected' : '' }}>
                                                {{ $anio }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-12 col-md-3">
                                    <button type="button"
                                            class="btn btn-dark fw-bold w-100 shadow-sm"
                                            data-bs-toggle="modal"
                                            data-bs-target="#modalCorregirCiclo"
                                            id="btnAbrirModalCorregir">
                                        <i class="bi bi-arrow-repeat me-1"></i>
                                        Corregir Nombre
                                    </button>
                                </div>
                            </div>
                        </form>
                    @endif
                </div>
            </div>

            {{-- ================================================= --}}
            {{-- 3. CIERRE DE CICLO                                --}}
            {{-- ================================================= --}}
            <div class="card admin-form-card shadow-sm mb-4 {{ $periodoEstado !== 'ABIERTO' ? 'section-disabled' : '' }}">
                <div class="card-header bg-dark text-white py-3 d-flex flex-wrap justify-content-between align-items-center gap-2">
                    <h6 class="fw-bold mb-0">
                        <i class="bi bi-flag-fill text-danger me-2"></i>
                        3. Cierre de Ciclo Escolar
                    </h6>

                    @if($periodoEstado === 'ABIERTO' && $analisis)
                        @if($analisis['puedeCerrar'])
                            <span class="periodo-pill-abierto">
                                <i class="bi bi-check-circle-fill"></i> Actas completas · Listo para cerrar
                            </span>
                        @else
                            <span class="periodo-pill-cerrado">
                                <i class="bi bi-exclamation-triangle-fill"></i> Bloqueado por pendientes
                            </span>
                        @endif
                    @else
                        <span class="periodo-pill-cerrado">
                            <i class="bi bi-lock-fill"></i> Ciclo cerrado
                        </span>
                    @endif
                </div>

                <div class="card-body p-4">
                    @if($periodoEstado !== 'ABIERTO' || !$analisis)
                        <p class="small text-dark mb-0">
                            <i class="bi bi-info-circle-fill text-secondary me-1"></i>
                            No hay ningún ciclo abierto para cerrar. Abre un nuevo ciclo en la sección 1 para comenzar.
                        </p>
                    @else
                        {{-- RESUMEN NUMÉRICO COMPACTO --}}
                        <div class="row g-3 mb-4">
                            <div class="col-12 col-sm-6 col-xl-3">
                                <div class="kpi-box shadow-sm">
                                    <div class="small text-muted fw-bold text-uppercase mb-1">Promovidos</div>
                                    <div class="kpi-number text-success">{{ count($analisis['alumnosPromovidos']) }}</div>
                                    <div class="small text-muted mt-1">
                                        @if(count($analisis['desglosePromovidos']) > 0)
                                            @foreach($analisis['desglosePromovidos'] as $tramo => $cantidad)
                                                <span class="me-2">{{ $tramo }}: <strong>{{ $cantidad }}</strong></span>
                                            @endforeach
                                        @else
                                            Sin promociones de grado
                                        @endif
                                    </div>
                                </div>
                            </div>

                            <div class="col-12 col-sm-6 col-xl-3">
                                <div class="kpi-box shadow-sm">
                                    <div class="small text-muted fw-bold text-uppercase mb-1">Egresados (6°)</div>
                                    <div class="kpi-number text-dark">{{ count($analisis['alumnosEgresados']) }}</div>
                                    <div class="small text-muted mt-1">Pasan a estado EGRESADO</div>
                                </div>
                            </div>

                            <div class="col-12 col-sm-6 col-xl-3">
                                <div class="kpi-box shadow-sm">
                                    <div class="small text-muted fw-bold text-uppercase mb-1">Candidatos a Baja</div>
                                    <div class="kpi-number text-danger">{{ count($analisis['candidatosBaja']) }}</div>
                                    <div class="small text-muted mt-1">Reprobaron Evaluación Final</div>
                                </div>
                            </div>

                            <div class="col-12 col-sm-6 col-xl-3">
                                <div class="kpi-box shadow-sm">
                                    <div class="small text-muted fw-bold text-uppercase mb-1">Alertas de Pendientes</div>
                                    <div class="kpi-number {{ count($analisis['alertasPendientes']) > 0 ? 'text-warning' : 'text-success' }}">
                                        {{ count($analisis['alertasPendientes']) }}
                                    </div>
                                    <div class="small text-muted mt-1">
                                        {{ count($analisis['alertasPendientes']) > 0 ? 'Bloquea el cierre hasta capturar' : 'Todas las actas capturadas' }}
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- TABLA 1: ALERTAS DE PENDIENTES --}}
                        <div class="mb-4">
                            <h6 class="fw-bold text-dark mb-2">
                                <i class="bi bi-exclamation-diamond-fill text-warning me-1"></i>
                                Alertas de Pendientes ({{ count($analisis['alertasPendientes']) }})
                            </h6>

                            @if(count($analisis['alertasPendientes']) === 0)
                                <div class="alert alert-success border-0 rounded-3 py-2 px-3 small fw-semibold mb-0 shadow-sm">
                                    <i class="bi bi-check-circle-fill me-1"></i>
                                    Todos los alumnos activos tienen sus calificaciones completas para cerrar el periodo.
                                </div>
                            @else
                                <div class="table-responsive bg-white rounded-3 border border-secondary shadow-sm">
                                    <table class="table table-hover table-sm align-middle mb-0">
                                        <thead class="table-light text-muted small">
                                            <tr>
                                                <th class="ps-3">MATRÍCULA</th>
                                                <th>ALUMNO</th>
                                                <th class="text-center">GRUPO</th>
                                                <th>MATERIA</th>
                                                <th>DOCENTE RESPONSABLE</th>
                                                <th>MOTIVO</th>
                                            </tr>
                                        </thead>
                                        <tbody class="small">
                                            @foreach($analisis['alertasPendientes'] as $alerta)
                                                <tr>
                                                    <td class="ps-3 fw-bold">{{ $alerta['matricula'] }}</td>
                                                    <td class="fw-semibold text-dark">{{ $alerta['alumno'] }}</td>
                                                    <td class="text-center">
                                                        <span class="badge bg-dark rounded-pill">{{ $alerta['grupo'] }}</span>
                                                    </td>
                                                    <td>{{ $alerta['materia'] }}</td>
                                                    <td>{{ $alerta['docente'] }}</td>
                                                    <td>
                                                        <span class="badge bg-warning text-dark">{{ $alerta['motivo'] }}</span>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @endif
                        </div>

                        {{-- TABLA 2: CANDIDATOS A BAJA --}}
                        <div class="mb-4">
                            <h6 class="fw-bold text-dark mb-2">
                                <i class="bi bi-person-x-fill text-danger me-1"></i>
                                Candidatos a Baja ({{ count($analisis['candidatosBaja']) }})
                            </h6>

                            @if(count($analisis['candidatosBaja']) === 0)
                                <div class="alert alert-light border rounded-3 py-2 px-3 small text-muted mb-0 shadow-sm">
                                    <i class="bi bi-check2-all text-success me-1"></i>
                                    No hay alumnos con Evaluación Final reprobada en este ciclo.
                                </div>
                            @else
                                <div class="table-responsive bg-white rounded-3 border border-secondary shadow-sm">
                                    <table class="table table-hover table-sm align-middle mb-0">
                                        <thead class="table-light text-muted small">
                                            <tr>
                                                <th class="ps-3">MATRÍCULA</th>
                                                <th>ALUMNO</th>
                                                <th class="text-center">GRUPO</th>
                                                <th>MATERIAS REPROBADAS EN EVAL. FINAL</th>
                                            </tr>
                                        </thead>
                                        <tbody class="small">
                                            @foreach($analisis['candidatosBaja'] as $baja)
                                                <tr>
                                                    <td class="ps-3 fw-bold">{{ $baja['matricula'] }}</td>
                                                    <td class="fw-semibold text-dark">{{ $baja['nombre'] }}</td>
                                                    <td class="text-center">
                                                        <span class="badge bg-dark rounded-pill">{{ $baja['grupo'] }}</span>
                                                    </td>
                                                    <td>
                                                        @foreach($baja['materias_reprobadas'] as $mat)
                                                            <span class="badge bg-danger me-1">
                                                                {{ $mat['materia'] }}: {{ $mat['nota'] }}
                                                            </span>
                                                        @endforeach
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @endif
                        </div>

                        {{-- BOTÓN DE CIERRE --}}
                        <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 pt-3 border-top border-secondary">
                            <div class="small text-dark">
                                @if(!$analisis['puedeCerrar'])
                                    <i class="bi bi-lock-fill text-danger me-1"></i>
                                    El cierre permanecerá bloqueado hasta que no existan <strong>Alertas de Pendientes</strong>.
                                @else
                                    <i class="bi bi-shield-check text-success me-1"></i>
                                    Al confirmar, se guardará el historial de grupos, se promoverán los aprobados y se cerrará <strong>{{ $periodoActual }}</strong>.
                                @endif
                            </div>

                            <button type="button"
                                    class="btn btn-danger fw-bold px-4 shadow-sm"
                                    data-bs-toggle="modal"
                                    data-bs-target="#modalCerrarCiclo"
                                    {{ $analisis['puedeCerrar'] ? '' : 'disabled' }}>
                                <i class="bi bi-lock-fill me-1"></i>
                                Confirmar Cierre de Ciclo
                            </button>
                        </div>
                    @endif
                </div>
            </div>

            {{-- HISTORIAL DE CICLOS CERRADOS --}}
            @if($periodosCerrados->isNotEmpty())
                <div class="card admin-form-card shadow-sm">
                    <div class="card-header bg-dark text-white py-3">
                        <h6 class="fw-bold mb-0">
                            <i class="bi bi-clock-history me-2"></i>
                            Historial de Ciclos Cerrados
                        </h6>
                    </div>
                    <div class="card-body p-4">
                        <div class="table-responsive bg-white rounded-3 border border-secondary shadow-sm">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light text-muted small">
                                    <tr>
                                        <th class="ps-4">PERIODO</th>
                                        <th class="text-center">TOTAL ALUMNOS</th>
                                        <th class="text-center">PROMOVIDOS</th>
                                        <th class="text-center">EGRESADOS</th>
                                        <th class="text-center">BAJAS</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($periodosCerrados as $hist)
                                        <tr>
                                            <td class="ps-4 fw-bold text-dark">{{ $hist->periodo }}</td>
                                            <td class="text-center fw-bold">{{ $hist->total_alumnos }}</td>
                                            <td class="text-center">
                                                <span class="badge bg-success rounded-pill px-3">{{ $hist->promovidos }}</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="badge bg-dark rounded-pill px-3">{{ $hist->egresados }}</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="badge bg-danger rounded-pill px-3">{{ $hist->bajas }}</span>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            @endif

        </main>
    </div>
</div>

{{-- MODAL CONFIRMAR CORRECCIÓN --}}
@if($periodoEstado === 'ABIERTO')
<div class="modal fade" id="modalCorregirCiclo" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header bg-dark text-white">
                <h6 class="modal-title fw-bold">
                    <i class="bi bi-arrow-repeat text-warning me-2"></i>
                    Confirmar Corrección de Ciclo
                </h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p class="mb-2">
                    Se renombrará el periodo vigente en configuraciones, asignaciones, calificaciones y documentos:
                </p>
                <div class="bg-light border rounded-3 p-3 text-center mb-2">
                    <span class="text-muted text-decoration-line-through">{{ $periodoActual }}</span>
                    <i class="bi bi-arrow-right mx-2 text-dark"></i>
                    <strong class="text-success" id="textoNuevoPeriodoModal"></strong>
                </div>
                <p class="small text-muted mb-0">
                    Esta acción <strong>no promueve alumnos ni aplica bajas</strong>.
                </p>
            </div>
            <div class="modal-footer border-0">
                <button type="button" class="btn btn-secondary fw-bold" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-dark fw-bold" id="btnConfirmarCorregir">
                    Sí, corregir periodo
                </button>
            </div>
        </div>
    </div>
</div>

{{-- MODAL CONFIRMAR CIERRE DE CICLO --}}
@if($analisis && $analisis['puedeCerrar'])
<div class="modal fade" id="modalCerrarCiclo" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header bg-danger text-white">
                <h6 class="modal-title fw-bold">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i>
                    Confirmar Cierre Definitivo de Ciclo
                </h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p class="mb-2">
                    Estás a punto de cerrar definitivamente el periodo <strong>{{ $periodoActual }}</strong>. Se aplicarán los siguientes movimientos irreversibles:
                </p>
                <ul class="small mb-3">
                    <li><strong>{{ count($analisis['alumnosPromovidos']) }}</strong> alumno(s) avanzarán al siguiente semestre.</li>
                    <li><strong>{{ count($analisis['alumnosEgresados']) }}</strong> alumno(s) de 6° pasarán a estado <strong>EGRESADO</strong>.</li>
                    <li><strong>{{ count($analisis['candidatosBaja']) }}</strong> alumno(s) pasarán a estado <strong>BAJA</strong> por reprobar Evaluación Final.</li>
                </ul>
                <p class="small text-muted mb-0">
                    Las actas de los docentes quedarán en solo lectura y podrás abrir el siguiente ciclo escolar.
                </p>
            </div>
            <div class="modal-footer border-0">
                <button type="button" class="btn btn-secondary fw-bold" data-bs-dismiss="modal">Revisar antes</button>
                <form action="{{ route('admin.periodo.cerrar') }}" method="POST" class="m-0">
                    @csrf
                    <button type="submit" class="btn btn-danger fw-bold">
                        <i class="bi bi-lock-fill me-1"></i>
                        Cerrar Ciclo Ahora
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endif
@endif

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const btnAbrirModal = document.getElementById('btnAbrirModalCorregir');
        const btnConfirmar = document.getElementById('btnConfirmarCorregir');
        const formCorregir = document.getElementById('formCorregirCiclo');
        const cicloEl = document.getElementById('corregir_ciclo');
        const anioEl = document.getElementById('corregir_anio');
        const textoModal = document.getElementById('textoNuevoPeriodoModal');

        if (btnAbrirModal && textoModal && cicloEl && anioEl) {
            btnAbrirModal.addEventListener('click', function () {
                textoModal.textContent = `${cicloEl.value} ${anioEl.value}`;
            });
        }

        if (btnConfirmar && formCorregir) {
            btnConfirmar.addEventListener('click', function () {
                formCorregir.submit();
            });
        }
    });
</script>

@endsection
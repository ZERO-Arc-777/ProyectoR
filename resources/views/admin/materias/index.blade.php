@extends('layouts.app')

@section('titulo', 'GESTIÓN DE MATERIAS')

@section('usuario_header')
    <span class="fs-6">
        <i class="bi bi-person-circle me-1"></i>
        {{ Auth::user()->nombre ?? 'Administrador' }}
    </span>
@endsection

@section('contenido')

@php
    $mesesLista = [
        1 => 'Enero',
        2 => 'Febrero',
        3 => 'Marzo',
        4 => 'Abril',
        5 => 'Mayo',
        6 => 'Junio',
        7 => 'Julio',
        8 => 'Agosto',
        9 => 'Septiembre',
        10 => 'Octubre',
        11 => 'Noviembre',
        12 => 'Diciembre',
    ];

    $mapaMeses = [
        'enero' => 1, 'febrero' => 2, 'marzo' => 3, 'abril' => 4,
        'mayo' => 5, 'junio' => 6, 'julio' => 7, 'agosto' => 8,
        'septiembre' => 9, 'setiembre' => 9, 'octubre' => 10,
        'noviembre' => 11, 'diciembre' => 12,
    ];

    $anioActualSistema = (int) now()->year;

    $mesInicioDefault = 8;
    $anioInicioDefault = $anioActualSistema;
    $mesFinDefault = 12;
    $anioFinDefault = $anioActualSistema;

    if (!empty($periodoActual) && preg_match('/^([A-Za-záéíóúÁÉÍÓÚñÑ]+)\s+(\d{4})\s*-\s*([A-Za-záéíóúÁÉÍÓÚñÑ]+)\s+(\d{4})$/u', trim($periodoActual), $coincidencias)) {
        $mIni = mb_strtolower($coincidencias[1], 'UTF-8');
        $mFin = mb_strtolower($coincidencias[3], 'UTF-8');

        if (isset($mapaMeses[$mIni], $mapaMeses[$mFin])) {
            $mesInicioDefault = $mapaMeses[$mIni];
            $anioInicioDefault = (int) $coincidencias[2];
            $mesFinDefault = $mapaMeses[$mFin];
            $anioFinDefault = (int) $coincidencias[4];
        }
    }

    $mesInicioSel = (int) old('mes_inicio', $mesInicioDefault);
    $anioInicioSel = (int) old('anio_inicio', $anioInicioDefault);
    $mesFinSel = (int) old('mes_fin', $mesFinDefault);
    $anioFinSel = (int) old('anio_fin', $anioFinDefault);

    $aniosDisponibles = range($anioActualSistema - 5, $anioActualSistema + 5);
    foreach ([$anioInicioSel, $anioFinSel] as $anioExtra) {
        if ($anioExtra > 0 && !in_array($anioExtra, $aniosDisponibles, true)) {
            $aniosDisponibles[] = $anioExtra;
        }
    }
    sort($aniosDisponibles);

    $mostrarConfirmacionPasado = $anioInicioSel < $anioActualSistema || session('requiere_confirmacion_pasado');
@endphp

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

    /* TARJETAS CON ENCABEZADO OSCURO */
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

    .periodo-activo-pill {
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

    .periodo-preview-box {
        background: #ffffff;
        border: 1px solid #c0c4c5;
        border-radius: 12px;
        padding: 10px 14px;
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

                <h5 class="admin-sidebar-title mb-1">
                    SGAE PREPA 5
                </h5>

                <small class="text-white-50">
                    Panel Administrativo
                </small>
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

                <a href="{{ route('admin.materias') }}"
                   class="admin-nav active"
                   aria-current="page">
                    <i class="bi bi-journal-text"></i>
                    Materias
                </a>

                <a href="{{ route('admin.pagos') }}" class="admin-nav">
                    <i class="bi bi-cash-coin"></i>
                    Pagos
                </a>

                <div class="mt-2 mb-1 ps-3 text-white-50 small fw-bold"
                     style="letter-spacing: 0.5px;">
                    REVISIONES
                </div>

                <a href="{{ route('admin.revision.inscripciones') }}"
                   class="admin-nav-sub">
                    <i class="bi bi-arrow-return-right me-1"></i>
                    Inscripciones
                </a>

                <a href="{{ route('admin.revision.reinscripciones') }}"
                   class="admin-nav-sub">
                    <i class="bi bi-arrow-return-right me-1"></i>
                    Reinscripciones
                </a>

                <a href="{{ route('admin.revision.egresos') }}"
                   class="admin-nav-sub">
                    <i class="bi bi-arrow-return-right me-1"></i>
                    Egresos
                </a>

                <hr class="border-secondary my-3">

                {{-- CIERRE DE SESIÓN SEGURO --}}
                <form action="{{ route('logout.seguro') }}"
                      method="POST"
                      class="m-0">
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

        {{-- CONTENIDO PRINCIPAL --}}
        <main class="col-12 col-md-9 col-lg-10 p-4 p-lg-5">

            <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">

                <div>
                    <h2 class="fw-bold mb-1 text-dark">
                        Gestión de Materias
                    </h2>

                    <p class="admin-muted mb-0">
                        Administra las materias y su asignación por semestre.
                    </p>
                </div>

                <span class="badge rounded-pill px-3 py-2"
                      style="background: rgba(25,135,84,.15); color: #198754;">
                    <i class="bi bi-journal-check me-1"></i>
                    Módulo de Materias
                </span>

            </div>

            {{-- ALERTAS DE ÉXITO Y ERROR --}}
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
                        <span>Revisa la configuración del periodo escolar:</span>
                    </div>
                    <ul class="mb-0 ps-4">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            {{-- TARJETA 1: PERIODO ESCOLAR --}}
            <div class="card admin-form-card shadow-sm mb-4">

                <div class="card-header bg-dark text-white py-3 d-flex flex-wrap justify-content-between align-items-center gap-2">
                    <h6 class="fw-bold mb-0">
                        <i class="bi bi-calendar-range me-2"></i>
                        Configuración del Periodo Escolar
                    </h6>

                    {{-- ETIQUETA FIJA DE SOLO LECTURA CON EL PERIODO ESTABLECIDO --}}
                    <span class="periodo-activo-pill">
                        <i class="bi bi-lock-fill"></i>
                        Periodo activo: {{ $periodoActual ?: 'Sin definir' }}
                    </span>
                </div>

                <div class="card-body p-4">

                    <form action="{{ route('admin.materias.periodo') }}"
                          method="POST"
                          id="formPeriodoEscolar">
                        @csrf

                        <div class="row g-3 align-items-end">

                            <div class="col-12 col-sm-6 col-lg-3">
                                <label for="mes_inicio" class="form-label fw-bold text-dark small mb-1">
                                    Mes de inicio
                                </label>
                                <select name="mes_inicio"
                                        id="mes_inicio"
                                        class="form-select border-0 shadow-sm"
                                        required>
                                    @foreach($mesesLista as $numMes => $nombreMes)
                                        <option value="{{ $numMes }}" {{ $mesInicioSel === $numMes ? 'selected' : '' }}>
                                            {{ $nombreMes }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-12 col-sm-6 col-lg-2">
                                <label for="anio_inicio" class="form-label fw-bold text-dark small mb-1">
                                    Año de inicio
                                </label>
                                <select name="anio_inicio"
                                        id="anio_inicio"
                                        class="form-select border-0 shadow-sm"
                                        required>
                                    @foreach($aniosDisponibles as $anio)
                                        <option value="{{ $anio }}" {{ $anioInicioSel === $anio ? 'selected' : '' }}>
                                            {{ $anio }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-12 col-sm-6 col-lg-3">
                                <label for="mes_fin" class="form-label fw-bold text-dark small mb-1">
                                    Mes de fin
                                </label>
                                <select name="mes_fin"
                                        id="mes_fin"
                                        class="form-select border-0 shadow-sm"
                                        required>
                                    @foreach($mesesLista as $numMes => $nombreMes)
                                        <option value="{{ $numMes }}" {{ $mesFinSel === $numMes ? 'selected' : '' }}>
                                            {{ $nombreMes }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-12 col-sm-6 col-lg-2">
                                <label for="anio_fin" class="form-label fw-bold text-dark small mb-1">
                                    Año de fin
                                </label>
                                <select name="anio_fin"
                                        id="anio_fin"
                                        class="form-select border-0 shadow-sm"
                                        required>
                                    @foreach($aniosDisponibles as $anio)
                                        <option value="{{ $anio }}" {{ $anioFinSel === $anio ? 'selected' : '' }}>
                                            {{ $anio }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-12 col-lg-2">
                                <button type="submit"
                                        id="btnActualizarPeriodo"
                                        class="btn btn-success fw-bold px-3 w-100 shadow-sm">
                                    <i class="bi bi-save me-1"></i>
                                    Actualizar
                                </button>
                            </div>

                        </div>

                        {{-- VISTA PREVIA DEL RANGO Y VALIDACIÓN VISUAL --}}
                        <div class="periodo-preview-box d-flex flex-wrap justify-content-between align-items-center gap-2 mt-3 shadow-sm">
                            <div class="small text-dark">
                                <span class="text-muted">Nuevo periodo seleccionado:</span>
                                <strong id="previewPeriodoTexto" class="ms-1"></strong>
                            </div>
                            <span id="previewPeriodoDuracion" class="badge rounded-pill px-3 py-2"></span>
                        </div>

                        {{-- CONFIRMACIÓN PARA PERIODOS DE AÑOS ANTERIORES --}}
                        <div id="bloqueConfirmacionPasado"
                             class="alert alert-warning border-0 rounded-3 shadow-sm mt-3 mb-0 {{ $mostrarConfirmacionPasado ? '' : 'd-none' }}">
                            <div class="form-check">
                                <input class="form-check-input border-secondary"
                                       type="checkbox"
                                       value="1"
                                       id="confirmar_periodo_pasado"
                                       name="confirmar_periodo_pasado"
                                       {{ old('confirmar_periodo_pasado') ? 'checked' : '' }}>
                                <label class="form-check-label fw-bold text-dark small" for="confirmar_periodo_pasado">
                                    Confirmo que deseo establecer un periodo correspondiente a un año anterior al actual ({{ $anioActualSistema }}).
                                </label>
                            </div>
                        </div>

                    </form>

                </div>
            </div>

            {{-- TARJETA 2: MATERIAS REGISTRADAS --}}
            <div class="card admin-form-card shadow-sm mb-4">

                <div class="card-header bg-dark text-white py-3">
                    <h6 class="fw-bold mb-0">
                        <i class="bi bi-journal-bookmark-fill me-2"></i>
                        Materias Registradas
                    </h6>
                </div>

                <div class="card-body p-4">

                    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">

                        <a href="{{ route('admin.materias.create') }}"
                           class="btn btn-dark fw-bold px-4 shadow-sm">
                            <i class="bi bi-plus-circle me-1"></i>
                            Registrar Nueva Materia
                        </a>

                        <form action="{{ route('admin.materias') }}"
                              method="GET"
                              class="d-flex"
                              style="width: 380px; max-width: 100%;">

                            <div class="input-group shadow-sm">

                                <input type="text"
                                       name="buscar"
                                       class="form-control border-0"
                                       placeholder="Buscar materia por nombre..."
                                       value="{{ $buscar ?? '' }}">

                                <button type="submit"
                                        class="btn text-white fw-bold px-3"
                                        style="background-color: #198754;"
                                        title="Buscar">
                                    <i class="bi bi-search"></i>
                                </button>

                                @if(request('buscar'))
                                    <a href="{{ route('admin.materias') }}"
                                       class="btn btn-secondary px-3 text-white"
                                       title="Limpiar búsqueda">
                                        <i class="bi bi-x-lg"></i>
                                    </a>
                                @endif

                            </div>

                        </form>

                    </div>

                    {{-- TABLA DE MATERIAS --}}
                    <div class="table-responsive bg-white rounded-3 border border-secondary shadow-sm">

                        <table class="table table-hover align-middle mb-0">

                            <thead class="table-light text-muted small">
                                <tr>
                                    <th class="ps-4">ID</th>
                                    <th>NOMBRE DE LA MATERIA</th>
                                    <th class="text-center">SEMESTRE</th>
                                    <th class="text-center">ESTADO</th>
                                    <th class="text-center">ACCIONES</th>
                                </tr>
                            </thead>

                            <tbody>

                                @forelse($materias as $materia)

                                    <tr>

                                        <td class="fw-bold text-dark ps-4">
                                            #{{ $materia->id_materia }}
                                        </td>

                                        <td class="fw-bold text-dark">
                                            <i class="bi bi-journal-text text-success me-2"></i>
                                            {{ $materia->nombre }}
                                        </td>

                                        <td class="text-center">
                                            <span class="badge bg-dark rounded-pill px-3 py-2">
                                                {{ $materia->semestre }}° semestre
                                            </span>
                                        </td>

                                        <td class="text-center">

                                            @if($materia->estado === 'ACTIVA')

                                                <span class="badge bg-success px-3 py-2 rounded-pill">
                                                    <i class="bi bi-check-circle-fill me-1"></i>
                                                    Activa
                                                </span>

                                            @else

                                                <span class="badge bg-danger px-3 py-2 rounded-pill">
                                                    <i class="bi bi-x-circle-fill me-1"></i>
                                                    Inactiva
                                                </span>

                                            @endif

                                        </td>

                                        <td class="text-center">

                                            <a href="{{ route('admin.materias.edit', $materia->id_materia) }}"
                                               class="btn btn-sm btn-outline-dark fw-bold px-3 rounded-pill">
                                                <i class="bi bi-pencil-square me-1"></i>
                                                Editar
                                            </a>

                                        </td>

                                    </tr>

                                @empty

                                    <tr>
                                        <td colspan="5"
                                            class="text-center py-5">

                                            <i class="bi bi-journal-x fs-1 text-muted opacity-50 d-block mb-2"></i>

                                            <span class="fw-bold text-muted">

                                                @if(request('buscar'))
                                                    No se encontraron materias con ese nombre.
                                                @else
                                                    Todavía no hay materias registradas.
                                                @endif

                                            </span>

                                        </td>
                                    </tr>

                                @endforelse

                            </tbody>

                        </table>

                    </div>

                    {{-- PAGINACIÓN --}}
                    <div class="d-flex justify-content-end mt-4">
                        {{ $materias->appends(request()->query())->links() }}
                    </div>

                </div>
            </div>

        </main>

    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const mesInicioEl = document.getElementById('mes_inicio');
        const anioInicioEl = document.getElementById('anio_inicio');
        const mesFinEl = document.getElementById('mes_fin');
        const anioFinEl = document.getElementById('anio_fin');
        const previewTextoEl = document.getElementById('previewPeriodoTexto');
        const previewDuracionEl = document.getElementById('previewPeriodoDuracion');
        const bloquePasadoEl = document.getElementById('bloqueConfirmacionPasado');
        const checkPasadoEl = document.getElementById('confirmar_periodo_pasado');
        const anioActualSistema = {{ $anioActualSistema }};

        const nombresMeses = {
            1: 'Enero', 2: 'Febrero', 3: 'Marzo', 4: 'Abril',
            5: 'Mayo', 6: 'Junio', 7: 'Julio', 8: 'Agosto',
            9: 'Septiembre', 10: 'Octubre', 11: 'Noviembre', 12: 'Diciembre'
        };

        const actualizarVistaPrevia = function () {
            const mIni = parseInt(mesInicioEl.value, 10);
            const aIni = parseInt(anioInicioEl.value, 10);
            const mFin = parseInt(mesFinEl.value, 10);
            const aFin = parseInt(anioFinEl.value, 10);

            previewTextoEl.textContent = `${nombresMeses[mIni]} ${aIni} - ${nombresMeses[mFin]} ${aFin}`;

            const inicioTotal = (aIni * 12) + mIni;
            const finTotal = (aFin * 12) + mFin;
            const duracion = (finTotal - inicioTotal) + 1;

            if (finTotal <= inicioTotal) {
                previewDuracionEl.className = 'badge rounded-pill px-3 py-2 bg-danger';
                previewDuracionEl.textContent = 'La fecha final debe ser posterior a la inicial';
            } else if (duracion < 4 || duracion > 6) {
                previewDuracionEl.className = 'badge rounded-pill px-3 py-2 bg-danger';
                previewDuracionEl.textContent = `Duración fuera de rango (${duracion} meses · permitido: 4 a 6)`;
            } else {
                previewDuracionEl.className = 'badge rounded-pill px-3 py-2 bg-success';
                previewDuracionEl.textContent = `Duración válida: ${duracion} meses`;
            }

            if (aIni < anioActualSistema) {
                bloquePasadoEl.classList.remove('d-none');
            } else {
                bloquePasadoEl.classList.add('d-none');
                checkPasadoEl.checked = false;
            }
        };

        [mesInicioEl, anioInicioEl, mesFinEl, anioFinEl].forEach(function (selectEl) {
            selectEl.addEventListener('change', actualizarVistaPrevia);
        });

        actualizarVistaPrevia();
    });
</script>

@endsection
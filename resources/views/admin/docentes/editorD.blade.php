@extends('layouts.app')

@section('titulo', 'Editar Expediente de Docente')

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
        font-size: .9rem;
        padding: 8px 16px 8px 40px;
        color: #8a939b;
        text-decoration: none;
        display: block;
        transition: color .2s;
    }

    .admin-nav-sub:hover {
        color: #ffffff;
    }

    .admin-nav i {
        font-size: 1.15rem;
    }

    .admin-logout {
        color: #ffb3b9;
    }

    .admin-logout:hover {
        color: #ffffff;
        background: rgba(220, 53, 69, .22);
    }

    .select2-container {
        width: 100% !important;
    }

    .select2-container--default .select2-selection--single {
        background-color: #fff;
        border: 0 !important;
        border-radius: .375rem !important;
        height: calc(3.5rem + 2px) !important;
        padding: .75rem !important;
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

    .materia-row {
        background: #f8f9fa;
        border: 1px solid #dee2e6;
        border-radius: 12px;
        padding: 15px;
        margin-bottom: 12px;
    }
</style>

<div class="container-fluid admin-panel">
    <div class="row min-vh-100">

        {{-- BARRA LATERAL --}}
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

                <a href="{{ route('admin.docentes') }}"
                   class="admin-nav active"
                   aria-current="page">
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

                <div class="mt-2 mb-1 ps-3 text-white-50 small fw-bold"
                     style="letter-spacing: .5px;">
                    REVISIONES
                </div>

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

            <div class="card shadow-sm border-0 rounded-4 w-100 mx-auto"
                 style="max-width: 750px; background-color: #d1d5d6;">

                <div class="card-header bg-dark text-white text-center py-3"
                     style="border-top-left-radius: 15px; border-top-right-radius: 15px;">

                    <h5 class="mb-0 fw-bold">
                        <i class="bi bi-person-badge-fill me-2"></i>
                        EDITAR EXPEDIENTE DE DOCENTE
                    </h5>

                </div>

                <div class="card-body p-4 p-md-5">

                    {{-- ALERTA DE ÉXITO --}}
                    @if (session('success'))
                        <div class="alert alert-success fw-bold rounded-3 shadow-sm border-0 mb-4">
                            <i class="bi bi-check-circle-fill me-2"></i>
                            {{ session('success') }}
                        </div>
                    @endif

                    {{-- ERRORES DE VALIDACIÓN --}}
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

                    <form action="{{ route('admin.docentes.update', $docente->id_docente) }}"
                          method="POST">
                        @csrf
                        @method('PUT')

                        <div class="row">

                            <div class="col-12 mb-3 text-start">
                                <label class="fw-bold text-dark mb-1 ms-1">
                                    Vincular a Cuenta de Usuario
                                </label>

                                <select name="id_usuario"
                                        id="buscador-usuarios"
                                        class="form-select border-0 shadow-sm">

                                    <option value="">-- Sin cuenta vinculada --</option>

                                    @foreach($usuarios as $user)
                                        <option value="{{ $user->id_usuario }}"
                                            {{ old('id_usuario', $docente->id_usuario) == $user->id_usuario ? 'selected' : '' }}>
                                            {{ $user->nombre }} ({{ $user->correo }})
                                        </option>
                                    @endforeach

                                </select>
                            </div>

                            <div class="col-md-6 mb-3 text-start">
                                <label class="fw-bold text-dark mb-1 ms-1">
                                    Número de Empleado
                                </label>

                                <input type="text"
                                       name="numero_empleado"
                                       class="form-control border-0 shadow-sm fw-bold"
                                       value="{{ old('numero_empleado', $docente->numero_empleado) }}"
                                       required>
                            </div>

                            <div class="col-md-6 mb-3 text-start">
                                <label class="fw-bold text-dark mb-1 ms-1">
                                    CURP
                                </label>

                                <input type="text"
                                       name="curp"
                                       class="form-control border-0 shadow-sm text-uppercase fw-bold"
                                       maxlength="18"
                                       value="{{ old('curp', $docente->curp) }}"
                                       required>
                            </div>

                            <div class="col-12 mb-3 text-start">
                                <label class="fw-bold text-dark mb-1 ms-1">
                                    Nombre(s)
                                </label>

                                <input type="text"
                                       name="nombres"
                                       class="form-control border-0 shadow-sm"
                                       value="{{ old('nombres', $docente->nombres) }}"
                                       required>
                            </div>

                            <div class="col-md-6 mb-3 text-start">
                                <label class="fw-bold text-dark mb-1 ms-1">
                                    Apellido Paterno
                                </label>

                                <input type="text"
                                       name="apellido_paterno"
                                       class="form-control border-0 shadow-sm"
                                       value="{{ old('apellido_paterno', $docente->apellido_paterno) }}"
                                       required>
                            </div>

                            <div class="col-md-6 mb-3 text-start">
                                <label class="fw-bold text-dark mb-1 ms-1">
                                    Apellido Materno
                                </label>

                                <input type="text"
                                       name="apellido_materno"
                                       class="form-control border-0 shadow-sm"
                                       value="{{ old('apellido_materno', $docente->apellido_materno) }}">
                            </div>

                            <div class="col-md-6 mb-4 text-start">
                                <label class="fw-bold text-dark mb-1 ms-1">
                                    Cédula Profesional
                                </label>

                                <input type="text"
                                       name="cedula_profesional"
                                       class="form-control border-0 shadow-sm"
                                       value="{{ old('cedula_profesional', $docente->cedula_profesional) }}">
                            </div>

                            <div class="col-md-6 mb-4 text-start">
                                <label class="fw-bold text-dark mb-1 ms-1">
                                    Estado Administrativo
                                </label>

                                <select name="estado"
                                        class="form-select border-0 shadow-sm fw-bold"
                                        required>

                                    <option value="ACTIVO"
                                        {{ old('estado', $docente->estado) == 'ACTIVO' ? 'selected' : '' }}>
                                        Activo
                                    </option>

                                    <option value="INACTIVO"
                                        {{ old('estado', $docente->estado) == 'INACTIVO' ? 'selected' : '' }}>
                                        Inactivo
                                    </option>

                                    <option value="BAJA"
                                        {{ old('estado', $docente->estado) == 'BAJA' ? 'selected' : '' }}>
                                        Baja Definitiva
                                    </option>

                                </select>
                            </div>

                        </div>

                        {{-- ASIGNACIONES ACADÉMICAS --}}
                        <div class="col-12 mb-2 text-start mt-2">
                            <label class="fw-bold text-dark mb-1 ms-1">
                                Asignaciones Académicas ({{ $periodoActual }})
                            </label>
                        </div>

                        @php
                            if (old('materias') !== null) {
                                $materiasFormulario = old('materias');
                                $gruposFormulario = old('grupos', []);
                            } else {
                                $materiasFormulario = $asignacionesActuales
                                    ->pluck('id_materia')
                                    ->toArray();

                                $gruposFormulario = $asignacionesActuales
                                    ->pluck('grupo')
                                    ->toArray();
                            }

                            if (count($materiasFormulario) === 0) {
                                $materiasFormulario = [''];
                                $gruposFormulario = ['A'];
                            }
                        @endphp

                        <div id="contenedor-materias">

                            @foreach($materiasFormulario as $index => $idMateria)
                                @php
                                    $grupoActual = strtoupper(trim((string) ($gruposFormulario[$index] ?? 'A')));
                                @endphp

                                <div class="materia-row">

                                    <div class="row align-items-end">

                                        <div class="col-md-8 mb-2">
                                            <label class="fw-bold text-dark mb-1">
                                                Materia
                                            </label>

                                            <select name="materias[]"
                                                    class="form-select border-0 shadow-sm">

                                                <option value="">
                                                    -- Seleccionar materia --
                                                </option>

                                                @foreach($materias as $materia)
                                                    <option value="{{ $materia->id_materia }}"
                                                        {{ $idMateria == $materia->id_materia ? 'selected' : '' }}>
                                                        {{ $materia->nombre }} - Semestre {{ $materia->semestre }}
                                                    </option>
                                                @endforeach

                                            </select>
                                        </div>

                                        <div class="col-md-3 col-8 mb-2">
                                            <label class="fw-bold text-dark mb-1">
                                                Grupo
                                            </label>

                                            <select name="grupos[]"
                                                    class="form-select border-0 shadow-sm fw-bold">
                                                <option value="A" {{ $grupoActual === 'A' ? 'selected' : '' }}>
                                                    Grupo A
                                                </option>
                                                <option value="B" {{ $grupoActual === 'B' ? 'selected' : '' }}>
                                                    Grupo B
                                                </option>
                                            </select>
                                        </div>

                                        <div class="col-md-1 col-4 mb-2">
                                            <button type="button"
                                                    class="btn btn-outline-danger w-100 btn-eliminar-materia"
                                                    title="Eliminar asignación">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </div>

                                    </div>

                                </div>

                            @endforeach

                        </div>

                        <div class="mb-4 text-start">
                            <button type="button"
                                    id="agregar-materia"
                                    class="btn btn-outline-success fw-bold">
                                <i class="bi bi-plus-circle me-1"></i>
                                Agregar otra materia
                            </button>
                        </div>

                        <div class="d-flex justify-content-end mt-4 gap-3 flex-wrap">
                            <a href="{{ route('admin.docentes') }}"
                               class="btn btn-outline-dark fw-bold px-4 shadow-sm">
                                Cancelar
                            </a>

                            <button type="submit"
                                    class="btn text-white fw-bold px-4 shadow-sm"
                                    style="background-color: #198754;">
                                <i class="bi bi-save me-1"></i>
                                Guardar Cambios
                            </button>
                        </div>

                    </form>

                </div>
            </div>

            {{-- TARJETA DE SUSTITUCIÓN DE DOCENTE --}}
            @if($asignacionesActuales->isNotEmpty())
                <div class="card shadow-sm border-0 rounded-4 w-100 mx-auto mt-4"
                     style="max-width: 750px; background-color: #d1d5d6;">

                    <div class="card-header bg-dark text-white py-3 d-flex justify-content-between align-items-center"
                         style="border-top-left-radius: 15px; border-top-right-radius: 15px;">
                        <h6 class="mb-0 fw-bold">
                            <i class="bi bi-arrow-left-right text-warning me-2"></i>
                            SUSTITUCIÓN DE DOCENTE ({{ $periodoActual }})
                        </h6>
                    </div>

                    <div class="card-body p-4">
                        <p class="small text-dark mb-3">
                            Usa esta opción si el docente causó baja o cambio a mitad del ciclo. El docente sustituto heredará el grupo y todas las calificaciones parciales ya capturadas.
                        </p>

                        <div class="table-responsive bg-white rounded-3 border border-secondary shadow-sm">
                            <table class="table table-hover align-middle mb-0 small">
                                <thead class="table-light text-muted">
                                    <tr>
                                        <th class="ps-3">MATERIA</th>
                                        <th class="text-center">GRUPO</th>
                                        <th class="text-center">ACTAS</th>
                                        <th class="text-end pe-3">ACCIÓN</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($asignacionesActuales as $asig)
                                        <tr>
                                            <td class="ps-3 fw-bold text-dark">
                                                {{ $asig->materia->nombre ?? 'N/A' }}
                                            </td>
                                            <td class="text-center">
                                                <span class="badge bg-dark rounded-pill">
                                                    {{ $asig->materia->semestre ?? '' }}° {{ $asig->grupo }}
                                                </span>
                                            </td>
                                            <td class="text-center">
                                                @if($asig->calificaciones_count > 0)
                                                    <span class="badge bg-warning text-dark">
                                                        {{ $asig->calificaciones_count }} registro(s)
                                                    </span>
                                                @else
                                                    <span class="badge bg-light text-muted border">Sin notas</span>
                                                @endif
                                            </td>
                                            <td class="text-end pe-3">
                                                <button type="button"
                                                        class="btn btn-sm btn-outline-dark fw-bold rounded-pill btn-abrir-sustitucion"
                                                        data-bs-toggle="modal"
                                                        data-bs-target="#modalSustituirDocente"
                                                        data-action="{{ route('admin.docentes.asignaciones.sustituir', $asig->id_asignacion) }}"
                                                        data-materia="{{ $asig->materia->nombre ?? 'Materia' }} ({{ $asig->materia->semestre ?? '' }}° {{ $asig->grupo }})"
                                                        {{ $otrosDocentes->isEmpty() ? 'disabled' : '' }}>
                                                    <i class="bi bi-arrow-left-right me-1"></i> Sustituir
                                                </button>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        @if($otrosDocentes->isEmpty())
                            <small class="text-muted d-block mt-2">
                                <i class="bi bi-info-circle me-1"></i>
                                Registra al menos otro docente activo para habilitar la sustitución.
                            </small>
                        @endif
                    </div>
                </div>

                {{-- MODAL SUSTITUIR DOCENTE --}}
                <div class="modal fade" id="modalSustituirDocente" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content border-0 shadow-lg rounded-4">
                            <form method="POST" id="formSustituirDocente" action="">
                                @csrf

                                <div class="modal-header bg-dark text-white">
                                    <h6 class="modal-title fw-bold">
                                        <i class="bi bi-arrow-left-right text-warning me-2"></i>
                                        Transferir Materia a Docente Sustituto
                                    </h6>
                                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                                </div>

                                <div class="modal-body">
                                    <p class="small mb-2">
                                        Materia a transferir: <strong id="textoMateriaSustituir" class="text-dark"></strong>
                                    </p>

                                    <div class="mb-2">
                                        <label for="nuevo_id_docente" class="form-label fw-bold small text-dark">
                                            Docente sustituto (solo activos)
                                        </label>

                                        <select name="nuevo_id_docente" id="nuevo_id_docente" class="form-select" required>
                                            <option value="">-- Seleccionar docente --</option>
                                            @foreach($otrosDocentes as $otro)
                                                <option value="{{ $otro->id_docente }}">
                                                    {{ $otro->apellido_paterno }} {{ $otro->apellido_materno }} {{ $otro->nombres }} (#{{ $otro->numero_empleado }})
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <p class="small text-muted mb-0">
                                        El nuevo docente heredará automáticamente el grupo y las calificaciones parciales registradas hasta hoy.
                                    </p>
                                </div>

                                <div class="modal-footer border-0">
                                    <button type="button" class="btn btn-secondary fw-bold" data-bs-dismiss="modal">
                                        Cancelar
                                    </button>
                                    <button type="submit" class="btn btn-success fw-bold">
                                        Confirmar Sustitución
                                    </button>
                                </div>

                            </form>
                        </div>
                    </div>
                </div>
            @endif

        </main>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>
    $(document).ready(function () {

        $('#buscador-usuarios').select2({
            placeholder: "-- Escribe el nombre o correo del docente --",
            allowClear: true,
            width: '100%'
        });

        $('#agregar-materia').on('click', function () {

            let nuevaFila = `
                <div class="materia-row">
                    <div class="row align-items-end">

                        <div class="col-md-8 mb-2">
                            <label class="fw-bold text-dark mb-1">Materia</label>

                            <select name="materias[]" class="form-select border-0 shadow-sm">
                                <option value="">-- Seleccionar materia --</option>

                                @foreach($materias as $materia)
                                    <option value="{{ $materia->id_materia }}">
                                        {{ $materia->nombre }} - Semestre {{ $materia->semestre }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-3 col-8 mb-2">
                            <label class="fw-bold text-dark mb-1">Grupo</label>

                            <select name="grupos[]" class="form-select border-0 shadow-sm fw-bold">
                                <option value="A" selected>Grupo A</option>
                                <option value="B">Grupo B</option>
                            </select>
                        </div>

                        <div class="col-md-1 col-4 mb-2">
                            <button type="button"
                                    class="btn btn-outline-danger w-100 btn-eliminar-materia"
                                    title="Eliminar asignación">
                                <i class="bi bi-trash"></i>
                            </button>
                        </div>

                    </div>
                </div>
            `;

            $('#contenedor-materias').append(nuevaFila);
        });

        $(document).on('click', '.btn-eliminar-materia', function () {

            if ($('.materia-row').length > 1) {
                $(this).closest('.materia-row').remove();
            } else {
                $(this).closest('.materia-row').find('select[name="materias[]"]').val('');
                $(this).closest('.materia-row').find('select[name="grupos[]"]').val('A');
            }

        });

        $(document).on('click', '.btn-abrir-sustitucion', function () {
            $('#formSustituirDocente').attr('action', $(this).data('action'));
            $('#textoMateriaSustituir').text($(this).data('materia'));
            $('#nuevo_id_docente').val('');
        });

    });
</script>

@endsection
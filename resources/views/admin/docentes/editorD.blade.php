
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

    .form-card {
        background-color: #d1d5d6;
    }

    .section-title {
        font-weight: 800;
        color: #343a40;
        border-bottom: 2px solid #198754;
        padding-bottom: 8px;
        margin-bottom: 20px;
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
                    <i class="bi bi-journal-text"></i> Materias / Asig.
                </a>

                <a href="{{ route('admin.pagos') }}" class="admin-nav">
                    <i class="bi bi-cash-coin"></i> Seg. de Pagos
                </a>

                <div class="mt-2 mb-1 ps-3 text-white-50 small fw-bold"
                     style="letter-spacing: .5px;">
                    REVISIONES
                </div>

                <a href="{{ route('admin.revision.inscripciones') }}"
                   class="admin-nav-sub">
                    <i class="bi bi-arrow-return-right me-1"></i> Inscripciones
                </a>

                <a href="{{ route('admin.revision.reinscripciones') }}"
                   class="admin-nav-sub">
                    <i class="bi bi-arrow-return-right me-1"></i> Reinscripciones
                </a>

                <a href="{{ route('admin.revision.egresos') }}"
                   class="admin-nav-sub">
                    <i class="bi bi-arrow-return-right me-1"></i> Egresos
                </a>

                <hr class="border-secondary my-3">

                <a href="{{ route('logout') }}"
                   class="admin-nav admin-logout">
                    <i class="bi bi-power"></i> Cerrar sesión
                </a>

            </nav>
        </aside>

        {{-- CONTENIDO PRINCIPAL --}}
        <main class="col-12 col-md-9 col-lg-10 p-4 p-lg-5">

            <div class="card shadow-sm border-0 rounded-4 w-100 mx-auto"
                 style="max-width: 850px;">

                <div class="card-header bg-dark text-white text-center py-3"
                     style="border-top-left-radius: 15px; border-top-right-radius: 15px;">

                    <h5 class="mb-0 fw-bold">
                        <i class="bi bi-person-badge-fill me-2"></i>
                        EDITAR EXPEDIENTE DE DOCENTE
                    </h5>
                </div>

                <div class="card-body p-4 p-md-5 form-card">

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

                        {{-- DATOS DE CUENTA --}}
                        <h6 class="section-title">
                            <i class="bi bi-person-badge me-2"></i>
                            Cuenta de usuario
                        </h6>

                        <div class="row">

                            <div class="col-12 mb-4 text-start">

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

                                <small class="text-muted">
                                    Puedes vincular una cuenta disponible o dejar el expediente sin cuenta asociada.
                                </small>

                            </div>

                        </div>

                        {{-- DATOS PERSONALES --}}
                        <h6 class="section-title">
                            <i class="bi bi-person-lines-fill me-2"></i>
                            Datos personales y laborales
                        </h6>

                        <div class="row">

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

                            <div class="col-md-6 mb-3 text-start">

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
                        <h6 class="section-title">
                            <i class="bi bi-journal-bookmark-fill me-2"></i>
                            Asignaciones académicas
                        </h6>

                        <p class="text-muted small">
                            Administra las materias que imparte el docente durante el periodo académico actual.
                        </p>

                        @php
                            /*
                             * Si el formulario regresó con errores, se muestran
                             * los datos enviados anteriormente.
                             *
                             * Si es la primera carga, se muestran las asignaciones
                             * que el controlador obtuvo de la base de datos.
                             */

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

                            // Garantizar que exista al menos una fila.
                            if (count($materiasFormulario) === 0) {
                                $materiasFormulario = [''];
                                $gruposFormulario = ['A'];
                            }
                        @endphp

                        <div id="contenedor-materias">

                            @foreach($materiasFormulario as $index => $idMateria)

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

                                            <input type="text"
                                                   name="grupos[]"
                                                   class="form-control border-0 shadow-sm text-uppercase"
                                                   value="{{ $gruposFormulario[$index] ?? 'A' }}"
                                                   maxlength="10"
                                                   placeholder="Ej. A">

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

                        <div class="mb-4">

                            <button type="button"
                                    id="agregar-materia"
                                    class="btn btn-outline-success fw-bold">

                                <i class="bi bi-plus-circle me-1"></i>
                                Agregar otra materia

                            </button>

                        </div>

                        <div class="alert alert-info border-0 rounded-3 small">

                            <i class="bi bi-info-circle-fill me-1"></i>

                            Las asignaciones mostradas corresponden al periodo académico actual.
                            Al guardar, se actualizarán las asignaciones de este periodo.

                        </div>

                        {{-- BOTONES --}}
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

        </main>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>
    $(document).ready(function () {

        // Buscador de cuentas de usuario.
        $('#buscador-usuarios').select2({
            placeholder: "-- Escribe el nombre o correo del docente --",
            allowClear: true,
            width: '100%'
        });

        // Agregar una nueva asignación académica de manera segura.
        $('#agregar-materia').on('click', function () {

            // Utilizar la fila generada por Laravel Blade.
            const filaOriginal = $('#contenedor-materias .materia-row').first();

            // Comprobar que exista una fila.
            if (filaOriginal.length === 0) {
                return;
            }

            // Clonar el HTML ya generado y escapado por Blade.
            const nuevaFila = filaOriginal.clone(false, false);

            // Limpiar los valores de la nueva asignación.
            nuevaFila.find('select[name="materias[]"]').val('');
            nuevaFila.find('input[name="grupos[]"]').val('A');

            // Agregar la nueva fila al formulario.
            $('#contenedor-materias').append(nuevaFila);

        });

        // Eliminar una asignación académica.
        $(document).on('click', '.btn-eliminar-materia', function () {

            const filaActual = $(this).closest('.materia-row');

            const totalFilas =
                $('#contenedor-materias .materia-row').length;

            if (totalFilas > 1) {

                // Eliminar la fila seleccionada.
                filaActual.remove();

            } else {

                // Si queda una sola fila, limpiar sus campos.
                filaActual.find('select[name="materias[]"]').val('');
                filaActual.find('input[name="grupos[]"]').val('A');

            }

        });

    });
</script>

@endsection

@extends('layouts.app')

@section('titulo', 'Captura de Calificaciones')

@section('usuario_header')
    <span class="fs-6">
        <i class="bi bi-person-circle me-1"></i>
        {{ Auth::user()->nombre ?? 'Docente' }}
    </span>
@endsection

@section('contenido')

<style>
    .calificaciones-panel {
        min-height: 100vh;
        background-color: #f4f6f9; 
        color: #212529;
    }

    .calificaciones-sidebar {
        background: #212529; 
        border-right: 1px solid #343a40;
    }

    .calificaciones-nav {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 13px 16px;
        border-radius: 12px;
        color: #adb5bd;
        text-decoration: none;
        transition: .2s;
    }

    .calificaciones-nav:hover {
        background: rgba(255, 255, 255, .1);
        color: #ffffff;
    }

    .calificaciones-nav.active {
        background: #fd7e14;
        color: #ffffff;
        font-weight: 600;
    }

    .calificaciones-muted {
        color: #6c757d;
    }

    /* TARJETA BLANCA PARA TABLA (Instrucción 1 de Formulario) */
    .calificaciones-table-card {
        background: #ffffff;
        border: 1px solid #c0c4c5;
        border-radius: 20px;
        padding: 25px;
        box-shadow: 0 4px 6px rgba(0,0,0,0.02);
    }

    /* ESTILOS DE TABLA */
    .table-calificaciones {
        width: 100%;
        margin-bottom: 0;
        border-collapse: separate;
        border-spacing: 0;
    }

    .table-calificaciones th {
        background-color: transparent;
        color: #212529;
        font-weight: 700;
        text-transform: uppercase;
        font-size: 0.85rem;
        padding: 15px 10px;
        border-bottom: 2px solid #e9ecef;
        text-align: left;
    }

    .table-calificaciones td {
        padding: 15px 10px;
        vertical-align: middle;
        border-bottom: 1px solid #f4f6f9;
        color: #495057;
    }

    .table-calificaciones tr:last-child td {
        border-bottom: none;
    }

    .table-calificaciones tr:hover td {
        background-color: #f8f9fa;
    }

    /* ETIQUETAS DE ACENTO NARANJA */
    .calificaciones-etiqueta {
        display: inline-flex;
        align-items: center;
        padding: 4px 10px;
        border-radius: 30px;
        background: rgba(253, 126, 20, 0.15);
        color: #d95c00;
        font-size: .75rem;
        font-weight: 600;
        margin-left: 5px;
    }

    /* ESTADO VACÍO */
    .calificaciones-vacio {
        background: #ffffff;
        border: 1px dashed #c0c4c5;
        border-radius: 20px;
        padding: 55px 25px;
        text-align: center;
        color: #212529;
    }

    .calificaciones-vacio-icon {
        width: 80px;
        height: 80px;
        border-radius: 22px;
        background: rgba(253, 126, 20, 0.15);
        color: #fd7e14;
        display: grid;
        place-items: center;
        margin: 0 auto 22px;
        font-size: 2.2rem;
    }

    /* INPUT DE CALIFICACIÓN (Instrucción 2 de Formulario) */
    .input-calificacion {
        max-width: 80px;
        text-align: center;
        font-weight: 600;
    }
</style>

<div class="container-fluid calificaciones-panel">

    <div class="row min-vh-100">

        <!-- MENÚ LATERAL -->
        <aside class="col-12 col-md-3 col-lg-2 calificaciones-sidebar p-4">
            <div class="text-center mb-4">
                <i class="bi bi-mortarboard-fill fs-2 text-white"></i>
                <h5 class="fw-bold mt-2 mb-1 text-white">
                    PANEL DOCENTE
                </h5>
                <small class="calificaciones-muted">
                    Gestión Académica
                </small>
            </div>

            <hr class="border-secondary my-4">

            <nav class="d-grid gap-2" aria-label="Menú del docente">
                <a href="{{ route('docente.dashboard') }}" class="calificaciones-nav">
                    <i class="bi bi-grid-1x2-fill"></i> Inicio
                </a>
                <!-- Se mantiene activo "Mis Materias" porque es una sub-vista de esta sección -->
                <a href="{{ route('docente.materias') }}" class="calificaciones-nav active">
                    <i class="bi bi-journal-bookmark-fill"></i> Mis Materias
                </a>
                <a href="{{ route('docente.grupos') }}" class="calificaciones-nav">
                    <i class="bi bi-people-fill"></i> Mis Grupos
                </a>
                <a href="{{ route('docente.perfil') }}" class="calificaciones-nav">
                    <i class="bi bi-person-badge-fill"></i> Mi Perfil
                </a>
                <hr class="border-secondary my-3">
                <!-- CERRAR SESIÓN SEGURO -->
                <form action="{{ route('logout.seguro') }}" method="POST" class="m-0">
                    @csrf

                    <button type="submit"
                            class="calificaciones-nav text-danger"
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
                        <i class="bi bi-box-arrow-right"></i>
                        <span>Cerrar sesión</span>
                    </button>
                </form>
            </nav>
        </aside>

        <!-- CONTENIDO PRINCIPAL -->
        <main class="col-12 col-md-9 col-lg-10 p-4 p-lg-5">

            <!-- ENCABEZADO CON BOTÓN DE VOLVER (Instrucción 2 - General) -->
            <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
                <div>
                    <h2 class="text-dark fw-bold mb-2">
                        Captura de Calificaciones
                    </h2>
                    <p class="calificaciones-muted mb-0">
                        Materia: <strong class="text-dark">{{ $asignacion->materia->nombre }}</strong> | 
                        Semestre: <span class="calificaciones-etiqueta">{{ $asignacion->materia->semestre }}°</span> | 
                        Grupo: <span class="calificaciones-etiqueta">{{ $asignacion->grupo }}</span>
                    </p>
                </div>
                
                <a href="{{ route('docente.materias') }}" class="btn btn-sm btn-outline-secondary rounded-pill px-3 d-inline-flex align-items-center gap-1">
                    <i class="bi bi-arrow-left"></i> Volver a mis materias
                </a>
            </div>

            <!-- ALERTA DE ÉXITO (Instrucción Diseño: Alerta verde) -->
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show rounded-4 d-flex align-items-center gap-2 mb-4" role="alert">
                    <i class="bi bi-check-circle-fill fs-5"></i>
                    <div>{{ session('success') }}</div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            <!-- FORMULARIO EN TARJETA BLANCA (Instrucción 1 - Formulario) -->
            <div class="calificaciones-table-card">
                <form action="{{ route('docente.materias.guardar', $asignacion->id_asignacion) }}" method="POST">
                    @csrf
                    
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h5 class="fw-bold mb-0">
                            <i class="bi bi-list-ol me-2 text-dark"></i>
                            Listado de Alumnos
                        </h5>
                    </div>

                    <div class="table-responsive mb-4">
                        <table class="table-calificaciones">
                            <thead>
                                <tr>
                                    <th style="width: 5%;">NÚM.</th>
                                    <th style="width: 15%;">MATRÍCULA</th>
                                    <th style="width: 55%;">NOMBRE DEL ALUMNO</th>
                                    <th style="width: 25%; text-align: center;">CALIFICACIÓN (0-10)</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($alumnos as $index => $alumno)
                                    <tr>
                                        <td>
                                            <span class="text-muted fw-bold">{{ $index + 1 }}</span>
                                        </td>
                                        <td>
                                            <span class="fw-medium">{{ $alumno->matricula }}</span>
                                        </td>
                                        <td>
                                            <span class="fw-bold text-dark">
                                                {{ $alumno->apellido_paterno }} {{ $alumno->apellido_materno }} {{ $alumno->nombres }}
                                            </span>
                                        </td>
                                        <td class="text-center">
                                            <!-- INPUTS PEQUEÑOS Y CENTRADOS (Instrucción 2 - Formulario) -->
                                            <input type="number" 
                                                   step="0.1" 
                                                   min="0" 
                                                   max="10" 
                                                   name="calificaciones[{{ $alumno->id_alumno }}]" 
                                                   value="{{ $calificacionesPrevias[$alumno->id_alumno] ?? '' }}"
                                                   placeholder="-"
                                                   class="form-control form-control-sm input-calificacion mx-auto">
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="p-0 border-0 pt-3">
                                            <div class="calificaciones-vacio mt-2">
                                                <div class="calificaciones-vacio-icon">
                                                    <i class="bi bi-person-x"></i>
                                                </div>
                                                <h4 class="fw-bold mb-2">Sin alumnos registrados</h4>
                                                <p class="calificaciones-muted mb-0">
                                                    No hay alumnos inscritos en este grupo y semestre en este momento.
                                                </p>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- BOTÓN VERDE FLOTANDO A LA DERECHA (Instrucción 3 - Formulario) -->
                    @if($alumnos->count() > 0)
                        <div class="d-flex justify-content-end border-top pt-4 mt-2">
                            <button type="submit" class="btn btn-success rounded-pill px-4 fw-medium d-inline-flex align-items-center gap-2">
                                <i class="bi bi-floppy-fill"></i> Guardar Calificaciones
                            </button>
                        </div>
                    @endif

                </form>
            </div>

        </main>
    </div>
</div>

@endsection
@extends('layouts.app')

@section('titulo', 'Detalle de Materia - ' . $materia->nombre)

@section('usuario_header')
    <span class="fs-6">
        <i class="bi bi-person-circle me-1"></i>
        {{ Auth::user()->nombre ?? 'Alumno' }}
    </span>
@endsection

@section('contenido')

<style>
    .alumno-panel {
        min-height: 100vh;
        background-color: #f4f6f9;
    }

    /* MENÚ LATERAL */
    .alumno-sidebar {
        background: #212529;
        border-right: 1px solid #343a40;
    }

    .alumno-sidebar-title {
        font-weight: 800;
        letter-spacing: 1.2px;
        color: #ffffff;
    }

    .alumno-nav {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 13px 16px;
        border-radius: 12px;
        color: #adb5bd;
        text-decoration: none;
        transition: background .2s, color .2s;
    }

    .alumno-nav:hover {
        background: rgba(255, 255, 255, .1);
        color: #ffffff;
    }

    .alumno-nav.active {
        background: #0d6efd;
        color: #ffffff;
        font-weight: 600;
    }

    .alumno-nav i {
        font-size: 1.15rem;
    }

    .alumno-logout {
        color: #ffb3b9;
    }

    .alumno-logout:hover {
        color: #ffffff;
        background: rgba(220, 53, 69, .22);
    }

    .alumno-muted {
        color: #6c757d;
    }

    .alumno-card {
        background: #d1d5d6;
        border: 1px solid #c0c4c5;
        border-radius: 18px;
    }

    .boleta-table thead th {
        background-color: #f1f3f5;
        color: #212529;
        font-size: 0.78rem;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.3px;
        border-bottom: 2px solid #adb5bd;
        white-space: nowrap;
    }

    .boleta-table tbody td {
        font-size: 0.92rem;
        vertical-align: middle;
    }

    .boleta-nota-vacia {
        color: #adb5bd;
        font-weight: 600;
        letter-spacing: 1px;
    }
</style>

<div class="container-fluid alumno-panel">
    <div class="row min-vh-100">

        {{-- MENÚ LATERAL --}}
        <aside class="col-12 col-md-3 col-lg-2 alumno-sidebar p-4">
            <div class="text-center mb-4">
                <div class="mb-2">
                    <i class="bi bi-mortarboard-fill fs-2 text-white"></i>
                </div>
                <h5 class="alumno-sidebar-title mb-1">SGAE PREPA 5</h5>
                <small class="text-white-50">Panel del Alumno</small>
            </div>

            <hr class="border-secondary mb-4">

            <nav class="d-grid gap-2" aria-label="Menú del alumno">
                <a href="{{ route('alumno.dashboard') }}" class="alumno-nav">
                    <i class="bi bi-grid-1x2-fill"></i> Inicio
                </a>
                <a href="{{ route('alumno.materias') }}" class="alumno-nav active" aria-current="page">
                    <i class="bi bi-journal-bookmark-fill"></i> Materias
                </a>
                <a href="{{ route('alumno.documentos') }}" class="alumno-nav">
                    <i class="bi bi-folder-fill"></i> Mi Expediente
                </a>
                <a href="{{ route('alumno.solicitudes') }}" class="alumno-nav">
                    <i class="bi bi-cash-coin"></i> Trámites y Pagos
                </a>

                <hr class="border-secondary my-3">

                {{-- CIERRE DE SESIÓN SEGURO --}}
                <form action="{{ route('logout.seguro') }}" method="POST" class="m-0">
                    @csrf
                    <button type="submit"
                            class="alumno-nav alumno-logout"
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

            {{-- BOTÓN REGRESAR Y ENCABEZADO --}}
            <div class="mb-3">
                <a href="{{ route('alumno.materias') }}" class="btn btn-outline-dark btn-sm fw-bold px-3 rounded-pill shadow-sm">
                    <i class="bi bi-arrow-left me-1"></i> Volver al Kárdex
                </a>
            </div>

            <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
                <div>
                    <h2 class="fw-bold mb-1 text-dark">{{ $materia->nombre }}</h2>
                    <p class="alumno-muted mb-0">
                        {{ $semestreTexto }} &middot; Desglose de calificaciones parciales y promedio semestral.
                    </p>
                </div>

                <span class="badge bg-dark px-3 py-2 rounded-pill fs-6">
                    {{ $alumno->semestre }}° Semestre &middot; Grupo "{{ $alumno->grupo }}"
                </span>
            </div>

            {{-- TABLA DE CALIFICACIONES DE LA MATERIA --}}
            <div class="card alumno-card shadow-sm p-4">
                <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
                    <h5 class="fw-bold text-dark mb-0">
                        <i class="bi bi-table text-primary me-2"></i>
                        Calificaciones Registradas
                    </h5>
                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill px-3 py-2">
                        {{ $materia->semestre }}° Semestre
                    </span>
                </div>

                <div class="table-responsive bg-white rounded-3 border border-secondary shadow-sm">
                    <table class="table table-hover boleta-table mb-0">
                        <thead>
                            <tr>
                                <th class="ps-3">Periodo</th>
                                <th>Unidad de Aprendizaje Curricular (UAC)</th>
                                <th class="text-center">Calif. I</th>
                                <th class="text-center">Calif. II</th>
                                <th class="text-center">Calif. III</th>
                                <th class="text-center">Eval. Final</th>
                                <th class="text-center">Promedio Semestral</th>
                                <th class="text-center pe-3">Estatus</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($filasDetalle as $fila)
                                <tr>
                                    <td class="ps-3 text-muted fw-semibold">
                                        {{ $fila['periodo'] }}
                                    </td>
                                    <td class="fw-bold text-dark">
                                        {{ $materia->nombre }}
                                    </td>
                                    <td class="text-center">
                                        @if ($fila['calif_1'] !== '---')
                                            <span class="fw-bold text-dark">{{ $fila['calif_1'] }}</span>
                                        @else
                                            <span class="boleta-nota-vacia">---</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        @if ($fila['calif_2'] !== '---')
                                            <span class="fw-bold text-dark">{{ $fila['calif_2'] }}</span>
                                        @else
                                            <span class="boleta-nota-vacia">---</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        @if ($fila['calif_3'] !== '---')
                                            <span class="fw-bold text-dark">{{ $fila['calif_3'] }}</span>
                                        @else
                                            <span class="boleta-nota-vacia">---</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        @if ($fila['eval_final'] !== '---')
                                            <span class="fw-bold text-dark">{{ $fila['eval_final'] }}</span>
                                        @else
                                            <span class="boleta-nota-vacia">---</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        @if ($fila['promedio_semestral'] !== '---')
                                            <span class="badge {{ $fila['promedio_numerico'] >= 6 ? 'bg-success' : 'bg-danger' }} px-3 py-2 rounded-pill">
                                                {{ $fila['promedio_semestral'] }}
                                            </span>
                                        @else
                                            <span class="boleta-nota-vacia">---</span>
                                        @endif
                                    </td>
                                    <td class="text-center pe-3">
                                        @if ($fila['promedio_numerico'] === null)
                                            <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle rounded-pill px-3 py-2">
                                                Pendiente
                                            </span>
                                        @elseif ($fila['promedio_numerico'] >= 6)
                                            <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-3 py-2">
                                                Aprobada
                                            </span>
                                        @else
                                            <span class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill px-3 py-2">
                                                Reprobada
                                            </span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="mt-3 small alumno-muted">
                    <i class="bi bi-info-circle me-1"></i>
                    Las celdas marcadas con <strong>---</strong> corresponden a evaluaciones que aún no han sido capturadas por tu docente.
                </div>
            </div>

        </main>
    </div>
</div>
@endsection
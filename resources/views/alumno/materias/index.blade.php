@extends('layouts.app')

@section('titulo', 'Mi Kárdex y Materias')

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

    /* CONTENEDORES PRINCIPALES */
    .alumno-card {
        background: #d1d5d6;
        border: 1px solid #c0c4c5;
        border-radius: 18px;
    }

    /* RETÍCULA KÁRDEX (6 COLUMNAS) */
    .kardex-scroll {
        overflow-x: auto;
        padding-bottom: 8px;
    }

    .kardex-grid {
        display: grid;
        grid-template-columns: repeat(6, minmax(155px, 1fr));
        gap: 14px;
        min-width: 980px;
    }

    .kardex-semestre-header {
        background-color: #b6915d;
        color: #ffffff;
        text-align: center;
        font-weight: 800;
        font-size: 0.95rem;
        padding: 8px 12px;
        border-radius: 10px;
        margin-bottom: 14px;
        letter-spacing: 0.5px;
    }

    .kardex-materia-card {
        background-color: #d6e4ff;
        border: 1px solid #6b9bff;
        border-left: 5px solid #2b59c3;
        border-radius: 12px;
        padding: 12px 14px;
        min-height: 112px;
        margin-bottom: 12px;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        text-decoration: none;
        color: inherit;
        cursor: pointer;
        box-shadow: 0 2px 6px rgba(0, 0, 0, .06);
        transition: transform .15s ease, box-shadow .15s ease, background-color .15s ease;
    }

    .kardex-materia-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 14px rgba(0, 0, 0, .12);
        background-color: #c8dcff;
        color: inherit;
    }

    .kardex-materia-nombre {
        font-size: 0.78rem;
        font-weight: 800;
        color: #111827;
        text-transform: uppercase;
        line-height: 1.3;
        word-break: break-word;
    }

    .kardex-materia-footer {
        display: flex;
        align-items: flex-end;
        justify-content: space-between;
        gap: 8px;
        margin-top: 14px;
    }

    .kardex-materia-calificacion {
        font-size: 1.15rem;
        font-weight: 800;
        color: #111827;
        line-height: 1;
    }

    .kardex-materia-link {
        font-size: 0.72rem;
        font-weight: 700;
        color: #2b59c3;
        white-space: nowrap;
    }

    .kardex-vacio {
        background-color: #f8f9fa;
        border: 1px dashed #ced4da;
        border-radius: 12px;
        padding: 18px 10px;
        text-align: center;
        color: #9aa0a6;
        font-size: 0.8rem;
    }

    /* TARJETAS DE BOLETAS POR PERIODO */
    .periodo-doc-card {
        background: #ffffff;
        border: 1px solid #d7dce1;
        border-radius: 16px;
        padding: 18px;
        height: 100%;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        gap: 14px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, .05);
        transition: transform .18s ease, box-shadow .18s ease, border-color .18s ease;
    }

    .periodo-doc-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 18px rgba(0, 0, 0, .09);
    }

    .periodo-doc-card.es-actual {
        border: 2px solid #0d6efd;
    }

    .periodo-doc-titulo {
        font-size: 0.92rem;
        font-weight: 800;
        color: #111827;
        line-height: 1.3;
    }

    .periodo-doc-meta {
        font-size: 0.76rem;
        color: #6c757d;
        font-weight: 600;
    }

    .btn-boleta-pdf {
        background-color: #0d6efd;
        border: none;
        color: #ffffff;
        font-weight: 700;
        border-radius: 10px;
        padding: 9px 14px;
        transition: background-color .2s;
    }

    .btn-boleta-pdf:hover {
        background-color: #0b5ed7;
        color: #ffffff;
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

            {{-- ENCABEZADO --}}
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
                <div>
                    <h2 class="fw-bold mb-1 text-dark">Mi Kárdex Académico</h2>
                    <p class="alumno-muted mb-0">
                        {{ $alumno->nombres }} {{ $alumno->apellido_paterno }} {{ $alumno->apellido_materno }}
                        &middot; Matrícula: <strong>{{ $alumno->matricula }}</strong>
                    </p>
                </div>

                <span class="badge bg-dark px-3 py-2 rounded-pill fs-6">
                    {{ $alumno->semestre }}° Semestre &middot; Grupo "{{ $alumno->grupo }}"
                </span>
            </div>

            {{-- SECCIÓN 1: KÁRDEX ACADÉMICO (1° A 6° SEMESTRE) --}}
            <div class="card alumno-card shadow-sm p-4 mb-4">
                <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
                    <h5 class="fw-bold text-dark mb-0">
                        <i class="bi bi-journal-bookmark-fill text-primary me-2"></i>
                        Materias por Semestre
                    </h5>
                    <small class="alumno-muted">
                        Haz clic en cualquier materia para consultar el desglose de sus calificaciones.
                    </small>
                </div>

                <div class="bg-white rounded-3 border border-secondary p-4 shadow-sm">
                    <div class="kardex-scroll">
                        <div class="kardex-grid">

                            @for ($semestre = 1; $semestre <= 6; $semestre++)
                                <div>
                                    {{-- ENCABEZADO DEL SEMESTRE --}}
                                    <div class="kardex-semestre-header">
                                        0{{ $semestre }}
                                    </div>

                                    {{-- TARJETAS CLICABLES DE MATERIAS --}}
                                    @if (isset($materias[$semestre]) && $materias[$semestre]->isNotEmpty())
                                        @foreach ($materias[$semestre] as $materia)
                                            @php
                                                $nota = $calificaciones[$materia->id_materia] ?? null;
                                            @endphp

                                            <a href="{{ route('alumno.materias.detalle', $materia->id_materia) }}"
                                               class="kardex-materia-card">
                                                <div class="kardex-materia-nombre">
                                                    {{ $materia->nombre }}
                                                </div>

                                                <div class="kardex-materia-footer">
                                                    <div class="kardex-materia-calificacion">
                                                        @if ($nota !== null)
                                                            {{ rtrim(rtrim(number_format((float) $nota, 2, '.', ''), '0'), '.') }}
                                                        @else
                                                            <span class="text-muted fw-normal">-</span>
                                                        @endif
                                                    </div>

                                                    <span class="kardex-materia-link">
                                                        Detalle <i class="bi bi-chevron-right"></i>
                                                    </span>
                                                </div>
                                            </a>
                                        @endforeach
                                    @else
                                        <div class="kardex-vacio">
                                            Sin materias
                                        </div>
                                    @endif
                                </div>
                            @endfor

                        </div>
                    </div>
                </div>
            </div>

            {{-- SECCIÓN 2: BOLETAS ORGANIZADAS POR PERIODO --}}
            <div class="card alumno-card shadow-sm p-4">
                <div class="bg-white rounded-3 border border-secondary p-4 shadow-sm">
                    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
                        <h5 class="fw-bold text-dark mb-0">
                            <i class="bi bi-folder2-open text-primary me-2"></i>
                            Boletas por periodo
                        </h5>
                        <small class="alumno-muted">
                            Descarga tu boleta oficial en formato PDF correspondiente a cada periodo escolar.
                        </small>
                    </div>

                    <div class="row g-3">
                        @foreach ($periodosDisponibles as $item)
                            <div class="col-12 col-sm-6 col-lg-4 col-xl-3">
                                <div class="periodo-doc-card {{ $item['es_actual'] ? 'es-actual' : '' }}">
                                    <div>
                                        <div class="d-flex justify-content-between align-items-start gap-2 mb-1">
                                            <span class="periodo-doc-titulo">
                                                {{ $item['periodo'] }}
                                            </span>
                                            @if ($item['es_actual'])
                                                <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill">
                                                    actual
                                                </span>
                                            @endif
                                        </div>
                                        <div class="periodo-doc-meta">
                                            {{ $item['semestre_etiqueta'] }} &middot; {{ $item['total_materias'] }} {{ $item['total_materias'] === 1 ? 'materia' : 'materias' }}
                                        </div>
                                    </div>

                                    <div class="d-grid">
                                        <a href="{{ route('alumno.materias.boleta', ['periodo' => $item['periodo']]) }}"
                                           class="btn btn-boleta-pdf shadow-sm text-center text-decoration-none">
                                            <i class="bi bi-file-earmark-pdf-fill me-1"></i>
                                            Boleta (PDF)
                                        </a>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

        </main>
    </div>
</div>
@endsection
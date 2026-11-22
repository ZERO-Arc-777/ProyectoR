@extends('layouts.app')

@section('titulo', 'Mi Expediente Digital')

@section('usuario_header')
    <span class="fs-6">
        <i class="bi bi-person-circle me-1"></i>
        {{ Auth::user()->nombre ?? 'Alumno' }}
    </span>
@endsection

@section('contenido')

<style>
    .alumno-panel { min-height: 100vh; background-color: #f4f6f9; }

    /* MENÚ LATERAL */
    .alumno-sidebar { background: #212529; border-right: 1px solid #343a40; }
    .alumno-sidebar-title { font-weight: 800; letter-spacing: 1.2px; color: #ffffff; }

    .alumno-nav {
        display: flex; align-items: center; gap: 12px; padding: 13px 16px;
        border-radius: 12px; color: #adb5bd; text-decoration: none; transition: .2s;
    }
    .alumno-nav:hover { background: rgba(255, 255, 255, .1); color: #ffffff; }
    .alumno-nav.active { background: #0d6efd; color: #ffffff; font-weight: 600; }
    .alumno-nav i { font-size: 1.15rem; }

    .alumno-muted { color: #6c757d; }
    .alumno-logout { color: #ffb3b9; }
    .alumno-logout:hover { color: #ffffff; background: rgba(220, 53, 69, .22); }

    /* BARRA INFORMATIVA SUPERIOR */
    .exp-banner {
        background: #ffffff;
        border: 1px solid #dfe3e8;
        border-left: 5px solid #0d6efd;
        border-radius: 16px;
        padding: 16px 22px;
    }

    /* SECCIONES DE TRÁMITE */
    .exp-section {
        background: #d1d5d6;
        border: 1px solid #c0c4c5;
        border-radius: 18px;
        padding: 22px;
    }

    .exp-section-locked {
        background: #e2e5e7;
        border-color: #cfd4d8;
    }

    /* TARJETAS FLOTANTES POR DOCUMENTO */
    .doc-card {
        background: #ffffff;
        border: 1px solid #dfe3e8;
        border-radius: 16px;
        padding: 20px;
        height: 100%;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        box-shadow: 0 4px 12px rgba(0, 0, 0, .05);
        transition: transform .2s, box-shadow .2s, border-color .2s;
    }

    .doc-card:not(.doc-card-disabled):hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 18px rgba(0, 0, 0, .08);
        border-color: #0d6efd;
    }

    .doc-card-disabled {
        background: #f8f9fa;
        opacity: .85;
    }

    .doc-card-icon {
        width: 46px;
        height: 46px;
        border-radius: 12px;
        display: grid;
        place-items: center;
        font-size: 1.35rem;
        flex-shrink: 0;
    }

    .doc-icon-disponible { background: rgba(13, 110, 253, .12); color: #0d6efd; }
    .doc-icon-pendiente  { background: rgba(255, 193, 7, .2); color: #997404; }
    .doc-icon-aprobado   { background: rgba(25, 135, 84, .15); color: #198754; }
    .doc-icon-rechazado  { background: rgba(220, 53, 69, .15); color: #dc3545; }
    .doc-icon-bloqueado  { background: rgba(108, 117, 125, .15); color: #6c757d; }

    .doc-dropzone {
        background: #f4f6f9;
        border: 1px dashed #b8c0c8;
        border-radius: 12px;
        padding: 10px 12px;
    }

    .doc-dropzone .form-control {
        background: transparent;
        border: none;
        padding: 0;
        font-size: .88rem;
        box-shadow: none;
    }

    .doc-status-box {
        background: #f4f6f9;
        border: 1px solid #dfe3e8;
        border-radius: 12px;
        padding: 12px 14px;
        font-size: .88rem;
    }

    /* TARJETA DE HISTORIAL */
    .alumno-card-table {
        background: #ffffff;
        border: 1px solid #e0e4e5;
        border-radius: 18px;
    }
</style>

<div class="container-fluid alumno-panel">
    <div class="row min-vh-100">

        <!-- MENÚ LATERAL -->
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
                <a href="{{ route('alumno.materias') }}" class="alumno-nav">
                    <i class="bi bi-journal-bookmark-fill"></i> Materias
                </a>
                <a href="{{ route('alumno.documentos') }}" class="alumno-nav active" aria-current="page">
                    <i class="bi bi-folder-fill"></i> Mi Expediente
                </a>
                <a href="{{ route('alumno.solicitudes') }}" class="alumno-nav">
                    <i class="bi bi-cash-coin"></i> Trámites y Pagos
                </a>
                <hr class="border-secondary my-3">

                <!-- CERRAR SESIÓN SEGURO -->
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

        <!-- CONTENIDO PRINCIPAL -->
        <main class="col-12 col-md-9 col-lg-10 p-4 p-lg-5">

            <!-- ENCABEZADO -->
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
                <div>
                    <h2 class="fw-bold mb-1 text-dark">Mi Expediente Digital</h2>
                    <p class="alumno-muted mb-0">Sube y da seguimiento a tus documentos oficiales según tu semestre.</p>
                </div>
                <span class="badge bg-dark px-3 py-2 rounded-pill fs-6">
                    <i class="bi bi-mortarboard me-1 text-primary"></i>
                    Semestre actual: {{ $alumno->semestre }}° {{ $alumno->grupo }}
                </span>
            </div>

            <!-- ALERTAS -->
            @if(session('success'))
                <div class="alert alert-success fw-bold rounded-3 shadow-sm border-0 mb-4">
                    <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger fw-bold rounded-3 shadow-sm border-0 mb-4">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i> {{ session('error') }}
                </div>
            @endif

            @if ($errors->any())
                <div class="alert alert-danger fw-bold rounded-3 shadow-sm border-0 mb-4">
                    <div class="d-flex align-items-center mb-2">
                        <i class="bi bi-exclamation-triangle-fill me-2 fs-5"></i>
                        <span>Por favor, corrige los siguientes errores:</span>
                    </div>
                    <ul class="mb-0 ps-4">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <!-- BARRA INFORMATIVA DE FORMATO -->
            <div class="exp-banner shadow-sm d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
                <div class="fw-bold text-dark">
                    <i class="bi bi-cloud-arrow-up-fill text-primary me-2"></i>
                    Subir documentos del expediente
                </div>
                <div class="small alumno-muted fw-semibold">
                    Solo formato PDF · Máximo 2 MB por documento
                </div>
            </div>

            <!-- TARJETAS FLOTANTES AGRUPADAS POR TRÁMITE -->
            <div class="d-flex flex-column gap-4 mb-5">
                @foreach ($secciones as $seccion)
                    <section class="exp-section shadow-sm {{ !$seccion['habilitado'] ? 'exp-section-locked' : '' }}">
                        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
                            <div>
                                <h5 class="fw-bold text-dark mb-1">
                                    <i class="bi {{ $seccion['icono'] }} me-2 text-primary"></i>
                                    {{ $seccion['titulo'] }}
                                </h5>
                                <p class="small alumno-muted mb-0">
                                    {{ $seccion['descripcion'] }}
                                </p>
                            </div>

                            @if (!$seccion['habilitado'])
                                <span class="badge bg-secondary px-3 py-2 rounded-pill">
                                    <i class="bi bi-lock-fill me-1"></i>
                                    {{ $seccion['etiqueta_bloqueo'] }}
                                </span>
                            @elseif ($seccion['por_semestre'])
                                <span class="badge bg-primary px-3 py-2 rounded-pill">
                                    <i class="bi bi-calendar2-check me-1"></i>
                                    Ciclo {{ $alumno->semestre }}° semestre
                                </span>
                            @else
                                <span class="badge bg-dark px-3 py-2 rounded-pill">
                                    <i class="bi bi-unlock-fill me-1 text-success"></i>
                                    Habilitado
                                </span>
                            @endif
                        </div>

                        <div class="row g-3">
                            @foreach ($seccion['tarjetas'] as $tarjeta)
                                @php
                                    $estado = $tarjeta['estado'];
                                    $registro = $tarjeta['registro'];
                                    $puedeSubir = $tarjeta['puede_subir'];

                                    $claseIcono = match ($estado) {
                                        'PENDIENTE' => 'doc-icon-pendiente',
                                        'APROBADO'  => 'doc-icon-aprobado',
                                        'RECHAZADO' => 'doc-icon-rechazado',
                                        'BLOQUEADO' => 'doc-icon-bloqueado',
                                        default     => 'doc-icon-disponible',
                                    };
                                @endphp

                                <div class="col-12 col-md-6 col-xl-4">
                                    <div class="doc-card {{ !$puedeSubir ? 'doc-card-disabled' : '' }}">
                                        <div>
                                            <!-- CABECERA DE TARJETA -->
                                            <div class="d-flex align-items-start gap-3 mb-3">
                                                <div class="doc-card-icon {{ $claseIcono }}">
                                                    @if ($estado === 'BLOQUEADO')
                                                        <i class="bi bi-lock-fill"></i>
                                                    @elseif ($estado === 'APROBADO')
                                                        <i class="bi bi-file-earmark-check-fill"></i>
                                                    @else
                                                        <i class="bi bi-file-earmark-pdf-fill"></i>
                                                    @endif
                                                </div>

                                                <div class="min-w-0">
                                                    <h6 class="fw-bold text-dark mb-1">
                                                        {{ $tarjeta['tipo_documento'] }}
                                                    </h6>

                                                    <div class="d-flex flex-wrap align-items-center gap-2">
                                                        @if ($estado === 'DISPONIBLE')
                                                            <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill">
                                                                <i class="bi bi-upload me-1"></i> Disponible
                                                            </span>
                                                        @elseif ($estado === 'PENDIENTE')
                                                            <span class="badge bg-warning text-dark rounded-pill">
                                                                <i class="bi bi-hourglass-split me-1"></i> En Revisión
                                                            </span>
                                                        @elseif ($estado === 'APROBADO')
                                                            <span class="badge bg-success rounded-pill">
                                                                <i class="bi bi-check-circle-fill me-1"></i> Aprobado
                                                            </span>
                                                        @elseif ($estado === 'RECHAZADO')
                                                            <span class="badge bg-danger rounded-pill">
                                                                <i class="bi bi-x-circle-fill me-1"></i> Rechazado
                                                            </span>
                                                        @else
                                                            <span class="badge bg-secondary rounded-pill">
                                                                <i class="bi bi-lock-fill me-1"></i> Bloqueado
                                                            </span>
                                                        @endif

                                                        <span class="small text-muted fw-semibold">PDF</span>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- MOTIVO DE RECHAZO SI EXISTE -->
                                            @if ($estado === 'RECHAZADO' && $registro)
                                                <div class="alert alert-danger py-2 px-3 small mb-3 rounded-3 border-0">
                                                    <strong>Motivo de rechazo:</strong>
                                                    {{ $registro->observaciones ?: 'Revisa el archivo y súbelo nuevamente.' }}
                                                </div>
                                            @endif
                                        </div>

                                        <!-- ZONA DE ACCIÓN -->
                                        @if ($puedeSubir)
                                            <form action="{{ route('alumno.documentos.store') }}"
                                                  method="POST"
                                                  enctype="multipart/form-data"
                                                  class="mt-auto">
                                                @csrf
                                                <input type="hidden" name="proceso" value="{{ $tarjeta['proceso'] }}">
                                                <input type="hidden" name="tipo_documento" value="{{ $tarjeta['tipo_documento'] }}">

                                                <div class="doc-dropzone mb-3">
                                                    <input type="file"
                                                           name="archivo"
                                                           class="form-control"
                                                           accept=".pdf,application/pdf"
                                                           required>
                                                </div>

                                                <div class="d-flex gap-2">
                                                    <button type="submit"
                                                            class="btn {{ $estado === 'RECHAZADO' ? 'btn-danger' : 'btn-primary' }} fw-bold flex-grow-1 shadow-sm">
                                                        <i class="bi bi-upload me-1"></i>
                                                        {{ $estado === 'RECHAZADO' ? 'Volver a subir PDF' : 'Subir PDF' }}
                                                    </button>

                                                    @if ($registro)
                                                        <a href="{{ route('documentos.ver', ['id_documento' => $registro->id_documento]) }}"
                                                           target="_blank"
                                                           rel="noopener noreferrer"
                                                           class="btn btn-outline-secondary fw-bold"
                                                           title="Ver archivo anterior">
                                                            <i class="bi bi-eye"></i>
                                                        </a>
                                                    @endif
                                                </div>
                                            </form>
                                        @elseif ($estado === 'PENDIENTE' && $registro)
                                            <div class="mt-auto">
                                                <div class="doc-status-box text-muted mb-3">
                                                    <i class="bi bi-clock-history me-1 text-warning"></i>
                                                    Enviado el {{ $registro->created_at->format('d/m/Y') }}. Esta tarjeta se reactivará solo si Control Escolar solicita corrección.
                                                </div>
                                                <div class="d-flex gap-2">
                                                    <button type="button" class="btn btn-secondary fw-bold flex-grow-1" disabled>
                                                        <i class="bi bi-hourglass-split me-1"></i> En revisión
                                                    </button>
                                                    <a href="{{ route('documentos.ver', ['id_documento' => $registro->id_documento]) }}"
                                                       target="_blank"
                                                       rel="noopener noreferrer"
                                                       class="btn btn-outline-dark fw-bold">
                                                        <i class="bi bi-file-earmark-pdf-fill text-danger me-1"></i> Ver PDF
                                                    </a>
                                                </div>
                                            </div>
                                        @elseif ($estado === 'APROBADO' && $registro)
                                            <div class="mt-auto">
                                                <div class="doc-status-box text-success fw-semibold mb-3">
                                                    <i class="bi bi-patch-check-fill me-1"></i>
                                                    Validado correctamente por Control Escolar.
                                                </div>
                                                <div class="d-flex gap-2">
                                                    <button type="button" class="btn btn-success fw-bold flex-grow-1" disabled>
                                                        <i class="bi bi-check-lg me-1"></i> Completado
                                                    </button>
                                                    <a href="{{ route('documentos.ver', ['id_documento' => $registro->id_documento]) }}"
                                                       target="_blank"
                                                       rel="noopener noreferrer"
                                                       class="btn btn-outline-dark fw-bold">
                                                        <i class="bi bi-file-earmark-pdf-fill text-danger me-1"></i> Ver PDF
                                                    </a>
                                                </div>
                                            </div>
                                        @else
                                            <div class="mt-auto">
                                                <div class="doc-status-box text-muted mb-3">
                                                    <i class="bi bi-info-circle me-1"></i>
                                                    {{ $seccion['etiqueta_bloqueo'] }}.
                                                </div>
                                                <button type="button" class="btn btn-secondary fw-bold w-100" disabled>
                                                    <i class="bi bi-lock-fill me-1"></i> No disponible aún
                                                </button>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </section>
                @endforeach
            </div>

            <!-- TABLA DE HISTORIAL COMPLETO -->
            <div class="alumno-card-table shadow-sm p-4">
                <h5 class="fw-bold text-dark mb-4">
                    <i class="bi bi-clock-history me-2 text-primary"></i> Historial de mis Documentos
                </h5>

                <div class="table-responsive">
                    <table class="table table-hover align-middle border-secondary mb-0">
                        <thead class="table-light text-muted small">
                            <tr>
                                <th>TRÁMITE</th>
                                <th>DOCUMENTO</th>
                                <th>ESTADO</th>
                                <th>OBSERVACIONES</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($documentos as $doc)
                                <tr>
                                    <td>
                                        <span class="badge bg-secondary text-uppercase">{{ $doc->proceso }}</span>
                                        @if (!empty($doc->semestre))
                                            <span class="badge bg-light text-dark border ms-1">{{ $doc->semestre }}° sem.</span>
                                        @endif
                                        <br>
                                        <small class="text-muted">{{ $doc->created_at->format('d/m/Y') }}</small>
                                    </td>
                                    <td>
                                        <span class="fw-bold d-block">{{ $doc->tipo_documento }}</span>
                                        <a href="{{ route('documentos.ver', ['id_documento' => $doc->id_documento]) }}"
                                           target="_blank"
                                           rel="noopener noreferrer"
                                           class="text-decoration-none text-primary fw-bold small">
                                            <i class="bi bi-file-earmark-pdf-fill"></i> Ver PDF
                                        </a>
                                    </td>
                                    <td>
                                        @if($doc->estado_validacion == 'PENDIENTE')
                                            <span class="badge bg-warning text-dark"><i class="bi bi-hourglass-split"></i> En Revisión</span>
                                        @elseif($doc->estado_validacion == 'APROBADO')
                                            <span class="badge bg-success"><i class="bi bi-check-circle-fill"></i> Aprobado</span>
                                        @else
                                            <span class="badge bg-danger"><i class="bi bi-x-circle-fill"></i> Rechazado</span>
                                        @endif
                                    </td>
                                    <td class="text-muted small" style="max-width: 240px;">
                                        {{ $doc->observaciones ?? 'Sin comentarios por parte de control escolar.' }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center py-5">
                                        <i class="bi bi-folder-x fs-1 text-muted opacity-50 d-block mb-2"></i>
                                        <span class="fw-bold text-muted">Aún no has subido ningún documento a tu expediente.</span>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

        </main>
    </div>
</div>
@endsection
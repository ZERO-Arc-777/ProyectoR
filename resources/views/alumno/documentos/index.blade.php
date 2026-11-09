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

    /* TARJETAS DE CONTENIDO */
    .alumno-card-form {
        background: #d1d5d6; border: 1px solid #c0c4c5; border-radius: 18px;
    }
    .alumno-card-table {
        background: #ffffff; border: 1px solid #e0e4e5; border-radius: 18px;
    }
    .alumno-muted { color: #6c757d; }
    .alumno-logout { color: #ffb3b9; }
    .alumno-logout:hover { color: #ffffff; background: rgba(220, 53, 69, .22); }
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
                <!-- Pestaña Activa -->
                <a href="{{ route('alumno.documentos') }}" class="alumno-nav active" aria-current="page">
                    <i class="bi bi-folder-fill"></i> Mi Expediente
                </a>
                <a href="{{ route('alumno.solicitudes') }}" class="alumno-nav">
                    <i class="bi bi-cash-coin"></i> Trámites y Pagos
                </a>
                <hr class="border-secondary my-3">
                <a href="{{ route('logout') }}" class="alumno-nav alumno-logout">
                    <i class="bi bi-box-arrow-right"></i> Cerrar sesión
                </a>
            </nav>
        </aside>

        <!-- CONTENIDO PRINCIPAL -->
        <main class="col-12 col-md-9 col-lg-10 p-4 p-lg-5">

            <!-- ENCABEZADO -->
            <div class="mb-4">
                <h2 class="fw-bold mb-1 text-dark">Mi Expediente Digital</h2>
                <p class="alumno-muted mb-0">Gestiona y actualiza tus documentos personales oficiales.</p>
            </div>

            <!-- ALERTAS DE ÉXITO O ERROR (Lógica Anti-Spam) -->
            @if(session('success'))
                <div class="alert alert-success fw-bold rounded-3 shadow-sm border-0">
                    <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
                </div>
            @endif

            @if ($errors->any())
                <div class="alert alert-danger fw-bold rounded-3 shadow-sm border-0">
                    <ul class="mb-0 ps-3">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="row g-4">
                
                <!-- COLUMNA IZQUIERDA: FORMULARIO DE SUBIDA -->
                <div class="col-12 col-xl-4">
                    <div class="alumno-card-form overflow-hidden shadow-sm h-100">
                        <div class="bg-dark text-white p-3 text-center">
                            <h6 class="mb-0 fw-bold"><i class="bi bi-cloud-arrow-up-fill me-2"></i>SUBIR DOCUMENTO</h6>
                        </div>
                        <div class="p-4">
                            <!-- enctype vital para que el PDF viaje al servidor -->
                            <form action="{{ route('alumno.documentos.store') }}" method="POST" enctype="multipart/form-data">
                                @csrf
                                
                                <div class="mb-3 text-start">
                                    <label class="fw-bold text-dark mb-1 ms-1 small">Proceso Académico:</label>
                                    <select name="proceso" class="form-select border-0 shadow-sm" required>
                                        <option value="">-- Selecciona el trámite --</option>
                                        <option value="INSCRIPCION">Nuevo Ingreso (Inscripción)</option>
                                        <option value="REINSCRIPCION">Pase de Semestre (Reinscripción)</option>
                                        <option value="EGRESO">Trámites Finales (Egreso)</option>
                                    </select>
                                </div>

                                <div class="mb-3 text-start">
                                    <label class="fw-bold text-dark mb-1 ms-1 small">Tipo de Documento:</label>
                                    <select name="tipo_documento" class="form-select border-0 shadow-sm" required>
                                        <option value="">-- ¿Qué vas a subir? --</option>
                                        <option value="Acta de Nacimiento">Acta de Nacimiento</option>
                                        <option value="CURP">CURP</option>
                                        <option value="Certificado de Secundaria">Certificado de Secundaria</option>
                                        <option value="Comprobante de Domicilio">Comprobante de Domicilio</option>
                                    </select>
                                </div>

                                <div class="mb-4 text-start">
                                    <label class="fw-bold text-dark mb-1 ms-1 small">Archivo (Solo PDF, Máx. 2MB):</label>
                                    <input type="file" name="archivo" class="form-control border-0 shadow-sm" accept=".pdf" required>
                                </div>

                                <div class="d-grid">
                                    <button type="submit" class="btn btn-primary fw-bold text-uppercase shadow-sm">
                                        Enviar a Revisión
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- COLUMNA DERECHA: TABLA DE HISTORIAL -->
                <div class="col-12 col-xl-8">
                    <div class="alumno-card-table shadow-sm h-100 p-4">
                        <h5 class="fw-bold text-dark mb-4">
                            <i class="bi bi-clock-history me-2 text-primary"></i> Historial de mis Documentos
                        </h5>
                        
                        <div class="table-responsive">
                            <table class="table table-hover align-middle border-secondary">
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
                                                <br>
                                                <small class="text-muted">{{ $doc->created_at->format('d/m/Y') }}</small>
                                            </td>
                                            <td>
                                                <span class="fw-bold d-block">{{ $doc->tipo_documento }}</span>
                                                <a href="{{ asset('storage/' . $doc->ruta_archivo) }}" target="_blank" class="text-decoration-none text-primary fw-bold small">
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
                                            <td class="text-muted small" style="max-width: 200px;">
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
                </div>

            </div>
        </main>
    </div>
</div>
@endsection
@extends('layouts.app')

@section('titulo', 'Mis Solicitudes de Trámites')

@section('usuario_header')
    <span class="fs-6">
        <i class="bi bi-person-circle me-1"></i>
        {{ Auth::user()->nombre ?? 'Alumno' }}
    </span>
@endsection

@section('contenido')

<style>
    .alumno-panel { min-height: 100vh; background-color: #f4f6f9; }
    
    .alumno-sidebar { background: #212529; border-right: 1px solid #343a40; }
    .alumno-sidebar-title { font-weight: 800; letter-spacing: 1.2px; color: #ffffff; }
    
    .alumno-nav {
        display: flex; align-items: center; gap: 12px; padding: 13px 16px;
        border-radius: 12px; color: #adb5bd; text-decoration: none; transition: .2s;
    }
    .alumno-nav:hover { background: rgba(255, 255, 255, .1); color: #ffffff; }
    .alumno-nav.active { background: #0d6efd; color: #ffffff; font-weight: 600; }
    .alumno-nav i { font-size: 1.15rem; }

    .alumno-card {
        background: #d1d5d6; border: 1px solid #c0c4c5; border-radius: 18px;
    }
    .alumno-muted { color: #6c757d; }
    .alumno-logout { color: #ffb3b9; }
    .alumno-logout:hover { color: #ffffff; background: rgba(220, 53, 69, .22); }
</style>

<div class="container-fluid alumno-panel">
    <div class="row min-vh-100">

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
                <a href="{{ route('alumno.documentos') }}" class="alumno-nav">
                    <i class="bi bi-folder-fill"></i> Mi Expediente
                </a>
                <a href="{{ route('alumno.solicitudes') }}" class="alumno-nav active" aria-current="page">
                    <i class="bi bi-cash-coin"></i> Trámites y Pagos
                </a>
                <hr class="border-secondary my-3">
                <a href="{{ route('logout') }}" class="alumno-nav alumno-logout">
                    <i class="bi bi-box-arrow-right"></i> Cerrar sesión
                </a>
            </nav>
        </aside>

        <main class="col-12 col-md-9 col-lg-10 p-4 p-lg-5">

            <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
                <div>
                    <h2 class="fw-bold mb-1 text-dark">Mis Solicitudes de Trámites</h2>
                    <p class="alumno-muted mb-0">Historial de fichas generadas y estado de tus pagos.</p>
                </div>
                <a href="{{ route('alumno.solicitudes.create') }}" class="btn btn-primary fw-bold px-4 py-2 shadow-sm">
                    <i class="bi bi-plus-circle me-1"></i> Generar Nueva Solicitud
                </a>
            </div>

            @if(session('success'))
                <div class="alert alert-success fw-bold rounded-3 shadow-sm border-0 mb-4">
                    <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
                </div>
            @endif

            <div class="card alumno-card shadow-sm p-4">
                <h5 class="fw-bold text-dark mb-4">
                    <i class="bi bi-receipt me-2 text-primary"></i> Historial de Solicitudes
                </h5>
                
                <div class="table-responsive bg-white rounded-3 border border-secondary shadow-sm">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light text-muted small">
                            <tr>
                                <th>FOLIO</th>
                                <th>FECHA DE SOLICITUD</th>
                                <th class="text-end">TOTAL A PAGAR</th>
                                <th class="text-center">ESTADO</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($solicitudes as $solicitud)
                                <tr>
                                    <td class="fw-bold text-dark">
                                        {{ $solicitud->folio }}
                                    </td>
                                    <td class="text-muted">
                                        <i class="bi bi-calendar-event me-1"></i>
                                        {{ $solicitud->created_at->format('d/m/Y') }} 
                                        <small class="ms-1">{{ $solicitud->created_at->format('h:i A') }}</small>
                                    </td>
                                    <td class="text-end fw-bold fs-5 text-success">
                                        ${{ number_format($solicitud->total_pagar, 2) }}
                                    </td>
                                    <td class="text-center">
                                        @if($solicitud->estado == 'PENDIENTE')
                                            <span class="badge bg-warning text-dark px-3 py-2 rounded-pill">
                                                <i class="bi bi-hourglass-split me-1"></i> Pendiente
                                            </span>
                                        @elseif($solicitud->estado == 'PAGADA')
                                            <span class="badge bg-success px-3 py-2 rounded-pill">
                                                <i class="bi bi-check-circle-fill me-1"></i> Pagada
                                            </span>
                                        @else
                                            <span class="badge bg-danger px-3 py-2 rounded-pill">
                                                <i class="bi bi-x-circle-fill me-1"></i> Cancelada
                                            </span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center py-5">
                                        <i class="bi bi-receipt fs-1 text-muted opacity-50 d-block mb-2"></i>
                                        <span class="fw-bold text-muted">Aún no tienes solicitudes generadas.</span>
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
@extends('layouts.app')

@section('titulo', 'Generar Solicitud de Pago')

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

            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h2 class="fw-bold mb-1 text-dark">Generar Nueva Solicitud</h2>
                    <p class="alumno-muted mb-0">Selecciona los conceptos que deseas incluir en tu ficha de pago.</p>
                </div>
                <a href="{{ route('alumno.solicitudes') }}" class="btn btn-secondary fw-bold">
                    <i class="bi bi-arrow-left me-1"></i> Volver
                </a>
            </div>

            @if ($errors->any())
                <div class="alert alert-danger fw-bold rounded-3 shadow-sm border-0 mb-4">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i> Debes seleccionar al menos un servicio para generar la solicitud.
                </div>
            @endif

            <div class="card alumno-card shadow-sm">
                <div class="card-header bg-dark text-white p-3 text-center" style="border-top-left-radius: 18px; border-top-right-radius: 18px;">
                    <h5 class="mb-0 fw-bold"><i class="bi bi-card-checklist me-2"></i>CATÁLOGO DE TRÁMITES Y SERVICIOS</h5>
                </div>
                
                <div class="card-body p-4">
                    <form action="{{ route('alumno.solicitudes.store') }}" method="POST">
                        @csrf
                        
                        <div class="table-responsive bg-white rounded-3 border border-secondary shadow-sm mb-4">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light text-muted small">
                                    <tr>
                                        <th class="text-center" style="width: 80px;">AGREGAR</th>
                                        <th>ARTÍCULO</th>
                                        <th>CONCEPTO</th>
                                        <th class="text-end">TOTAL</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($servicios as $servicio)
                                        <tr>
                                            <td class="text-center">
                                                <div class="form-check d-flex justify-content-center m-0">
                                                    <input class="form-check-input border-secondary" 
                                                           type="checkbox" 
                                                           name="servicios[]" 
                                                           value="{{ $servicio->id_servicio }}" 
                                                           style="width: 1.5em; height: 1.5em; cursor: pointer;">
                                                </div>
                                            </td>
                                            <td class="fw-bold text-dark">{{ $servicio->articulo }}</td>
                                            <td class="text-muted">{{ $servicio->concepto }}</td>
                                            <td class="text-end fw-bold text-success fs-5">
                                                ${{ number_format($servicio->total, 2) }}
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        
                        <div class="d-flex justify-content-end align-items-center">
                            <button type="submit" class="btn btn-primary fw-bold px-4 py-2 text-uppercase shadow-sm">
                                <i class="bi bi-receipt me-2"></i> Generar Solicitud
                            </button>
                        </div>
                        
                    </form>
                </div>
            </div>

        </main>
    </div>
</div>
@endsection
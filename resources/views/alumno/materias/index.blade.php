@extends('layouts.app')

@section('titulo', 'Mi Kárdex')

@section('usuario_header')
    <span class="fs-6">
        <i class="bi bi-person-circle me-1"></i>
        {{ Auth::user()->nombre ?? 'Alumno' }}
    </span>
@endsection

@section('contenido')

<style>
    /* VARIABLES Y ESTILOS BASE DEL PANEL */
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

    .alumno-muted { color: #6c757d; }
    .alumno-logout { color: #ffb3b9; }
    .alumno-logout:hover { color: #ffffff; background: rgba(220, 53, 69, .22); }

    /* ESTILOS ESPECÍFICOS DEL KÁRDEX */
    .kardex-wrapper {
        background: #ffffff;
        border: 1px solid #c0c4c5;
        border-radius: 18px;
        padding: 24px;
        box-shadow: 0 4px 6px rgba(0,0,0,0.02);
    }

    .kardex-container {
        overflow-x: auto;
        padding-bottom: 16px;
    }

    /* Forzar el grid horizontal */
    .kardex-grid {
        display: flex;
        flex-wrap: nowrap;
        gap: 20px;
        min-width: max-content; 
    }

    /* Columna de 6 del mismo tamaño */
    .semestre-col {
        width: 220px; /* Ancho fijo para garantizar consistencia visual */
        flex-shrink: 0;
    }

    .semestre-badge {
        background-color: #C59A65; /* Dorado/Café */
        color: #ffffff;
        font-weight: 800;
        font-size: 1.1rem;
        text-align: center;
        padding: 6px 16px;
        border-radius: 50rem;
        margin-bottom: 20px;
        box-shadow: 0 4px 8px rgba(197, 154, 101, 0.25);
    }

    .materia-card {
        background-color: #D4E4FC; /* Azul muy claro */
        border-left: 5px solid #0d6efd; /* Borde principal */
        border-radius: 12px;
        padding: 16px;
        margin-bottom: 16px;
        height: 120px; /* Altura consistente */
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        box-shadow: 0 .125rem .25rem rgba(0,0,0,.075);
        transition: transform .2s, box-shadow .2s;
    }

    .materia-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 .5rem 1rem rgba(0,0,0,.15);
    }

    .materia-title {
        color: #212529;
        font-size: 0.75rem;
        font-weight: 800;
        text-transform: uppercase;
        margin: 0;
        display: -webkit-box;
        -webkit-line-clamp: 3;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }

    .materia-grade {
        font-size: 1.75rem;
        font-weight: 800;
        color: #0d6efd;
        margin: 0;
        line-height: 1;
    }

    .materia-empty {
        color: #8c98a4;
        font-size: 1.75rem;
        font-weight: 800;
        margin: 0;
        line-height: 1;
    }
</style>

<div class="container-fluid alumno-panel">
    <div class="row min-vh-100">

        <!-- BARRA LATERAL -->
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
                <a href="{{ route('logout') }}" class="alumno-nav alumno-logout">
                    <i class="bi bi-box-arrow-right"></i> Cerrar sesión
                </a>
            </nav>
        </aside>

        <!-- CONTENIDO PRINCIPAL -->
        <main class="col-12 col-md-9 col-lg-10 p-4 p-lg-5">

            <!-- Encabezado de la vista -->
            <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
                <div>
                    <h2 class="fw-bold mb-1 text-dark">Mi Historial Académico (Kárdex)</h2>
                    <p class="alumno-muted mb-0">
                        <i class="bi bi-person-badge me-1"></i>
                        Alumno: <strong>{{ $alumno->nombres }} {{ $alumno->apellido_paterno }}</strong>
                    </p>
                </div>
                <a href="{{ route('alumno.materias') }}" class="btn btn-secondary fw-bold px-4 shadow-sm">
                    <i class="bi bi-arrow-left me-1"></i> Volver a Materias
                </a>
            </div>

            <!-- Contenedor del Kárdex (Retícula Reticular) -->
            <div class="kardex-wrapper">
                <div class="kardex-container">
                    <div class="kardex-grid">
                        
                        <!-- Ciclo para las 6 columnas (Semestres) -->
                        @for ($semestre = 1; $semestre <= 6; $semestre++)
                            <div class="semestre-col">
                                
                                <!-- Encabezado del semestre -->
                                <div class="semestre-badge">
                                    0{{ $semestre }}
                                </div>

                                <!-- Validación de materias -->
                                @if(isset($materias[$semestre]) && count($materias[$semestre]) > 0)
                                    @foreach($materias[$semestre] as $materia)
                                        
                                        <!-- Tarjeta de Materia -->
                                        <div class="materia-card">
                                            <h6 class="materia-title" title="{{ $materia->nombre }}">
                                                {{ $materia->nombre }}
                                            </h6>
                                            
                                            <div>
                                                @if(isset($calificaciones[$materia->id_materia]))
                                                    <p class="materia-grade">{{ $calificaciones[$materia->id_materia] }}</p>
                                                @else
                                                    <!-- Sin calificación registrada -->
                                                    <p class="materia-empty">-</p>
                                                @endif
                                            </div>
                                        </div>

                                    @endforeach
                                @else
                                    <!-- Columna vacía de relleno si no hay materias -->
                                    <div class="d-flex align-items-center justify-content-center h-100" style="min-height: 120px;">
                                        <span class="text-muted opacity-50 small fw-bold">Sin asignaturas</span>
                                    </div>
                                @endif
                                
                            </div>
                        @endfor
                        
                    </div>
                </div>
            </div>

        </main>
    </div>
</div>

@endsection
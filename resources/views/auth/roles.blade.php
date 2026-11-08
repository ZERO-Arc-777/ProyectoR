@extends('layouts.app')

@section('titulo', 'Selección de Rol')

@section('contenido')
<div class="container d-flex flex-column justify-content-center align-items-center" style="min-height: 80vh;">
    
    <div class="mb-4 text-center">
        <img src="{{ asset('img/logo-prepa5.png') }}" alt="Logo Prepa 5" width="120" class="mb-3">
        <h3 class="text-white fw-bold">SISTEMA DE GESTIÓN ADMINISTRATIVA ESCOLAR</h3>
        <h5 class="text-light opacity-75">Escuela Preparatoria No.5 del Estado</h5>
    </div>

    <div class="row w-100 justify-content-center border border-secondary rounded-4 bg-dark bg-opacity-50 p-5 shadow-lg">
        
        <div class="col-md-3 text-center mx-2 p-4 bg-white rounded-3 shadow-sm border position-relative" style="transition: transform 0.2s; cursor: pointer;" onmouseover="this.style.transform='scale(1.05)'" onmouseout="this.style.transform='scale(1)'">
            <h5 class="text-secondary fw-bold">SGAE</h5>
            <h1 class="display-1">👨‍🎓</h1>
            <hr>
            <h6 class="fw-bold text-dark">ESTUDIANTE</h6>
            <p class="small text-muted">Módulo de servicios y consulta</p>
            <!-- Enlace invisible que cubre toda la tarjeta -->
            <a href="{{ route('login') }}" class="stretched-link"></a>
        </div>

        <div class="col-md-3 text-center mx-2 p-4 bg-white rounded-3 shadow-sm border position-relative" style="transition: transform 0.2s; cursor: pointer;" onmouseover="this.style.transform='scale(1.05)'" onmouseout="this.style.transform='scale(1)'">
            <h5 class="text-secondary fw-bold">SGAE</h5>
            <h1 class="display-1">👨‍🏫</h1>
            <hr>
            <h6 class="fw-bold text-dark">DOCENTES</h6>
            <p class="small text-muted">Módulo de Actividades</p>
            <a href="{{ route('login') }}" class="stretched-link"></a>
        </div>

        <div class="col-md-3 text-center mx-2 p-4 bg-white rounded-3 shadow-sm border position-relative" style="transition: transform 0.2s; cursor: pointer;" onmouseover="this.style.transform='scale(1.05)'" onmouseout="this.style.transform='scale(1)'">
            <h5 class="text-secondary fw-bold">SGAE</h5>
            <h1 class="display-1">👨‍💻</h1>
            <hr>
            <h6 class="fw-bold text-dark">ADMINISTRATIVO</h6>
            <p class="small text-muted">Módulo de gestión y control</p>
            <a href="{{ route('login') }}" class="stretched-link"></a>
        </div>

    </div>
</div>
@endsection
@extends('layouts.app')

@section('titulo', 'Selección de Rol')

@section('contenido')

<style>
    .roles-wrapper {
        min-height: 82vh;
        padding: 2.5rem 1rem;
    }

    .roles-logo {
        width: 112px;
        height: auto;
        filter: drop-shadow(0 6px 14px rgba(0, 0, 0, 0.35));
    }

    .roles-title {
        letter-spacing: 0.8px;
    }

    .roles-subtitle {
        letter-spacing: 0.4px;
    }

    .roles-container {
        max-width: 980px;
        backdrop-filter: blur(6px);
    }

    .role-card {
        background-color: #ffffff;
        border: 1px solid #dee2e6;
        border-radius: 1rem;
        padding: 2rem 1.5rem;
        cursor: pointer;
        transition: transform 0.22s ease, box-shadow 0.22s ease, border-color 0.22s ease;
    }

    .role-card:hover,
    .role-card:focus-within {
        transform: translateY(-6px);
        box-shadow: 0 1rem 2rem rgba(0, 0, 0, 0.28) !important;
        border-color: #212529;
    }

    .role-kicker {
        font-size: 0.72rem;
        font-weight: 700;
        letter-spacing: 1.6px;
        text-transform: uppercase;
        color: #6c757d;
        background-color: #f8f9fa;
        border: 1px solid #e9ecef;
        border-radius: 999px;
        padding: 0.25rem 0.75rem;
    }

    .role-icon-box {
        width: 76px;
        height: 76px;
        border-radius: 18px;
        background-color: #f1f3f5;
        color: #212529;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 2.15rem;
        margin: 1.25rem auto 1.15rem;
        transition: background-color 0.22s ease, color 0.22s ease, transform 0.22s ease;
    }

    .role-card:hover .role-icon-box,
    .role-card:focus-within .role-icon-box {
        background-color: #212529;
        color: #ffffff;
        transform: scale(1.04);
    }

    .role-divider {
        width: 44px;
        height: 2px;
        background-color: #dee2e6;
        margin: 0 auto 1rem;
        border-radius: 2px;
        transition: width 0.22s ease, background-color 0.22s ease;
    }

    .role-card:hover .role-divider,
    .role-card:focus-within .role-divider {
        width: 68px;
        background-color: #212529;
    }

    .role-name {
        letter-spacing: 0.6px;
        font-size: 1rem;
    }

    .role-action {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        font-size: 0.85rem;
        font-weight: 600;
        color: #212529;
        margin-top: 1rem;
        padding-top: 0.85rem;
        border-top: 1px solid #f1f3f5;
        width: 100%;
        justify-content: center;
    }

    .role-action i {
        transition: transform 0.2s ease;
    }

    .role-card:hover .role-action i,
    .role-card:focus-within .role-action i {
        transform: translateX(4px);
    }
</style>

<div class="container roles-wrapper d-flex flex-column justify-content-center align-items-center">

    {{-- ENCABEZADO INSTITUCIONAL --}}
    <div class="mb-4 text-center">
        <img src="{{ asset('img/logo-prepa5.png') }}" alt="Logo Prepa 5" class="roles-logo mb-3">
        <h3 class="text-white fw-bold roles-title mb-1">
            SISTEMA DE GESTIÓN ADMINISTRATIVA ESCOLAR
        </h3>
        <h5 class="text-light opacity-75 fw-normal roles-subtitle mb-0">
            Escuela Preparatoria No. 5 del Estado
        </h5>
    </div>

    {{-- CONTENEDOR DE SELECCIÓN DE ROL --}}
    <div class="w-100 roles-container border border-secondary rounded-4 bg-dark bg-opacity-50 p-4 p-md-5 shadow-lg">

        <div class="text-center mb-4">
            <p class="text-light opacity-75 small text-uppercase fw-semibold mb-0" style="letter-spacing: 1px;">
                Selecciona tu perfil para ingresar
            </p>
        </div>

        <div class="row g-4 justify-content-center">

            {{-- ESTUDIANTE --}}
            <div class="col-12 col-md-4">
                <div class="role-card h-100 d-flex flex-column align-items-center text-center shadow-sm position-relative">
                    <span class="role-kicker">SGAE</span>

                    <div class="role-icon-box" aria-hidden="true">
                        <i class="bi bi-mortarboard-fill"></i>
                    </div>

                    <div class="role-divider"></div>

                    <h6 class="fw-bold text-dark role-name mb-2">ESTUDIANTE</h6>

                    <div class="role-action">
                        <span>Ingresar</span>
                        <i class="bi bi-arrow-right"></i>
                    </div>

                    <a href="{{ route('login') }}"
                       class="stretched-link"
                       aria-label="Ingresar como Estudiante"></a>
                </div>
            </div>

            {{-- DOCENTES --}}
            <div class="col-12 col-md-4">
                <div class="role-card h-100 d-flex flex-column align-items-center text-center shadow-sm position-relative">
                    <span class="role-kicker">SGAE</span>

                    <div class="role-icon-box" aria-hidden="true">
                        <i class="bi bi-person-workspace"></i>
                    </div>

                    <div class="role-divider"></div>

                    <h6 class="fw-bold text-dark role-name mb-2">DOCENTES</h6>

                    <div class="role-action">
                        <span>Ingresar</span>
                        <i class="bi bi-arrow-right"></i>
                    </div>

                    <a href="{{ route('login') }}"
                       class="stretched-link"
                       aria-label="Ingresar como Docente"></a>
                </div>
            </div>

            {{-- ADMINISTRATIVO --}}
            <div class="col-12 col-md-4">
                <div class="role-card h-100 d-flex flex-column align-items-center text-center shadow-sm position-relative">
                    <span class="role-kicker">SGAE</span>

                    <div class="role-icon-box" aria-hidden="true">
                        <i class="bi bi-shield-lock-fill"></i>
                    </div>

                    <div class="role-divider"></div>

                    <h6 class="fw-bold text-dark role-name mb-2">ADMINISTRATIVO</h6>

                    <div class="role-action">
                        <span>Ingresar</span>
                        <i class="bi bi-arrow-right"></i>
                    </div>

                    <a href="{{ route('login') }}"
                       class="stretched-link"
                       aria-label="Ingresar como Administrativo"></a>
                </div>
            </div>

        </div>
    </div>

</div>
@endsection
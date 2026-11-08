@extends('layouts.app')

@section('titulo', 'Iniciar Sesión')

@section('contenido')
<div class="container d-flex justify-content-center align-items-center" style="min-height: 85vh;">
    <div class="row w-100 align-items-center" style="max-width: 1000px;">
        
        <div class="col-md-6 mb-4 mb-md-0">
            <div class="card border-0 shadow-lg" style="background-color: #a3a7a6; border-radius: 25px;">
                <div class="card-body p-4 p-md-5 text-center">
                    
                    <div class="bg-dark text-white py-2 mb-4" style="border-radius: 15px;">
                        <h5 class="mb-0 fw-bold">INICIO DE SESIÓN</h5>
                    </div>

                    @if(session('error'))
                        <div class="alert alert-danger py-2 rounded-3 small fw-bold">
                            {{ session('error') }}
                        </div>
                    @endif

                    <form action="{{ route('login.post') }}" method="POST">
                        @csrf
                        
                        <div class="mb-4 text-start">
                            <label class="fw-bold text-dark mb-1 ms-2" style="font-size: 0.9rem;">CORREO ELECTRONICO</label>
                            <input type="text" name="username" class="form-control form-control-lg border-0 shadow-sm" style="background-color: #d1d5d6; border-radius: 12px;" required autofocus>
                        </div>

                        <div class="mb-4 text-start">
                            <label class="fw-bold text-dark mb-1 ms-2" style="font-size: 0.9rem;">CONTRASEÑA</label>
                            <input type="password" name="password" class="form-control form-control-lg border-0 shadow-sm" style="background-color: #d1d5d6; border-radius: 12px;" required>
                        </div>

                        <button type="submit" class="btn btn-dark btn-lg px-5 fw-bold mb-3 shadow" style="border-radius: 12px; background: linear-gradient(180deg, #4a4a5a, #1a1a1a);">
                            ACEPTAR
                        </button>
                    </form>

                    <a href="#" class="d-block text-dark fw-bold text-decoration-none mb-4" style="font-size: 0.85rem;">
                        RECUPERACION DE CONTRASEÑA
                    </a>

                </div>
            </div>
        </div>

        <div class="col-md-6 text-center text-white ps-md-5">
            <img src="{{ asset('img/logo-prepa5.png') }}" alt="Logo Prepa 5" class="img-fluid mb-4 shadow-lg rounded-circle" style="max-width: 320px; border: 5px solid #28a745;">
            
            <!-- Textos institucionales -->
            <h6 class="fw-bold mb-1" style="letter-spacing: 0.5px;">EDUCAR CON CONCIENCIA, DEMOCRACIA Y LIBERTAD</h6>
            <h6 class="fw-bold">CLAVE: 07EBH0019D</h6>
        </div>

    </div>
</div>
@endsection
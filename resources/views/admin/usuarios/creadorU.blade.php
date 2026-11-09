@extends('layouts.app')

@section('titulo', 'Crear Nuevo Usuario')
@section('usuario_header', 'ADMINISTRATIVO')

@section('contenido')
<div class="container mt-4 d-flex justify-content-center">
    <div class="card shadow-lg border-0 rounded-4 w-100" style="max-width: 600px; background-color: #d1d5d6;">
        
        <div class="card-header bg-dark text-white text-center py-3" style="border-top-left-radius: 15px; border-top-right-radius: 15px;">
            <h5 class="mb-0 fw-bold">CREAR NUEVO USUARIO</h5>
        </div>

        <div class="card-body p-4">
            <a href="{{ route('admin.usuarios') }}" class="text-decoration-none text-primary fw-bold mb-3 d-block">Volver a la lista de Usuarios</a>
            
            <!-- Mostrar errores de validación con estilo Bootstrap -->
            @if ($errors->any())
                <div class="alert alert-danger fw-bold rounded-3">
                    <ul class="mb-0 ps-3">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('admin.usuarios.store') }}" method="POST">
                @csrf

                <div class="mb-3 text-start">
                    <label class="fw-bold text-dark mb-1 ms-1">Nombre Completo:</label>
                    <input type="text" name="nombre" class="form-control" value="{{ old('nombre') }}" required>
                </div>

                <div class="mb-3 text-start">
                    <label class="fw-bold text-dark mb-1 ms-1">Correo Electrónico:</label>
                    <input type="email" name="correo" class="form-control" value="{{ old('correo') }}" required>
                </div>

                <div class="mb-3 text-start">
                    <label class="fw-bold text-dark mb-1 ms-1">Contraseña:</label>
                    <!-- Las contraseñas nunca deben recuperar su valor con old() por seguridad -->
                    <input type="password" name="contrasena" class="form-control" required minlength="6">
                </div>

                <div class="mb-4 text-start">
                    <label class="fw-bold text-dark mb-1 ms-1">Asignar Rol:</label>
                    <select name="id_rol" class="form-select" required>
                        <option value="">-- Selecciona un Rol --</option>
                        @foreach ($roles as $rol)
                            <option value="{{ $rol->id_rol }}" {{ old('id_rol') == $rol->id_rol ? 'selected' : '' }}>
                                {{ $rol->nombre }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="d-flex justify-content-between mt-4">
                    <a href="{{ route('admin.usuarios') }}" class="btn btn-secondary fw-bold px-4">Cancelar</a>
                    <button type="submit" class="btn btn-dark fw-bold px-4">Guardar Usuario</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
@extends('layouts.app')

@section('titulo', 'Editar Usuario')
@section('usuario_header', 'ADMINISTRATIVO')

@section('contenido')
<div class="container mt-4 d-flex justify-content-center">
    <div class="card shadow-lg border-0 rounded-4 w-100" style="max-width: 600px; background-color: #d1d5d6;">
        
        <div class="card-header bg-dark text-white text-center py-3" style="border-top-left-radius: 15px; border-top-right-radius: 15px;">
            <h5 class="mb-0 fw-bold">EDITAR USUARIO</h5>
        </div>

        <div class="card-body p-4">
            <form action="{{ route('admin.usuarios.update', $usuario->id_usuario) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="mb-3 text-start">
                    <label class="fw-bold text-dark mb-1 ms-1">Nombre Completo</label>
                    <input type="text" name="nombre" class="form-control" value="{{ $usuario->nombre }}" required>
                </div>

                <div class="mb-3 text-start">
                    <label class="fw-bold text-dark mb-1 ms-1">Correo Electrónico</label>
                    <input type="email" name="correo" class="form-control" value="{{ $usuario->correo }}" required>
                </div>

                <div class="mb-3 text-start">
                    <label class="fw-bold text-dark mb-1 ms-1">Rol en el Sistema</label>
                    <select name="id_rol" class="form-select" required>
                        @foreach($roles as $rol)
                            <option value="{{ $rol->id_rol }}" {{ $usuario->id_rol == $rol->id ? 'selected' : '' }}>
                                {{ $rol->nombre }} 
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="mb-3 text-start">
                    <label class="fw-bold text-dark mb-1 ms-1">Estado (Baja lógica)</label>
                    <select name="estado" class="form-select" required>
                        <option value="ACTIVO" {{ $usuario->estado == 'ACTIVO' ? 'selected' : '' }}>Activo</option>
                        <option value="INACTIVO" {{ $usuario->estado == 'INACTIVO' ? 'selected' : '' }}>Inactivo (Dado de baja)</option>
                    </select>
                </div>

                <div class="mb-4 text-start">
                    <label class="fw-bold text-dark mb-1 ms-1">Nueva Contraseña <small class="text-danger">(Opcional)</small></label>
                    <input type="password" name="password" class="form-control" placeholder="Dejar en blanco para mantener la actual">
                </div>

                <div class="d-flex justify-content-between mt-4">
                    <a href="{{ route('admin.usuarios') }}" class="btn btn-secondary fw-bold px-4">Cancelar</a>
                    <button type="submit" class="btn btn-dark fw-bold px-4">Guardar Cambios</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
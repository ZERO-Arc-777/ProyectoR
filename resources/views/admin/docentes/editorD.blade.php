@extends('layouts.app')

@section('titulo', 'Editar Expediente de Docente')
@section('usuario_header', 'ADMINISTRATIVO')

@section('contenido')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

<div class="container mt-4 d-flex justify-content-center">
    <div class="card shadow-lg border-0 rounded-4 w-100" style="max-width: 700px; background-color: #d1d5d6;">
        
        <div class="card-header bg-dark text-white text-center py-3" style="border-top-left-radius: 15px; border-top-right-radius: 15px;">
            <h5 class="mb-0 fw-bold">EDITAR EXPEDIENTE DE DOCENTE</h5>
        </div>

        <div class="card-body p-4">
            <a href="{{ route('admin.docentes') }}" class="text-decoration-none text-primary fw-bold mb-3 d-block">Volver a la lista</a>
            
            <form action="{{ route('admin.docentes.update', $docente->id_docente) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="mb-3 text-start">
                    <label class="fw-bold text-dark mb-1 ms-1">Vincular a Cuenta de Usuario:</label>
                    <select name="id_usuario" id="buscador-usuarios" class="form-select">
                        <option value="">-- Sin cuenta vinculada --</option>
                        @foreach($usuarios as $user)
                            <option value="{{ $user->id_usuario }}" {{ $docente->id_usuario == $user->id_usuario ? 'selected' : '' }}>
                                {{ $user->id_usuario }} - {{ $user->nombre }} ({{ $user->correo }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3 text-start">
                        <label class="fw-bold text-dark mb-1 ms-1">Número de Empleado:</label>
                        <input type="text" name="numero_empleado" class="form-control" value="{{ $docente->numero_empleado }}" required>
                    </div>
                    <div class="col-md-6 mb-3 text-start">
                        <label class="fw-bold text-dark mb-1 ms-1">CURP:</label>
                        <input type="text" name="curp" class="form-control text-uppercase" maxlength="18" value="{{ $docente->curp }}" required>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-12 mb-3 text-start">
                        <label class="fw-bold text-dark mb-1 ms-1">Nombre(s):</label>
                        <input type="text" name="nombres" class="form-control" value="{{ $docente->nombres }}" required>
                    </div>
                    <div class="col-md-6 mb-3 text-start">
                        <label class="fw-bold text-dark mb-1 ms-1">Apellido Paterno:</label>
                        <input type="text" name="apellido_paterno" class="form-control" value="{{ $docente->apellido_paterno }}" required>
                    </div>
                    <div class="col-md-6 mb-3 text-start">
                        <label class="fw-bold text-dark mb-1 ms-1">Apellido Materno:</label>
                        <input type="text" name="apellido_materno" class="form-control" value="{{ $docente->apellido_materno }}">
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3 text-start">
                        <label class="fw-bold text-dark mb-1 ms-1">Cédula Profesional:</label>
                        <input type="text" name="cedula_profesional" class="form-control" value="{{ $docente->cedula_profesional }}">
                    </div>
                    <div class="col-md-6 mb-4 text-start">
                        <label class="fw-bold text-dark mb-1 ms-1">Estado Administrativo:</label>
                        <select name="estado" class="form-select" required>
                            <option value="ACTIVO" {{ $docente->estado == 'ACTIVO' ? 'selected' : '' }}>Activo</option>
                            <option value="INACTIVO" {{ $docente->estado == 'INACTIVO' ? 'selected' : '' }}>Inactivo</option>
                            <option value="BAJA" {{ $docente->estado == 'BAJA' ? 'selected' : '' }}>Baja Definitiva</option>
                        </select>
                    </div>
                </div>

                <div class="d-flex justify-content-between mt-4">
                    <a href="{{ route('admin.docentes') }}" class="btn btn-secondary fw-bold px-4">Cancelar</a>
                    <button type="submit" class="btn btn-dark fw-bold px-4">Guardar Cambios</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
    $(document).ready(function() {
        $('#buscador-usuarios').select2({
            placeholder: "-- Escribe el nombre o correo del docente --",
            allowClear: true,
            width: '100%'
        });
    });
</script>
@endsection
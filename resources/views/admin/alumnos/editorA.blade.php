@extends('layouts.app')

@section('titulo', 'Editar Expediente de Alumno')
@section('usuario_header', 'ADMINISTRATIVO')

@section('contenido')
<!-- CSS de Select2 -->
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

<div class="container mt-4 d-flex justify-content-center">
    <div class="card shadow-lg border-0 rounded-4 w-100" style="max-width: 700px; background-color: #d1d5d6;">
        
        <div class="card-header bg-dark text-white text-center py-3" style="border-top-left-radius: 15px; border-top-right-radius: 15px;">
            <h5 class="mb-0 fw-bold">EDITAR EXPEDIENTE DE ALUMNO</h5>
        </div>

        <div class="card-body p-4">
            <a href="{{ route('admin.alumnos') }}" class="text-decoration-none text-primary fw-bold mb-3 d-block">Volver a la lista</a>
            
            <form action="{{ route('admin.alumnos.update', $alumno->matricula) }}" method="POST">
                @csrf
                @method('PUT') <!-- Obligatorio para actualizar -->

                <div class="mb-3 text-start">
                    <label class="fw-bold text-dark mb-1 ms-1">Vincular a Cuenta de Usuario:</label>
                    <select name="id_usuario" id="buscador-usuarios" class="form-select" required>
                        <option value="">-- Selecciona una cuenta --</option>
                        @foreach($usuarios as $user)
                            <option value="{{ $user->id_usuario }}" {{ $alumno->id_usuario == $user->id_usuario ? 'selected' : '' }}>
                                {{ $user->id_usuario }} - {{ $user->nombre }} ({{ $user->correo }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="mb-3 text-start">
                    <label class="fw-bold text-dark mb-1 ms-1">CURP:</label>
                    <input type="text" name="curp" class="form-control text-uppercase" maxlength="18" value="{{ $alumno->curp }}" required>
                </div>

                <div class="row">
                    <div class="col-md-12 mb-3 text-start">
                        <label class="fw-bold text-dark mb-1 ms-1">Nombre(s):</label>
                        <input type="text" name="nombres" class="form-control" value="{{ $alumno->nombres }}" required>
                    </div>
                    <div class="col-md-6 mb-3 text-start">
                        <label class="fw-bold text-dark mb-1 ms-1">Apellido Paterno:</label>
                        <input type="text" name="apellido_paterno" class="form-control" value="{{ $alumno->apellido_paterno }}" required>
                    </div>
                    <div class="col-md-6 mb-3 text-start">
                        <label class="fw-bold text-dark mb-1 ms-1">Apellido Materno:</label>
                        <input type="text" name="apellido_materno" class="form-control" value="{{ $alumno->apellido_materno }}">
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3 text-start">
                        <label class="fw-bold text-dark mb-1 ms-1">Semestre:</label>
                        <select name="semestre" class="form-select" required>
                            <option value="1" {{ $alumno->semestre == 1 ? 'selected' : '' }}>1° Semestre</option>
                            <option value="2" {{ $alumno->semestre == 2 ? 'selected' : '' }}>2° Semestre</option>
                            <option value="3" {{ $alumno->semestre == 3 ? 'selected' : '' }}>3° Semestre</option>
                            <option value="4" {{ $alumno->semestre == 4 ? 'selected' : '' }}>4° Semestre</option>
                            <option value="5" {{ $alumno->semestre == 5 ? 'selected' : '' }}>5° Semestre</option>
                            <option value="6" {{ $alumno->semestre == 6 ? 'selected' : '' }}>6° Semestre</option>
                        </select>
                    </div>
                    <div class="col-md-6 mb-4 text-start">
                        <label class="fw-bold text-dark mb-1 ms-1">Grupo:</label>
                        <select name="grupo" class="form-select" required>
                            <option value="A" {{ $alumno->grupo == 'A' ? 'selected' : '' }}>Grupo A</option>
                            <option value="B" {{ $alumno->grupo == 'B' ? 'selected' : '' }}>Grupo B</option>
                            <option value="C" {{ $alumno->grupo == 'C' ? 'selected' : '' }}>Grupo C</option>
                        </select>
                    </div>
                </div>

                <div class="d-flex justify-content-between mt-4">
                    <a href="{{ route('admin.alumnos') }}" class="btn btn-secondary fw-bold px-4">Cancelar</a>
                    <button type="submit" class="btn btn-dark fw-bold px-4">Guardar Cambios</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- jQuery y Select2 -->
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
    $(document).ready(function() {
        $('#buscador-usuarios').select2({
            placeholder: "-- Escribe el nombre o correo del usuario --",
            allowClear: true,
            width: '100%'
        });
    });
</script>
@endsection
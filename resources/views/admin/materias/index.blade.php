@extends('layouts.app')

@section('titulo', 'Asignación Académica')
@section('usuario_header', 'ADMINISTRATIVO')

@section('contenido')
<div class="container mt-4 d-flex justify-content-center">
    <div class="card shadow-lg border-0 rounded-4 w-100" style="max-width: 700px; background-color: #d1d5d6;">
        
        <div class="card-header bg-dark text-white text-center py-3" style="border-top-left-radius: 15px; border-top-right-radius: 15px;">
            <h5 class="mb-0 fw-bold"><i class="bi bi-person-video3 me-2"></i>ASIGNAR MATERIA A DOCENTE</h5>
        </div>

        <div class="card-body p-4">
            <a href="{{ route('admin.dashboard') }}" class="text-decoration-none text-primary fw-bold mb-4 d-inline-block">
                <i class="bi bi-arrow-left me-1"></i> Volver al Dashboard
            </a>
            
            @if (request()->has('success'))
                <div class="alert alert-success fw-bold rounded-3 shadow-sm border-0 mb-4">
                    <i class="bi bi-check-circle-fill me-2"></i> Asignación registrada correctamente en el ciclo escolar actual.
                </div>
            @endif

            <form action="{{ url()->current() }}" method="GET">
                <input type="hidden" name="success" value="1">

                <div class="mb-3 text-start">
                    <label class="fw-bold text-dark mb-1 ms-1">Seleccionar Docente:</label>
                    <select name="docente" class="form-select border-0 shadow-sm" required>
                        <option value="">-- Elige el profesor --</option>
                        <option value="1">Maria Sanchez Sanchez</option>
                    </select>
                </div>

                <div class="mb-3 text-start">
                    <label class="fw-bold text-dark mb-1 ms-1">Materia a Impartir:</label>
                    <select name="materia" class="form-select border-0 shadow-sm" required>
                        <option value="">-- Elige la materia --</option>
                        <option value="cultura">Cultura Digital I. Ciudadanía digital</option>
                        <option value="ciencias">Ciencias Naturales Experimentales y Tecnología I</option>
                        <option value="submodulo">Submódulo VI (Desarrollo Comunitario)</option>
                    </select>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3 text-start">
                        <label class="fw-bold text-dark mb-1 ms-1">Semestre:</label>
                        <select name="semestre" class="form-select border-0 shadow-sm" required>
                            <option value="">-- Selecciona --</option>
                            <option value="1">1° Semestre</option>
                            <option value="3">3° Semestre</option>
                            <option value="5">5° Semestre</option>
                        </select>
                    </div>

                    <div class="col-md-6 mb-4 text-start">
                        <label class="fw-bold text-dark mb-1 ms-1">Grupo:</label>
                        <select name="grupo" class="form-select border-0 shadow-sm" required>
                            <option value="">-- Selecciona --</option>
                            <option value="A">Grupo A</option>
                            <option value="B">Grupo B</option>
                        </select>
                    </div>
                </div>

                <div class="d-flex justify-content-end mt-2">
                    <button type="submit" class="btn btn-dark fw-bold px-4 shadow-sm">
                        <i class="bi bi-save me-2"></i> Guardar Asignación
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
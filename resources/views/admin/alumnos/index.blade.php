@extends('layouts.app')

@section('titulo', 'Gestión de Alumnos')
@section('usuario_header', 'ADMINISTRATIVO')

@section('contenido')
<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="text-white fw-bold">Gestión de Alumnos</h3>
        <a href="{{ route('admin.dashboard') }}" class="btn btn-secondary">Volver al Dashboard</a>
    </div>

    @if(session('success'))
        <div class="alert alert-success fw-bold">{{ session('success') }}</div>
    @endif

    <div class="card shadow-sm border-0 rounded-4" style="background-color: #d1d5d6;">
        <div class="card-body p-4">
            
            <div class="d-flex justify-content-between align-items-center mb-3">
                <a href="{{ route('admin.alumnos.create') }}" class="btn btn-dark fw-bold">+ Inscribir Nuevo Alumno</a>
                
                <form action="{{ route('admin.alumnos') }}" method="GET" class="d-flex" style="width: 375px;">
                    <input type="text" name="buscar" class="form-control me-2" placeholder="Buscar por matrícula, nombre o CURP." value="{{ $buscar ?? '' }}">
                    <button type="submit" class="btn btn-primary fw-bold">Buscar</button>
                    @if(request('buscar'))
                        <a href="{{ route('admin.alumnos') }}" class="btn btn-secondary ms-1" title="Limpiar">✖</a>
                    @endif
                </form>
            </div>
            
            <table class="table table-hover table-light border-secondary">
                <thead class="table-dark">
                    <tr>
                        <th>Matrícula</th>
                        <th>Nombre Completo</th>
                        <th>CURP</th>
                        <th>Semestre y Grupo</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($alumnos as $alumno)
                    <tr>
                        <td>{{ $alumno->matricula }}</td>
                        <td>{{ $alumno->apellido_paterno }} {{ $alumno->apellido_materno }} {{ $alumno->nombres }}</td>
                        <td>{{ $alumno->curp }}</td>
                        <td>{{ $alumno->semestre }}° "{{ $alumno->grupo }}"</td>
                        <td>
                            <a href="{{ route('admin.alumnos.edit', $alumno->matricula) }}" class="btn btn-sm btn-primary fw-bold">
                                Editar
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="text-center py-3 fw-bold text-muted">No se encontraron alumnos.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>

            <div class="d-flex justify-content-end mt-3">
                {{ $alumnos->appends(request()->query())->links() }}
            </div>

        </div>
    </div>
</div>
@endsection
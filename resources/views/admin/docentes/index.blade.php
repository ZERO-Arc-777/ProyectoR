@extends('layouts.app')

@section('titulo', 'Gestión de Docentes')
@section('usuario_header', 'ADMINISTRATIVO')

@section('contenido')
<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="text-white fw-bold">Gestión de Docentes</h3>
        <a href="{{ route('admin.dashboard') }}" class="btn btn-secondary">Volver al Dashboard</a>
    </div>

    @if(session('success'))
        <div class="alert alert-success fw-bold">{{ session('success') }}</div>
    @endif

    <div class="card shadow-sm border-0 rounded-4" style="background-color: #d1d5d6;">
        <div class="card-body p-4">
            
            <div class="d-flex justify-content-between align-items-center mb-3">
                <a href="{{ route('admin.docentes.create') }}" class="btn btn-dark fw-bold">+ Registrar Nuevo Docente</a>
                
                <form action="{{ route('admin.docentes') }}" method="GET" class="d-flex" style="width: 350px;">
                    <input type="text" name="buscar" class="form-control me-2" placeholder="Buscar por número o nombre..." value="{{ $buscar ?? '' }}">
                    <button type="submit" class="btn btn-primary fw-bold">Buscar</button>
                    @if(request('buscar'))
                        <a href="{{ route('admin.docentes') }}" class="btn btn-secondary ms-1" title="Limpiar">✖</a>
                    @endif
                </form>
            </div>
            
            <table class="table table-hover table-light border-secondary">
                <thead class="table-dark">
                    <tr>
                        <th>Núm. Empleado</th>
                        <th>Nombre Completo</th>
                        <th>CURP</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($docentes as $docente)
                    <tr>
                        <td>{{ $docente->numero_empleado }}</td>
                        <td>{{ $docente->apellido_paterno }} {{ $docente->apellido_materno }} {{ $docente->nombres }}</td>
                        <td>{{ $docente->curp }}</td>
                        <td>
                            @if($docente->estado == 'ACTIVO')
                                <span class="badge bg-success">Activo</span>
                            @elseif($docente->estado == 'INACTIVO')
                                <span class="badge bg-warning text-dark">Inactivo</span>
                            @else
                                <span class="badge bg-danger">Baja</span>
                            @endif
                        </td>
                        <td>
                            <a href="{{ route('admin.docentes.edit', $docente->id_docente) }}" class="btn btn-sm btn-primary fw-bold">
                                Editar
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="text-center py-3 fw-bold text-muted">No se encontraron docentes.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>

            <div class="d-flex justify-content-end mt-3">
                {{ $docentes->appends(request()->query())->links() }}
            </div>

        </div>
    </div>
</div>
@endsection
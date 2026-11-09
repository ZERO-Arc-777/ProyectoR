@extends('layouts.app')

@section('titulo', $titulo)
@section('usuario_header', 'ADMINISTRATIVO')

@section('contenido')
<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="text-white fw-bold">{{ $titulo }}</h3>
        <a href="{{ route('admin.dashboard') }}" class="btn btn-secondary">Volver al Dashboard</a>
    </div>

    @if(session('success'))
        <div class="alert alert-success fw-bold">{{ session('success') }}</div>
    @endif

    <div class="card shadow-sm border-0 rounded-4" style="background-color: #d1d5d6;">
        <div class="card-body p-4">
            
            <div class="d-flex justify-content-end mb-3">
                <!-- url()->current() permite que el buscador funcione en cualquiera de tus 3 rutas -->
                <form action="{{ url()->current() }}" method="GET" class="d-flex" style="width: 350px;">
                    <input type="text" name="buscar" class="form-control me-2" placeholder="Buscar por matrícula o nombre..." value="{{ $buscar ?? '' }}">
                    <button type="submit" class="btn btn-primary fw-bold">Buscar</button>
                    @if(request('buscar'))
                        <a href="{{ url()->current() }}" class="btn btn-secondary ms-1" title="Limpiar">✖</a>
                    @endif
                </form>
            </div>
            
            <table class="table table-hover table-light border-secondary align-middle">
                <thead class="table-dark">
                    <tr>
                        <th>Matrícula</th>
                        <th>Nombre del Alumno</th>
                        <th>Documento Subido</th>
                        <th>Estado Actual</th>
                        <th>Acción (Validar)</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($documentos as $doc)
                    <tr>
                        <td class="fw-bold">{{ $doc->alumno->matricula }}</td>
                        <td>
                            {{ $doc->alumno->apellido_paterno }} {{ $doc->alumno->apellido_materno }} {{ $doc->alumno->nombres }}
                        </td>
                        <td>
                            <span class="d-block fw-bold mb-1">{{ $doc->tipo_documento }}</span>
                            <a href="{{ asset('storage/' . $doc->ruta_archivo) }}" target="_blank" class="btn btn-sm btn-outline-primary fw-bold">
                                📄 Ver PDF
                            </a>
                        </td>
                        <td>
                            @if($doc->estado_validacion == 'PENDIENTE')
                                <span class="badge bg-warning text-dark fs-6">PENDIENTE</span>
                            @elseif($doc->estado_validacion == 'APROBADO')
                                <span class="badge bg-success fs-6">APROBADO</span>
                            @else
                                <span class="badge bg-danger fs-6 mb-1">RECHAZADO</span>
                                <br><small class="text-muted fst-italic">{{ $doc->observaciones }}</small>
                            @endif
                        </td>
                        <td style="width: 250px;">
                            <form action="{{ route('admin.documentos.estado', $doc->id_documento) }}" method="POST" class="m-0">
                                @csrf
                                @method('PUT')
                                <select name="estado_validacion" class="form-select form-select-sm mb-2 fw-bold" required>
                                    <option value="PENDIENTE" {{ $doc->estado_validacion == 'PENDIENTE' ? 'selected' : '' }}>Pendiente</option>
                                    <option value="APROBADO" {{ $doc->estado_validacion == 'APROBADO' ? 'selected' : '' }}>Aprobar</option>
                                    <option value="RECHAZADO" {{ $doc->estado_validacion == 'RECHAZADO' ? 'selected' : '' }}>Rechazar</option>
                                </select>
                                <input type="text" name="observaciones" class="form-control form-control-sm mb-2" placeholder="Motivo de rechazo (opcional)" value="{{ $doc->observaciones }}">
                                <button type="submit" class="btn btn-sm btn-dark fw-bold w-100">Actualizar</button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="text-center py-4 fw-bold text-muted">No hay documentos pendientes de revisión en esta bandeja.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>

            <div class="d-flex justify-content-end mt-3">
                {{ $documentos->appends(request()->query())->links() }}
            </div>

        </div>
    </div>
</div>
@endsection
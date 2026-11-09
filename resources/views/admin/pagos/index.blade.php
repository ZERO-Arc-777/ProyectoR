@extends('layouts.app')

@section('titulo', 'Seguimiento de Pagos')
@section('usuario_header', 'ADMINISTRATIVO')

@section('contenido')
<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="text-white fw-bold">Seguimiento de Pagos</h3>
        <a href="{{ route('admin.dashboard') }}" class="btn btn-secondary">Volver al Dashboard</a>
    </div>

    @if(session('success'))
        <div class="alert alert-success fw-bold">{{ session('success') }}</div>
    @endif

    <ul class="nav nav-pills mb-3 bg-white p-2 rounded-3 shadow-sm w-auto d-inline-flex">
        <li class="nav-item">
            <a class="nav-link {{ $estadoFiltro == 'PENDIENTE' ? 'active bg-dark text-white' : 'text-dark' }} fw-bold" 
               href="{{ route('admin.pagos', ['estado' => 'PENDIENTE']) }}">⏱ Pendientes</a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ $estadoFiltro == 'PAGADA' ? 'active bg-success text-white' : 'text-dark' }} fw-bold" 
               href="{{ route('admin.pagos', ['estado' => 'PAGADA']) }}">✅ Pagadas</a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ $estadoFiltro == 'CANCELADA' ? 'active bg-danger text-white' : 'text-dark' }} fw-bold" 
               href="{{ route('admin.pagos', ['estado' => 'CANCELADA']) }}">✖ Canceladas</a>
        </li>
    </ul>

    <div class="card shadow-sm border-0 rounded-4" style="background-color: #d1d5d6;">
        <div class="card-body p-4">
            
            <div class="d-flex justify-content-end mb-3">
                <form action="{{ route('admin.pagos') }}" method="GET" class="d-flex" style="width: 350px;">
                    <input type="hidden" name="estado" value="{{ $estadoFiltro }}">
                    <input type="text" name="buscar" class="form-control me-2" placeholder="Buscar folio, matrícula o nombre..." value="{{ $buscar ?? '' }}">
                    <button type="submit" class="btn btn-primary fw-bold">Buscar</button>
                    @if(request('buscar'))
                        <a href="{{ route('admin.pagos', ['estado' => $estadoFiltro]) }}" class="btn btn-secondary ms-1" title="Limpiar">✖</a>
                    @endif
                </form>
            </div>
            
            <table class="table table-hover table-light border-secondary align-middle">
                <thead class="table-dark">
                    <tr>
                        <th>Folio</th>
                        <!-- Columnas separadas -->
                        <th>Matrícula</th>
                        <th>Nombre del Alumno</th>
                        <th>Conceptos Solicitados</th>
                        <th>Total</th>
                        <th>Cambiar Estado</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($solicitudes as $solicitud)
                    <tr>
                        <td class="fw-bold">{{ $solicitud->folio }}</td>
                        
                        <td class="fw-bold">{{ $solicitud->alumno->matricula }}</td>
                        
                        <td>{{ $solicitud->alumno->apellido_paterno }} {{ $solicitud->alumno->apellido_materno }} {{ $solicitud->alumno->nombres }}</td>
                        
                        <td>
                            <ul class="mb-0 ps-3">
                                @foreach($solicitud->detalles as $detalle)
                                    <li>{{ $detalle->servicio->nombre }} <small class="text-muted">(${{ number_format($detalle->precio_cobrado, 2) }})</small></li>
                                @endforeach
                            </ul>
                        </td>
                        <td class="fw-bold text-success">${{ number_format($solicitud->total_pagar, 2) }}</td>
                        <td style="width: 250px;">
                            <form action="{{ route('admin.pagos.updateEstado', $solicitud->id_solicitud) }}" method="POST" class="d-flex">
                                @csrf
                                @method('PUT')
                                <select name="estado" class="form-select form-select-sm me-2 fw-bold 
                                    {{ $solicitud->estado == 'PENDIENTE' ? 'text-warning' : ($solicitud->estado == 'PAGADA' ? 'text-success' : 'text-danger') }}">
                                    <option value="PENDIENTE" {{ $solicitud->estado == 'PENDIENTE' ? 'selected' : '' }}>Pendiente</option>
                                    <option value="PAGADA" {{ $solicitud->estado == 'PAGADA' ? 'selected' : '' }}>Pagada</option>
                                    <option value="CANCELADA" {{ $solicitud->estado == 'CANCELADA' ? 'selected' : '' }}>Cancelada</option>
                                </select>
                                <button type="submit" class="btn btn-sm btn-dark fw-bold">Guardar</button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr
                        <td colspan="6" class="text-center py-4 fw-bold text-muted">No hay solicitudes en estado {{ $estadoFiltro }}.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>

            <div class="d-flex justify-content-end mt-3">
                {{ $solicitudes->appends(request()->query())->links() }}
            </div>

        </div>
    </div>
</div>
@endsection
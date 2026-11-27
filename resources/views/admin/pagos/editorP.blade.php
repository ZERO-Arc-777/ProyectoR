@extends('layouts.app')

@section('titulo', 'Editor del Catálogo de Pagos')

@section('usuario_header')
    <span class="fs-6">
        <i class="bi bi-person-circle me-1"></i>
        {{ Auth::user()->nombre ?? 'Administrador' }}
    </span>
@endsection

@section('contenido')

<style>
    .admin-panel {
        min-height: 100vh;
        background-color: #f4f6f9;
    }

    .admin-sidebar {
        background: #212529;
        border-right: 1px solid #343a40;
    }

    .admin-sidebar-title {
        font-weight: 800;
        letter-spacing: 1.2px;
        color: #ffffff;
    }

    .admin-nav {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 13px 16px;
        border-radius: 12px;
        color: #adb5bd;
        text-decoration: none;
        transition: background .2s, color .2s;
    }

    .admin-nav:hover {
        background: rgba(255, 255, 255, .1);
        color: #ffffff;
    }

    .admin-nav.active {
        background: #198754;
        color: #ffffff;
        font-weight: 600;
    }

    .admin-nav-sub {
        font-size: 0.9rem;
        padding: 8px 16px 8px 40px;
        color: #8a939b;
        text-decoration: none;
        display: block;
        transition: color .2s;
    }

    .admin-nav-sub:hover {
        color: #ffffff;
    }

    .admin-nav-sub.active {
        color: #198754;
        font-weight: bold;
    }

    .admin-nav i { font-size: 1.15rem; }

    .admin-card-content {
        background: #d1d5d6;
        border: 1px solid #c0c4c5;
        border-radius: 18px;
    }

    .admin-muted { color: #6c757d; }

    .admin-logout { color: #ffb3b9; }
    .admin-logout:hover { color: #ffffff; background: rgba(220, 53, 69, .22); }
</style>

<div class="container-fluid admin-panel">
    <div class="row min-vh-100">

        <aside class="col-12 col-md-3 col-lg-2 admin-sidebar p-4">

            <div class="text-center mb-4">
                <div class="mb-2">
                    <i class="bi bi-shield-lock-fill fs-2 text-white"></i>
                </div>
                <h5 class="admin-sidebar-title mb-1">SGAE PREPA 5</h5>
                <small class="text-white-50">Panel Administrativo</small>
            </div>

            <hr class="border-secondary mb-4">

            <nav class="d-grid gap-2" aria-label="Menú del administrador">

                <a href="{{ route('admin.dashboard') }}" class="admin-nav">
                    <i class="bi bi-grid-1x2-fill"></i> Inicio
                </a>

                <a href="{{ route('admin.usuarios') }}" class="admin-nav">
                    <i class="bi bi-people-fill"></i> Usuarios
                </a>

                <a href="{{ route('admin.alumnos') }}" class="admin-nav">
                    <i class="bi bi-mortarboard-fill"></i> Alumnos
                </a>

                <a href="{{ route('admin.docentes') }}" class="admin-nav">
                    <i class="bi bi-person-workspace"></i> Docentes
                </a>

                <a href="{{ route('admin.materias') }}" class="admin-nav">
                    <i class="bi bi-journal-text"></i> Materias
                </a>

                <a href="{{ route('admin.periodo.index') }}" class="admin-nav">
                    <i class="bi bi-calendar-range-fill"></i> Periodo Escolar
                </a>

                <a href="{{ route('admin.pagos') }}" class="admin-nav active" aria-current="page">
                    <i class="bi bi-cash-coin"></i> Pagos
                </a>

                <div class="mt-2 mb-1 ps-3 text-white-50 small fw-bold" style="letter-spacing: 0.5px;">REVISIONES</div>

                <a href="{{ route('admin.revision.inscripciones') }}" class="admin-nav-sub">
                    <i class="bi bi-arrow-return-right me-1"></i> Inscripciones
                </a>
                <a href="{{ route('admin.revision.reinscripciones') }}" class="admin-nav-sub">
                    <i class="bi bi-arrow-return-right me-1"></i> Reinscripciones
                </a>
                <a href="{{ route('admin.revision.egresos') }}" class="admin-nav-sub">
                    <i class="bi bi-arrow-return-right me-1"></i> Egresos
                </a>

                <hr class="border-secondary my-3">

                <!-- CERRAR SESIÓN SEGURO -->
                <form action="{{ route('logout.seguro') }}" method="POST" class="m-0">
                    @csrf

                    <button type="submit"
                            class="admin-nav admin-logout"
                            style="background: none;
                                   border: none;
                                   cursor: pointer;
                                   width: 100%;
                                   text-align: left;
                                   font: inherit;
                                   display: flex;
                                   align-items: center;
                                   gap: 12px;
                                   white-space: nowrap;">
                        <i class="bi bi-power"></i>
                        <span>Cerrar sesión</span>
                    </button>
                </form>

            </nav>
        </aside>

        <main class="col-12 col-md-9 col-lg-10 p-4 p-lg-5">

            <!-- ENCABEZADO -->
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
                <div>
                    <h2 class="fw-bold mb-1 text-dark">Editor del Catálogo de Pagos</h2>
                    <p class="admin-muted mb-0">Agrega, modifica o elimina los conceptos y costos disponibles para los estudiantes.</p>
                </div>

                <a href="{{ route('admin.pagos') }}" class="btn btn-secondary fw-bold px-4 py-2 shadow-sm">
                    <i class="bi bi-arrow-left me-1"></i> Volver a Pagos
                </a>
            </div>

            @if(session('success'))
                <div class="alert alert-success fw-bold rounded-3 shadow-sm border-0 mb-4">
                    <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
                </div>
            @endif

            @if ($errors->any())
                <div class="alert alert-danger fw-bold rounded-3 shadow-sm border-0 mb-4">
                    <div class="d-flex align-items-center mb-2">
                        <i class="bi bi-exclamation-triangle-fill me-2 fs-5"></i>
                        <span>Por favor, corrige los siguientes errores:</span>
                    </div>

                    <ul class="mb-0 ps-4">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @if (session('error'))
                <div class="alert alert-danger fw-bold rounded-3 shadow-sm border-0 mb-4">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i> {{ session('error') }}
                </div>
            @endif

            <!-- FORMULARIO DE ALTA / EDICIÓN -->
            <div id="formulario-catalogo" class="card admin-card-content shadow-sm mb-4 overflow-hidden">
                <div class="card-header text-white px-4 py-3"
                     style="background-color: #212529;">
                    <h5 class="mb-0 fw-bold">
                        @if($servicioEditar)
                            <i class="bi bi-pencil-square me-2"></i> Editando concepto #{{ $servicioEditar->id_servicio }}
                        @else
                            <i class="bi bi-plus-circle me-2"></i> Nuevo concepto de pago
                        @endif
                    </h5>
                </div>

                <div class="card-body p-4">
                    <form action="{{ $servicioEditar ? route('admin.pagos.catalogo.update', $servicioEditar->id_servicio) : route('admin.pagos.catalogo.store') }}"
                          method="POST"
                          class="bg-white rounded-3 border border-secondary p-4 shadow-sm">
                        @csrf
                        @if($servicioEditar)
                            @method('PUT')
                        @endif

                        <div class="row g-3">
                            <div class="col-12 col-md-3">
                                <label for="articulo" class="form-label fw-bold text-dark">Artículo <span class="text-muted fw-normal small">(opcional)</span></label>
                                <input type="text"
                                       id="articulo"
                                       name="articulo"
                                       maxlength="50"
                                       class="form-control @error('articulo') is-invalid @enderror"
                                       placeholder="Ej. Art. 14 Fracc. I"
                                       value="{{ old('articulo', $servicioEditar->articulo ?? '') }}">
                                @error('articulo')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-12 col-md-9">
                                <label for="concepto" class="form-label fw-bold text-dark">Concepto <span class="text-danger">*</span></label>
                                <input type="text"
                                       id="concepto"
                                       name="concepto"
                                       maxlength="255"
                                       required
                                       class="form-control @error('concepto') is-invalid @enderror"
                                       placeholder="Ej. Constancia de estudios con calificaciones"
                                       value="{{ old('concepto', $servicioEditar->concepto ?? '') }}">
                                @error('concepto')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-12 col-md-4">
                                <label for="costo_servicio" class="form-label fw-bold text-dark">Costo del servicio <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text">$</span>
                                    <input type="number"
                                           id="costo_servicio"
                                           name="costo_servicio"
                                           step="0.01"
                                           min="0"
                                           max="999999.99"
                                           required
                                           class="form-control @error('costo_servicio') is-invalid @enderror"
                                           value="{{ old('costo_servicio', $servicioEditar ? number_format((float) $servicioEditar->costo_servicio, 2, '.', '') : '0.00') }}">
                                </div>
                                @error('costo_servicio')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-12 col-md-4">
                                <label for="derechos" class="form-label fw-bold text-dark">Derechos <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text">$</span>
                                    <input type="number"
                                           id="derechos"
                                           name="derechos"
                                           step="0.01"
                                           min="0"
                                           max="999999.99"
                                           required
                                           class="form-control @error('derechos') is-invalid @enderror"
                                           value="{{ old('derechos', $servicioEditar ? number_format((float) $servicioEditar->derechos, 2, '.', '') : '0.00') }}">
                                </div>
                                @error('derechos')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-12 col-md-4">
                                <label for="total_preview" class="form-label fw-bold text-dark">Total calculado</label>
                                <div class="input-group">
                                    <span class="input-group-text fw-bold text-success">$</span>
                                    <input type="text"
                                           id="total_preview"
                                           class="form-control fw-bold text-success bg-light"
                                           value="{{ $servicioEditar ? number_format((float) $servicioEditar->total, 2, '.', '') : '0.00' }}"
                                           readonly>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex flex-wrap justify-content-end gap-2 mt-4">
                            @if($servicioEditar)
                                <a href="{{ route('admin.pagos.catalogo', request()->only('buscar', 'page')) }}"
                                   class="btn btn-secondary fw-bold px-4">
                                    Cancelar
                                </a>
                            @endif

                            <button type="submit"
                                    class="btn text-white fw-bold px-4 shadow-sm"
                                    style="background-color: #198754;">
                                <i class="bi {{ $servicioEditar ? 'bi-check-circle' : 'bi-save' }} me-2"></i>
                                {{ $servicioEditar ? 'Guardar cambios' : 'Agregar al catálogo' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- BARRA INFORMATIVA Y BUSCADOR -->
            <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-3">
                <div class="text-muted small">
                    <i class="bi bi-info-circle me-1"></i>
                    Los cambios de precio aplican solo a solicitudes nuevas; los folios ya generados conservan su monto original.
                </div>

                <form action="{{ route('admin.pagos.catalogo') }}" method="GET" class="d-flex" style="width: 380px; max-width: 100%;">
                    <div class="input-group shadow-sm">
                        <input type="text"
                               name="buscar"
                               class="form-control border-0"
                               placeholder="Buscar por artículo o concepto..."
                               value="{{ $buscar ?? '' }}">
                        <button type="submit" class="btn text-white fw-bold px-3" style="background-color: #198754;">
                            <i class="bi bi-search"></i>
                        </button>
                        @if(request('buscar'))
                            <a href="{{ route('admin.pagos.catalogo') }}" class="btn btn-secondary px-3 text-white" title="Limpiar búsqueda">
                                <i class="bi bi-x-lg"></i>
                            </a>
                        @endif
                    </div>
                </form>
            </div>

            <!-- LISTADO DEL CATÁLOGO -->
            <div class="card admin-card-content shadow-sm p-4">

                <div class="table-responsive bg-white rounded-3 border border-secondary shadow-sm">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light text-muted small">
                            <tr>
                                <th class="ps-4">ARTÍCULO</th>
                                <th>CONCEPTO</th>
                                <th class="text-end">COSTO SERVICIO</th>
                                <th class="text-end">DERECHOS</th>
                                <th class="text-end">TOTAL</th>
                                <th class="text-center">ACCIONES</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($servicios as $servicio)
                                @php
                                    $enEdicion = $servicioEditar && $servicioEditar->id_servicio === $servicio->id_servicio;
                                @endphp
                                <tr class="{{ $enEdicion ? 'table-success' : '' }}">
                                    <td class="fw-bold text-dark ps-4">
                                        {{ $servicio->articulo ?: 'Sin artículo' }}
                                    </td>
                                    <td class="text-dark">
                                        {{ $servicio->concepto }}
                                        @if($enEdicion)
                                            <span class="badge bg-success ms-1">Editando</span>
                                        @endif
                                        @if($servicio->detalles_count > 0)
                                            <span class="badge bg-light text-secondary border ms-1">
                                                {{ $servicio->detalles_count }} {{ $servicio->detalles_count === 1 ? 'solicitud' : 'solicitudes' }}
                                            </span>
                                        @endif
                                    </td>
                                    <td class="text-end text-secondary">
                                        ${{ number_format($servicio->costo_servicio, 2) }}
                                    </td>
                                    <td class="text-end text-secondary">
                                        ${{ number_format($servicio->derechos, 2) }}
                                    </td>
                                    <td class="text-end fw-bold text-success fs-6">
                                        ${{ number_format($servicio->total, 2) }}
                                    </td>
                                    <td class="text-center px-3" style="width: 190px;">
                                        <div class="d-inline-flex align-items-center gap-2">
                                            <a href="{{ route('admin.pagos.catalogo', array_merge(request()->only('buscar', 'page'), ['editar' => $servicio->id_servicio])) }}#formulario-catalogo"
                                               class="btn btn-sm text-white fw-semibold px-3"
                                               style="background-color: #212529;">
                                                <i class="bi bi-pencil-square me-1"></i> Editar
                                            </a>

                                            @if($servicio->detalles_count > 0)
                                                <button type="button"
                                                        class="btn btn-sm btn-outline-secondary"
                                                        disabled
                                                        title="No se puede eliminar porque ya tiene solicitudes registradas">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            @else
                                                <form action="{{ route('admin.pagos.catalogo.destroy', $servicio->id_servicio) }}"
                                                      method="POST"
                                                      class="m-0"
                                                      onsubmit="return confirm('¿Seguro que deseas eliminar este concepto del catálogo?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit"
                                                            class="btn btn-sm btn-danger"
                                                            title="Eliminar concepto">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                </form>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center py-5">
                                        <i class="bi bi-card-list fs-1 text-muted opacity-50 d-block mb-2"></i>
                                        <span class="fw-bold text-muted">
                                            {{ $buscar !== '' ? 'No se encontraron conceptos con esa búsqueda.' : 'Aún no hay conceptos registrados en el catálogo de pagos.' }}
                                        </span>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="d-flex justify-content-end mt-4">
                    {{ $servicios->appends(request()->query())->links() }}
                </div>

            </div>

        </main>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const costoInput = document.getElementById('costo_servicio');
        const derechosInput = document.getElementById('derechos');
        const totalPreview = document.getElementById('total_preview');

        function actualizarTotal() {
            const costo = parseFloat(costoInput.value) || 0;
            const derechos = parseFloat(derechosInput.value) || 0;
            const total = Math.max(0, costo) + Math.max(0, derechos);
            totalPreview.value = total.toFixed(2);
        }

        costoInput.addEventListener('input', actualizarTotal);
        derechosInput.addEventListener('input', actualizarTotal);
        actualizarTotal();
    });
</script>
@endsection
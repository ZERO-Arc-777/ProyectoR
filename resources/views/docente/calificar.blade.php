@extends('layouts.app')

@section('titulo', 'Captura de Calificaciones')

@section('usuario_header')
    <span class="fs-6">
        <i class="bi bi-person-circle me-1"></i>
        {{ Auth::user()->nombre ?? 'Docente' }}
    </span>
@endsection

@section('contenido')

<style>
    .calificaciones-panel {
        min-height: 100vh;
        background-color: #f4f6f9;
        color: #212529;
    }

    .calificaciones-sidebar {
        background: #212529;
        border-right: 1px solid #343a40;
    }

    .calificaciones-nav {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 13px 16px;
        border-radius: 12px;
        color: #adb5bd;
        text-decoration: none;
        transition: .2s;
    }

    .calificaciones-nav:hover {
        background: rgba(255, 255, 255, .1);
        color: #ffffff;
    }

    .calificaciones-nav.active {
        background: #fd7e14;
        color: #ffffff;
        font-weight: 600;
    }

    .calificaciones-muted {
        color: #6c757d;
    }

    /* TARJETA BLANCA PARA TABLA */
    .calificaciones-table-card {
        background: #ffffff;
        border: 1px solid #c0c4c5;
        border-radius: 20px;
        padding: 25px;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.02);
    }

    /* ESTILOS DE TABLA */
    .table-calificaciones {
        width: 100%;
        margin-bottom: 0;
        border-collapse: separate;
        border-spacing: 0;
    }

    .table-calificaciones th {
        background-color: transparent;
        color: #212529;
        font-weight: 700;
        text-transform: uppercase;
        font-size: 0.78rem;
        padding: 15px 8px;
        border-bottom: 2px solid #e9ecef;
        text-align: left;
        white-space: nowrap;
    }

    .table-calificaciones td {
        padding: 14px 8px;
        vertical-align: middle;
        border-bottom: 1px solid #f4f6f9;
        color: #495057;
    }

    .table-calificaciones tr:last-child td {
        border-bottom: none;
    }

    .table-calificaciones tr:hover td {
        background-color: #f8f9fa;
    }

    /* ETIQUETAS DE ACENTO NARANJA */
    .calificaciones-etiqueta {
        display: inline-flex;
        align-items: center;
        padding: 4px 10px;
        border-radius: 30px;
        background: rgba(253, 126, 20, 0.15);
        color: #d95c00;
        font-size: .75rem;
        font-weight: 600;
        margin-left: 5px;
    }

    /* ESTADO VACÍO */
    .calificaciones-vacio {
        background: #ffffff;
        border: 1px dashed #c0c4c5;
        border-radius: 20px;
        padding: 55px 25px;
        text-align: center;
        color: #212529;
    }

    .calificaciones-vacio-icon {
        width: 80px;
        height: 80px;
        border-radius: 22px;
        background: rgba(253, 126, 20, 0.15);
        color: #fd7e14;
        display: grid;
        place-items: center;
        margin: 0 auto 22px;
        font-size: 2.2rem;
    }

    /* INPUTS DE CALIFICACIÓN */
    .input-calificacion {
        width: 74px;
        text-align: center;
        font-weight: 600;
    }

    .promedio-badge {
        min-width: 56px;
        font-size: 0.85rem;
        font-weight: 700;
    }
</style>

@php
    $formatearInput = function ($valor) {
        if ($valor === null || $valor === '') {
            return '';
        }

        return rtrim(rtrim(number_format((float) $valor, 2, '.', ''), '0'), '.');
    };
@endphp

<div class="container-fluid calificaciones-panel">

    <div class="row min-vh-100">

        <!-- MENÚ LATERAL -->
        <aside class="col-12 col-md-3 col-lg-2 calificaciones-sidebar p-4">
            <div class="text-center mb-4">
                <i class="bi bi-mortarboard-fill fs-2 text-white"></i>
                <h5 class="fw-bold mt-2 mb-1 text-white">
                    PANEL DOCENTE
                </h5>
                <small class="calificaciones-muted">
                    Gestión Académica
                </small>
            </div>

            <hr class="border-secondary my-4">

            <nav class="d-grid gap-2" aria-label="Menú del docente">
                <a href="{{ route('docente.dashboard') }}" class="calificaciones-nav">
                    <i class="bi bi-grid-1x2-fill"></i> Inicio
                </a>
                <a href="{{ route('docente.materias') }}" class="calificaciones-nav active">
                    <i class="bi bi-journal-bookmark-fill"></i> Mis Materias
                </a>
                <a href="{{ route('docente.grupos') }}" class="calificaciones-nav">
                    <i class="bi bi-people-fill"></i> Mis Grupos
                </a>
                <a href="{{ route('docente.perfil') }}" class="calificaciones-nav">
                    <i class="bi bi-person-badge-fill"></i> Mi Perfil
                </a>
                <hr class="border-secondary my-3">
                <!-- CERRAR SESIÓN SEGURO -->
                <form action="{{ route('logout.seguro') }}" method="POST" class="m-0">
                    @csrf

                    <button type="submit"
                            class="calificaciones-nav text-danger"
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
                        <i class="bi bi-box-arrow-right"></i>
                        <span>Cerrar sesión</span>
                    </button>
                </form>
            </nav>
        </aside>

        <!-- CONTENIDO PRINCIPAL -->
        <main class="col-12 col-md-9 col-lg-10 p-4 p-lg-5">

            <!-- ENCABEZADO CON BOTÓN DE VOLVER -->
            <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
                <div>
                    <h2 class="text-dark fw-bold mb-2">
                        Captura de Calificaciones
                    </h2>
                    <p class="calificaciones-muted mb-0">
                        Materia: <strong class="text-dark">{{ $asignacion->materia->nombre }}</strong> |
                        Semestre: <span class="calificaciones-etiqueta">{{ $asignacion->materia->semestre }}°</span> |
                        Grupo: <span class="calificaciones-etiqueta">{{ $asignacion->grupo }}</span>
                        @if(!empty($asignacion->periodo))
                            | Periodo: <strong class="text-dark">{{ $asignacion->periodo }}</strong>
                        @endif
                    </p>
                </div>

                <a href="{{ route('docente.materias') }}" class="btn btn-sm btn-outline-secondary rounded-pill px-3 d-inline-flex align-items-center gap-1">
                    <i class="bi bi-arrow-left"></i> Volver a mis materias
                </a>
            </div>

            <!-- ALERTA DE ÉXITO -->
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show rounded-4 d-flex align-items-center gap-2 mb-4" role="alert">
                    <i class="bi bi-check-circle-fill fs-5"></i>
                    <div>{{ session('success') }}</div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            <!-- ALERTA DE ERRORES DE VALIDACIÓN -->
            @if($errors->any())
                <div class="alert alert-danger alert-dismissible fade show rounded-4 mb-4" role="alert">
                    <div class="d-flex align-items-center gap-2 fw-bold mb-1">
                        <i class="bi bi-exclamation-triangle-fill fs-5"></i>
                        <span>Revisa las calificaciones ingresadas (rango permitido: 0 a 10):</span>
                    </div>
                    <ul class="mb-0 ps-4 small">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            <!-- FORMULARIO EN TARJETA BLANCA -->
            <div class="calificaciones-table-card">
                <form action="{{ route('docente.materias.guardar', $asignacion->id_asignacion) }}" method="POST">
                    @csrf

                    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
                        <div>
                            <h5 class="fw-bold mb-1">
                                <i class="bi bi-list-ol me-2 text-dark"></i>
                                Listado de Alumnos
                            </h5>
                            <small class="calificaciones-muted">
                                Escala de 0 a 10. El promedio se calcula automáticamente dividiendo la suma de las calificaciones capturadas entre la cantidad registrada.
                            </small>
                        </div>
                    </div>

                    <div class="table-responsive mb-4">
                        <table class="table-calificaciones">
                            <thead>
                                <tr>
                                    <th style="width: 5%;">NÚM.</th>
                                    <th style="width: 11%;">MATRÍCULA</th>
                                    <th style="width: 30%;">NOMBRE DEL ALUMNO</th>
                                    <th class="text-center" style="width: 11%;">PARCIAL 1</th>
                                    <th class="text-center" style="width: 11%;">PARCIAL 2</th>
                                    <th class="text-center" style="width: 11%;">PARCIAL 3</th>
                                    <th class="text-center" style="width: 11%;">EVAL. FINAL</th>
                                    <th class="text-center" style="width: 10%;">PROMEDIO</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($alumnos as $index => $alumno)
                                    @php
                                        $registro = $calificacionesPrevias[$alumno->id_alumno] ?? null;

                                        $valC1 = old("calificaciones.{$alumno->id_alumno}.calif_1", $formatearInput($registro?->calif_1));
                                        $valC2 = old("calificaciones.{$alumno->id_alumno}.calif_2", $formatearInput($registro?->calif_2));
                                        $valC3 = old("calificaciones.{$alumno->id_alumno}.calif_3", $formatearInput($registro?->calif_3));
                                        $valEf = old("calificaciones.{$alumno->id_alumno}.eval_final", $formatearInput($registro?->eval_final));

                                        $promedioGuardado = $registro?->calificacion;
                                    @endphp

                                    <tr data-alumno-row="{{ $alumno->id_alumno }}">
                                        <td>
                                            <span class="text-muted fw-bold">{{ $index + 1 }}</span>
                                        </td>
                                        <td>
                                            <span class="fw-medium">{{ $alumno->matricula }}</span>
                                        </td>
                                        <td>
                                            <span class="fw-bold text-dark">
                                                {{ $alumno->apellido_paterno }} {{ $alumno->apellido_materno }} {{ $alumno->nombres }}
                                            </span>
                                        </td>
                                        <td class="text-center">
                                            <input type="number"
                                                   step="0.1"
                                                   min="0"
                                                   max="10"
                                                   name="calificaciones[{{ $alumno->id_alumno }}][calif_1]"
                                                   value="{{ $valC1 }}"
                                                   placeholder="-"
                                                   class="form-control form-control-sm input-calificacion nota-input mx-auto">
                                        </td>
                                        <td class="text-center">
                                            <input type="number"
                                                   step="0.1"
                                                   min="0"
                                                   max="10"
                                                   name="calificaciones[{{ $alumno->id_alumno }}][calif_2]"
                                                   value="{{ $valC2 }}"
                                                   placeholder="-"
                                                   class="form-control form-control-sm input-calificacion nota-input mx-auto">
                                        </td>
                                        <td class="text-center">
                                            <input type="number"
                                                   step="0.1"
                                                   min="0"
                                                   max="10"
                                                   name="calificaciones[{{ $alumno->id_alumno }}][calif_3]"
                                                   value="{{ $valC3 }}"
                                                   placeholder="-"
                                                   class="form-control form-control-sm input-calificacion nota-input mx-auto">
                                        </td>
                                        <td class="text-center">
                                            <input type="number"
                                                   step="0.1"
                                                   min="0"
                                                   max="10"
                                                   name="calificaciones[{{ $alumno->id_alumno }}][eval_final]"
                                                   value="{{ $valEf }}"
                                                   placeholder="-"
                                                   class="form-control form-control-sm input-calificacion nota-input mx-auto">
                                        </td>
                                        <td class="text-center">
                                            @if($promedioGuardado !== null)
                                                <span class="badge rounded-pill px-3 py-2 promedio-badge {{ (float) $promedioGuardado >= 6 ? 'bg-success' : 'bg-danger' }}"
                                                      data-promedio-display>
                                                    {{ $formatearInput($promedioGuardado) }}
                                                </span>
                                            @else
                                                <span class="badge rounded-pill px-3 py-2 promedio-badge bg-light text-muted border"
                                                      data-promedio-display>
                                                    ---
                                                </span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="p-0 border-0 pt-3">
                                            <div class="calificaciones-vacio mt-2">
                                                <div class="calificaciones-vacio-icon">
                                                    <i class="bi bi-person-x"></i>
                                                </div>
                                                <h4 class="fw-bold mb-2">Sin alumnos registrados</h4>
                                                <p class="calificaciones-muted mb-0">
                                                    No hay alumnos inscritos en este grupo y semestre en este momento.
                                                </p>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- BOTÓN VERDE FLOTANDO A LA DERECHA -->
                    @if($alumnos->count() > 0)
                        <div class="d-flex justify-content-end border-top pt-4 mt-2">
                            <button type="submit" class="btn btn-success rounded-pill px-4 fw-medium d-inline-flex align-items-center gap-2">
                                <i class="bi bi-floppy-fill"></i> Guardar Calificaciones
                            </button>
                        </div>
                    @endif

                </form>
            </div>

        </main>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const formatearPromedio = function (valor) {
            return parseFloat(valor.toFixed(2)).toString();
        };

        const recalcularFila = function (fila) {
            const inputs = fila.querySelectorAll('.nota-input');
            const badge = fila.querySelector('[data-promedio-display]');

            if (!badge) {
                return;
            }

            let suma = 0;
            let cantidad = 0;

            inputs.forEach(function (input) {
                const texto = input.value.trim();
                if (texto === '') {
                    return;
                }

                const numero = parseFloat(texto);
                if (!Number.isNaN(numero) && numero >= 0 && numero <= 10) {
                    suma += numero;
                    cantidad++;
                }
            });

            if (cantidad === 0) {
                badge.textContent = '---';
                badge.className = 'badge rounded-pill px-3 py-2 promedio-badge bg-light text-muted border';
                return;
            }

            const promedio = suma / cantidad;
            badge.textContent = formatearPromedio(promedio);
            badge.className = 'badge rounded-pill px-3 py-2 promedio-badge ' + (promedio >= 6 ? 'bg-success' : 'bg-danger');
        };

        document.querySelectorAll('tr[data-alumno-row]').forEach(function (fila) {
            const inputs = fila.querySelectorAll('.nota-input');

            // Si al menos uno de los 4 rubros ya tiene valor, sincroniza la vista previa al cargar.
            const tieneAlgunRubro = Array.from(inputs).some(function (input) {
                return input.value.trim() !== '';
            });

            if (tieneAlgunRubro) {
                recalcularFila(fila);
            }

            inputs.forEach(function (input) {
                input.addEventListener('input', function () {
                    recalcularFila(fila);
                });
            });
        });
    });
</script>

@endsection
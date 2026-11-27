@extends('layouts.app')

@section('titulo', 'Captura de Calificaciones')

@section('usuario_header')
    <span class="fs-6">
        <i class="bi bi-person-circle me-1"></i>
        {{ Auth::user()->nombre ?? 'Docente' }}
    </span>
@endsection

@section('contenido')

@php
    $formatearEntero = static function ($valor) {
        if ($valor === null || $valor === '') {
            return '';
        }

        return (string) ((int) $valor);
    };
@endphp

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

    .calificaciones-table-card {
        background: #ffffff;
        border: 1px solid #c0c4c5;
        border-radius: 20px;
        padding: 25px;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.02);
    }

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

    .table-calificaciones tr.fila-eval-final td {
        background-color: rgba(255, 193, 7, 0.08);
    }

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

    .input-calificacion {
        width: 74px;
        text-align: center;
        font-weight: 600;
    }

    .input-calificacion:disabled {
        background-color: #f1f3f5;
        color: #adb5bd;
        cursor: not-allowed;
    }

    .ef-hint {
        display: block;
        font-size: 0.7rem;
        line-height: 1.2;
        margin-top: 4px;
        font-weight: 600;
    }

    .promedio-badge {
        min-width: 68px;
        font-size: 0.82rem;
        font-weight: 700;
    }

    .reglas-box {
        background-color: #f8f9fa;
        border: 1px solid #e9ecef;
        border-radius: 14px;
        padding: 12px 16px;
        font-size: 0.83rem;
        color: #495057;
    }
</style>

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
                <form action="{{ route('logout.seguro') }}" method="POST" class="m-0">
                    @csrf
                    <button type="submit"
                            class="calificaciones-nav text-danger"
                            style="background: none; border: none; cursor: pointer; width: 100%; text-align: left; font: inherit; display: flex; align-items: center; gap: 12px; white-space: nowrap;">
                        <i class="bi bi-box-arrow-right"></i>
                        <span>Cerrar sesión</span>
                    </button>
                </form>
            </nav>
        </aside>

        <!-- CONTENIDO PRINCIPAL -->
        <main class="col-12 col-md-9 col-lg-10 p-4 p-lg-5">

            <!-- ENCABEZADO -->
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

                <a href="{{ route('docente.materias', ['periodo' => $asignacion->periodo]) }}" class="btn btn-sm btn-outline-secondary rounded-pill px-3 d-inline-flex align-items-center gap-1">
                    <i class="bi bi-arrow-left"></i> Volver a mis materias
                </a>
            </div>

            <!-- AVISO SI EL PERIODO ESTÁ CERRADO -->
            @if($esSoloLectura)
                <div class="alert alert-warning rounded-4 d-flex align-items-center gap-2 mb-4 border-0 shadow-sm" role="alert">
                    <i class="bi bi-lock-fill fs-5"></i>
                    <div>
                        <strong>Periodo cerrado (Solo lectura):</strong> Las calificaciones de este ciclo ya no pueden editarse.
                    </div>
                </div>
            @endif

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
                        <span>Revisa las calificaciones ingresadas (solo enteros del 5 al 10):</span>
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
                <form action="{{ route('docente.materias.guardar', $asignacion->id_asignacion) }}"
                      method="POST"
                      id="formCalificaciones">
                    @csrf

                    <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-3">
                        <div>
                            <h5 class="fw-bold mb-1">
                                <i class="bi bi-list-ol me-2 text-dark"></i>
                                Listado de Alumnos
                            </h5>
                            <small class="calificaciones-muted">
                                Captura únicamente números enteros del <strong>5 al 10</strong>. Mientras el periodo siga abierto, cualquier corrección es reversible.
                            </small>
                        </div>
                    </div>

                    <div class="reglas-box mb-4">
                        <i class="bi bi-info-circle-fill text-warning me-1"></i>
                        <strong>Regla de Evaluación Final:</strong> Se habilita al confirmar <strong>2 o más parciales en 5</strong> (el Parcial 3 queda opcional) o cuando la suma de los 3 parciales es <strong>menor a 18 puntos</strong>. La nota de Evaluación Final sustituye al promedio de parciales.
                    </div>

                    <div class="table-responsive mb-4">
                        <table class="table-calificaciones">
                            <thead>
                                <tr>
                                    <th style="width: 5%;">NÚM.</th>
                                    <th style="width: 11%;">MATRÍCULA</th>
                                    <th style="width: 28%;">NOMBRE DEL ALUMNO</th>
                                    <th class="text-center" style="width: 11%;">PARCIAL 1</th>
                                    <th class="text-center" style="width: 11%;">PARCIAL 2</th>
                                    <th class="text-center" style="width: 11%;">PARCIAL 3</th>
                                    <th class="text-center" style="width: 12%;">EVAL. FINAL</th>
                                    <th class="text-center" style="width: 11%;">PROMEDIO</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($alumnos as $index => $alumno)
                                    @php
                                        $registro = $calificacionesPrevias[$alumno->id_alumno] ?? null;

                                        $p1Previo = $registro?->calif_1 !== null ? (int) $registro->calif_1 : null;
                                        $p2Previo = $registro?->calif_2 !== null ? (int) $registro->calif_2 : null;
                                        $p3Previo = $registro?->calif_3 !== null ? (int) $registro->calif_3 : null;
                                        $efPrevio = $registro?->eval_final !== null ? (int) $registro->eval_final : null;

                                        $requeriaAntes = \App\Models\Calificacion::requiereEvaluacionFinal($p1Previo, $p2Previo, $p3Previo);

                                        $valC1 = old("calificaciones.{$alumno->id_alumno}.calif_1", $formatearEntero($p1Previo));
                                        $valC2 = old("calificaciones.{$alumno->id_alumno}.calif_2", $formatearEntero($p2Previo));
                                        $valC3 = old("calificaciones.{$alumno->id_alumno}.calif_3", $formatearEntero($p3Previo));
                                        $valEf = old("calificaciones.{$alumno->id_alumno}.eval_final", $formatearEntero($efPrevio));

                                        $nombreCompleto = trim(implode(' ', array_filter([
                                            $alumno->apellido_paterno,
                                            $alumno->apellido_materno,
                                            $alumno->nombres,
                                        ])));

                                        $promedioGuardado = $registro?->calificacion;
                                    @endphp

                                    <tr data-alumno-row="{{ $alumno->id_alumno }}"
                                        data-alumno-nombre="{{ $nombreCompleto }}"
                                        data-requeria-antes="{{ $requeriaAntes ? '1' : '0' }}"
                                        data-ef-previo="{{ $efPrevio ?? '' }}"
                                        class="{{ $requeriaAntes ? 'fila-eval-final' : '' }}">
                                        <td>
                                            <span class="text-muted fw-bold">{{ $index + 1 }}</span>
                                        </td>
                                        <td>
                                            <span class="fw-medium">{{ $alumno->matricula }}</span>
                                        </td>
                                        <td>
                                            <span class="fw-bold text-dark">{{ $nombreCompleto }}</span>
                                        </td>
                                        <td class="text-center">
                                            <input type="number"
                                                   step="1"
                                                   min="5"
                                                   max="10"
                                                   inputmode="numeric"
                                                   name="calificaciones[{{ $alumno->id_alumno }}][calif_1]"
                                                   value="{{ $valC1 }}"
                                                   placeholder="-"
                                                   {{ $esSoloLectura ? 'disabled' : '' }}
                                                   class="form-control form-control-sm input-calificacion parcial-input mx-auto"
                                                   data-parcial="1">
                                        </td>
                                        <td class="text-center">
                                            <input type="number"
                                                   step="1"
                                                   min="5"
                                                   max="10"
                                                   inputmode="numeric"
                                                   name="calificaciones[{{ $alumno->id_alumno }}][calif_2]"
                                                   value="{{ $valC2 }}"
                                                   placeholder="-"
                                                   {{ $esSoloLectura ? 'disabled' : '' }}
                                                   class="form-control form-control-sm input-calificacion parcial-input mx-auto"
                                                   data-parcial="2">
                                        </td>
                                        <td class="text-center">
                                            <input type="number"
                                                   step="1"
                                                   min="5"
                                                   max="10"
                                                   inputmode="numeric"
                                                   name="calificaciones[{{ $alumno->id_alumno }}][calif_3]"
                                                   value="{{ $valC3 }}"
                                                   placeholder="-"
                                                   {{ $esSoloLectura ? 'disabled' : '' }}
                                                   class="form-control form-control-sm input-calificacion parcial-input mx-auto"
                                                   data-parcial="3">
                                        </td>
                                        <td class="text-center">
                                            <input type="number"
                                                   step="1"
                                                   min="5"
                                                   max="10"
                                                   inputmode="numeric"
                                                   name="calificaciones[{{ $alumno->id_alumno }}][eval_final]"
                                                   value="{{ $valEf }}"
                                                   placeholder="-"
                                                   {{ ($esSoloLectura || !$requeriaAntes) ? 'disabled' : '' }}
                                                   class="form-control form-control-sm input-calificacion ef-input mx-auto">
                                            <span class="ef-hint text-muted d-none" data-ef-hint></span>
                                        </td>
                                        <td class="text-center">
                                            @if($requeriaAntes && $efPrevio === null)
                                                <span class="badge rounded-pill px-3 py-2 promedio-badge bg-warning text-dark"
                                                      data-promedio-display>
                                                    Pend. EF
                                                </span>
                                            @elseif($promedioGuardado !== null)
                                                <span class="badge rounded-pill px-3 py-2 promedio-badge {{ (float) $promedioGuardado >= 6 ? 'bg-success' : 'bg-danger' }}"
                                                      data-promedio-display>
                                                    {{ \App\Models\Calificacion::formatearPromedio((float) $promedioGuardado) }}
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

                    @if($alumnos->count() > 0 && !$esSoloLectura)
                        <div class="d-flex justify-content-end border-top pt-4 mt-2">
                            <button type="submit"
                                    id="btnGuardarCalificaciones"
                                    class="btn btn-success rounded-pill px-4 fw-medium d-inline-flex align-items-center gap-2">
                                <i class="bi bi-floppy-fill"></i> Guardar Calificaciones
                            </button>
                        </div>
                    @endif

                </form>
            </div>

        </main>
    </div>
</div>

{{-- MODAL DE CONFIRMACIÓN ANTI-ERRORES --}}
@if(!$esSoloLectura)
<div class="modal fade" id="modalConfirmarEvalFinal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header bg-dark text-white">
                <h6 class="modal-title fw-bold">
                    <i class="bi bi-exclamation-triangle-fill text-warning me-2"></i>
                    Confirmar Cambios en Evaluación Final
                </h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                <div id="bloqueNuevosEF" class="d-none mb-3">
                    <p class="small fw-bold text-dark mb-2">
                        Los siguientes alumnos pasarán a Evaluación Final. Verifica que no haya errores de captura antes de continuar:
                    </p>
                    <ul id="listaNuevosEF" class="list-group list-group-flush small border rounded-3"></ul>
                </div>

                <div id="bloqueRevertidosEF" class="d-none mb-2">
                    <p class="small fw-bold text-dark mb-2">
                        Al corregir sus parciales, estos alumnos salen de Evaluación Final y se limpiará la nota final registrada previamente:
                    </p>
                    <ul id="listaRevertidosEF" class="list-group list-group-flush small border rounded-3"></ul>
                </div>

                <p class="small text-muted mb-0 mt-2">
                    Podrás corregir cualquier calificación mientras el periodo escolar permanezca abierto.
                </p>
            </div>
            <div class="modal-footer border-0">
                <button type="button" class="btn btn-outline-secondary rounded-pill px-3 fw-semibold" data-bs-dismiss="modal">
                    Revisar antes
                </button>
                <button type="button" class="btn btn-success rounded-pill px-4 fw-semibold" id="btnConfirmarEnvioEF">
                    <i class="bi bi-check-lg me-1"></i> Confirmar y Guardar
                </button>
            </div>
        </div>
    </div>
</div>
@endif

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const esSoloLectura = {{ $esSoloLectura ? 'true' : 'false' }};
        const form = document.getElementById('formCalificaciones');
        let envioConfirmado = false;

        const leerEnteroValido = function (input) {
            const texto = input.value.trim();
            if (texto === '') {
                input.classList.remove('is-invalid');
                return { estado: 'vacio', valor: null };
            }

            if (!/^\d+$/.test(texto)) {
                input.classList.add('is-invalid');
                return { estado: 'invalido', valor: null };
            }

            const numero = parseInt(texto, 10);
            if (numero < 5 || numero > 10) {
                input.classList.add('is-invalid');
                return { estado: 'invalido', valor: null };
            }

            input.classList.remove('is-invalid');
            return { estado: 'valido', valor: numero };
        };

        const requiereEvaluacionFinal = function (p1, p2, p3) {
            const capturados = [p1, p2, p3].filter(function (v) { return v !== null; });
            const reprobados = capturados.filter(function (v) { return v < 6; }).length;

            if (reprobados >= 2) {
                return true;
            }

            if (capturados.length === 3) {
                const suma = capturados[0] + capturados[1] + capturados[2];
                if (suma < 18) {
                    return true;
                }
            }

            return false;
        };

        const aplicarReglaRedondeo = function (valor) {
            const truncadoCent = Math.floor((valor * 100) + 1e-9);
            const segundoDecimal = truncadoCent % 10;

            if (segundoDecimal >= 5) {
                return (Math.floor(truncadoCent / 10) + 1) / 10;
            }

            return truncadoCent / 100;
        };

        const formatearPromedio = function (valor) {
            const ajustado = aplicarReglaRedondeo(valor);
            return parseFloat(ajustado.toFixed(2)).toString();
        };

        const analizarFila = function (fila) {
            const p1Input = fila.querySelector('[data-parcial="1"]');
            const p2Input = fila.querySelector('[data-parcial="2"]');
            const p3Input = fila.querySelector('[data-parcial="3"]');
            const efInput = fila.querySelector('.ef-input');

            const r1 = leerEnteroValido(p1Input);
            const r2 = leerEnteroValido(p2Input);
            const r3 = leerEnteroValido(p3Input);
            const rEf = leerEnteroValido(efInput);

            const requeriaAntes = fila.dataset.requeriaAntes === '1';
            const efPrevio = fila.dataset.efPrevio !== '' ? parseInt(fila.dataset.efPrevio, 10) : null;
            const requiereAhora = requiereEvaluacionFinal(r1.valor, r2.valor, r3.valor);

            return {
                nombre: fila.dataset.alumnoNombre,
                p1: r1.valor,
                p2: r2.valor,
                p3: r3.valor,
                ef: rEf.valor,
                requeriaAntes: requeriaAntes,
                efPrevio: efPrevio,
                requiereAhora: requiereAhora,
                efInput: efInput,
                efHint: fila.querySelector('[data-ef-hint]'),
                badge: fila.querySelector('[data-promedio-display]')
            };
        };

        const actualizarFila = function (fila) {
            const info = analizarFila(fila);
            const { p1, p2, p3, ef, requeriaAntes, efPrevio, requiereAhora, efInput, efHint, badge } = info;

            // Control del campo Evaluación Final y su indicación
            if (!esSoloLectura) {
                if (requiereAhora && !requeriaAntes) {
                    // Desbloqueo en dos tiempos: sigue bloqueado hasta confirmar y guardar
                    efInput.disabled = true;
                    efHint.textContent = 'Se habilitará al guardar';
                    efHint.className = 'ef-hint text-warning';
                    fila.classList.add('fila-eval-final');
                } else if (requiereAhora && requeriaAntes) {
                    efInput.disabled = false;
                    efHint.textContent = 'Habilitada';
                    efHint.className = 'ef-hint text-warning';
                    fila.classList.add('fila-eval-final');
                } else if (!requiereAhora && requeriaAntes) {
                    efInput.disabled = true;
                    efHint.textContent = efPrevio !== null ? 'Se limpiará al guardar' : 'Se bloqueará al guardar';
                    efHint.className = 'ef-hint text-info';
                    fila.classList.remove('fila-eval-final');
                } else {
                    efInput.disabled = true;
                    efHint.textContent = '';
                    efHint.className = 'ef-hint d-none';
                    fila.classList.remove('fila-eval-final');
                }
            }

            // Recálculo del badge de Promedio Final
            if (!badge) {
                return;
            }

            if (requiereAhora) {
                if (requeriaAntes && ef !== null) {
                    badge.textContent = String(ef);
                    badge.className = 'badge rounded-pill px-3 py-2 promedio-badge ' + (ef >= 6 ? 'bg-success' : 'bg-danger');
                } else {
                    badge.textContent = 'Pend. EF';
                    badge.className = 'badge rounded-pill px-3 py-2 promedio-badge bg-warning text-dark';
                }
                return;
            }

            const parciales = [p1, p2, p3].filter(function (v) { return v !== null; });

            if (parciales.length === 0) {
                badge.textContent = '---';
                badge.className = 'badge rounded-pill px-3 py-2 promedio-badge bg-light text-muted border';
                return;
            }

            const promedio = parciales.reduce(function (a, b) { return a + b; }, 0) / parciales.length;
            const ajustado = aplicarReglaRedondeo(promedio);

            badge.textContent = formatearPromedio(promedio);
            badge.className = 'badge rounded-pill px-3 py-2 promedio-badge ' + (ajustado >= 6 ? 'bg-success' : 'bg-danger');
        };

        document.querySelectorAll('tr[data-alumno-row]').forEach(function (fila) {
            actualizarFila(fila);

            fila.querySelectorAll('.parcial-input, .ef-input').forEach(function (input) {
                input.addEventListener('input', function () {
                    actualizarFila(fila);
                });
            });
        });

        // Interceptar el envío para mostrar el modal solo si hay ingresos nuevos a EF o pérdida de EF previo
        if (form && !esSoloLectura) {
            const modalEl = document.getElementById('modalConfirmarEvalFinal');
            const bloqueNuevos = document.getElementById('bloqueNuevosEF');
            const listaNuevos = document.getElementById('listaNuevosEF');
            const bloqueRevertidos = document.getElementById('bloqueRevertidosEF');
            const listaRevertidos = document.getElementById('listaRevertidosEF');
            const btnConfirmar = document.getElementById('btnConfirmarEnvioEF');

            form.addEventListener('submit', function (e) {
                if (envioConfirmado) {
                    return;
                }

                const nuevosEF = [];
                const revertidosConNota = [];

                document.querySelectorAll('tr[data-alumno-row]').forEach(function (fila) {
                    const info = analizarFila(fila);

                    if (info.requiereAhora && !info.requeriaAntes) {
                        nuevosEF.push(info);
                    } else if (!info.requiereAhora && info.requeriaAntes && info.efPrevio !== null) {
                        revertidosConNota.push(info);
                    }
                });

                if (nuevosEF.length === 0 && revertidosConNota.length === 0) {
                    return;
                }

                e.preventDefault();

                listaNuevos.innerHTML = '';
                listaRevertidos.innerHTML = '';

                if (nuevosEF.length > 0) {
                    bloqueNuevos.classList.remove('d-none');
                    nuevosEF.forEach(function (item) {
                        const li = document.createElement('li');
                        li.className = 'list-group-item d-flex justify-content-between align-items-center';
                        const detalle = `P1: ${item.p1 ?? '-'} · P2: ${item.p2 ?? '-'} · P3: ${item.p3 ?? '-'}`;
                        li.innerHTML = `<span class="fw-bold text-dark"></span><span class="badge bg-warning text-dark"></span>`;
                        li.querySelector('span.fw-bold').textContent = item.nombre;
                        li.querySelector('span.badge').textContent = detalle;
                        listaNuevos.appendChild(li);
                    });
                } else {
                    bloqueNuevos.classList.add('d-none');
                }

                if (revertidosConNota.length > 0) {
                    bloqueRevertidos.classList.remove('d-none');
                    revertidosConNota.forEach(function (item) {
                        const li = document.createElement('li');
                        li.className = 'list-group-item d-flex justify-content-between align-items-center';
                        li.innerHTML = `<span class="fw-bold text-dark"></span><span class="badge bg-secondary"></span>`;
                        li.querySelector('span.fw-bold').textContent = item.nombre;
                        li.querySelector('span.badge').textContent = `EF previa: ${item.efPrevio}`;
                        listaRevertidos.appendChild(li);
                    });
                } else {
                    bloqueRevertidos.classList.add('d-none');
                }

                if (modalEl && typeof bootstrap !== 'undefined' && bootstrap.Modal) {
                    bootstrap.Modal.getOrCreateInstance(modalEl).show();
                } else {
                    envioConfirmado = true;
                    form.submit();
                }
            });

            if (btnConfirmar) {
                btnConfirmar.addEventListener('click', function () {
                    envioConfirmado = true;
                    form.submit();
                });
            }
        }
    });
</script>

@endsection
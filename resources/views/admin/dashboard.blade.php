@extends('layouts.app')

@section('titulo', 'Panel de Administrador')

@section('usuario_header', 'ADMINISTRATIVO')

@section('contenido')
<div class="d-flex" style="min-height: calc(100vh - 54px);">
    
    <!--MENÚ LATERAL (SIDEBAR)-->
    <div class="bg-light border-end shadow-sm" style="width: 250px; flex-shrink: 0;">
        <div class="list-group list-group-flush text-center fw-bold" style="font-size: 0.95rem;">
            
            <a href="{{ route('admin.dashboard') }}" class="list-group-item list-group-item-action py-3 text-white" style="background-color: #4a4a5a;">
                Inicio
            </a>

            <a href="{{ route('admin.usuarios') }}" class="list-group-item list-group-item-action py-3 text-dark">Usuarios</a>
            <a href="{{ route('admin.alumnos') }}" class="list-group-item list-group-item-action py-3 text-dark">Alumnos</a>
            <a href="{{ route('admin.docentes') }}" class="list-group-item list-group-item-action py-3 text-dark">Docentes</a>
            <a href="{{ route('admin.materias') }}" class="list-group-item list-group-item-action py-3 text-dark">Materias</a>
            <a href="{{ route('admin.pagos') }}" class="list-group-item list-group-item-action py-3 text-dark">Pagos</a>
            
            <a href="{{ route('admin.revision.inscripciones') }}" class="list-group-item list-group-item-action py-3 text-dark bg-opacity-25" style="background-color: #e9ecef;">Rev. Inscripciones</a>
            <a href="{{ route('admin.revision.reinscripciones') }}" class="list-group-item list-group-item-action py-3 text-dark bg-opacity-25" style="background-color: #e9ecef;">Rev. Reinscripciones</a>
            <a href="{{ route('admin.revision.egresos') }}" class="list-group-item list-group-item-action py-3 text-dark bg-opacity-25" style="background-color: #e9ecef;">Rev. Egresos</a>
            
            <a href="{{ route('logout') }}" class="list-group-item list-group-item-action py-3 mt-5 fw-bold text-white" style="background-color: #8b0000;">
                <i class="bi bi-power"></i> Salir
            </a>
        </div>
    </div>

    <!-- ÁREA PRINCIPAL (TARJETAS Y CONTENIDO) -->
    <div class="p-4 p-md-5 flex-grow-1">
        
        <div class="mb-5 text-white">
            <h4 class="fw-bold">Buenos días, ADMIN</h4>
            <p class="mb-0 text-light opacity-75">Bienvenido al Sistema de Gestión Escolar</p>
        </div>

        <div class="row g-4 mb-5">
            
            <div class="col-md-3">
                <a href="{{ route('admin.usuarios') }}" class="text-decoration-none">
                    <div class="card h-100 border-0 shadow" style="background-color: #d1d5d6; border-radius: 12px; transition: transform 0.2s;" onmouseover="this.style.transform='scale(1.03)'" onmouseout="this.style.transform='scale(1)'">
                        <div class="card-body text-center d-flex align-items-center justify-content-center" style="min-height: 120px;">
                            <h5 class="fw-bold text-dark mb-0">USUARIOS</h5>
                        </div>
                    </div>
                </a>
            </div>

            <div class="col-md-3">
                <a href="{{ route('admin.alumnos') }}" class="text-decoration-none">
                    <div class="card h-100 border-0 shadow" style="background-color: #d1d5d6; border-radius: 12px; transition: transform 0.2s;" onmouseover="this.style.transform='scale(1.03)'" onmouseout="this.style.transform='scale(1)'">
                        <div class="card-body text-center d-flex align-items-center justify-content-center" style="min-height: 120px;">
                            <h5 class="fw-bold text-dark mb-0">ALUMNOS</h5>
                        </div>
                    </div>
                </a>
            </div>

            <div class="col-md-3">
                <a href="{{ route('admin.docentes') }}" class="text-decoration-none">
                    <div class="card h-100 border-0 shadow" style="background-color: #d1d5d6; border-radius: 12px; transition: transform 0.2s;" onmouseover="this.style.transform='scale(1.03)'" onmouseout="this.style.transform='scale(1)'">
                        <div class="card-body text-center d-flex align-items-center justify-content-center" style="min-height: 120px;">
                            <h5 class="fw-bold text-dark mb-0">DOCENTES</h5>
                        </div>
                    </div>
                </a>
            </div>

            <div class="col-md-3">
                <a href="{{ route('admin.pagos') }}" class="text-decoration-none">
                    <div class="card h-100 border-0 shadow" style="background-color: #d1d5d6; border-radius: 12px; transition: transform 0.2s;" onmouseover="this.style.transform='scale(1.03)'" onmouseout="this.style.transform='scale(1)'">
                        <div class="card-body text-center d-flex align-items-center justify-content-center" style="min-height: 120px;">
                            <h5 class="fw-bold text-dark mb-0">PAGOS</h5>
                        </div>
                    </div>
                </a>
            </div>

            <div class="col-md-6">
                <div class="card h-100 border-0 shadow" style="background-color: #c0c4c5; border-radius: 12px;">
                    <div class="card-body text-center p-4">
                        <h6 class="fw-bold text-dark mb-3 border-bottom border-secondary pb-2">EXPEDIENTES Y DOCUMENTOS</h6>
                        <div class="d-flex justify-content-around mt-3">
                            <a href="{{ route('admin.revision.inscripciones') }}" class="btn btn-sm btn-dark fw-bold">Inscripciones</a>
                            <a href="{{ route('admin.revision.reinscripciones') }}" class="btn btn-sm btn-dark fw-bold">Reinscripciones</a>
                            <a href="{{ route('admin.revision.egresos') }}" class="btn btn-sm btn-dark fw-bold">Egresos</a>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <a href="{{ route('admin.materias') }}" class="text-decoration-none">
                    <div class="card h-100 border-0 shadow" style="background-color: #d1d5d6; border-radius: 12px; transition: transform 0.2s;" onmouseover="this.style.transform='scale(1.03)'" onmouseout="this.style.transform='scale(1)'">
                        <div class="card-body text-center d-flex align-items-center justify-content-center" style="min-height: 120px;">
                            <h5 class="fw-bold text-dark mb-0">MATERIAS</h5>
                        </div>
                    </div>
                </a>
            </div>

        </div>

        <div class="mt-4">
            <h6 class="fw-bold text-white mb-3" style="border-bottom: 2px dashed rgba(255,255,255,0.3); padding-bottom: 8px; letter-spacing: 1px;">ACTIVIDAD RECIENTE</h6>
            
            <div class="text-white opacity-50 small">
                <i>El sistema está funcionando correctamente. No hay alertas nuevas.</i>
            </div>
        </div>

    </div>
</div>
@endsection

<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Admin\UsuarioController;
use App\Http\Controllers\Admin\AlumnoController;
use App\Http\Controllers\Admin\DocenteController;
use App\Http\Controllers\Admin\MateriaController;
use App\Http\Controllers\Admin\PagoController;
use App\Http\Controllers\Admin\ExpedienteController;

use App\Http\Controllers\Alumno\DocumentoController;
use App\Http\Controllers\Alumno\MateriaAController;
use App\Http\Controllers\Alumno\SolicitudController as AlumnoSolicitudController;

// ==========================================
// RUTAS GENERALES
// ==========================================

Route::get('/', [AuthController::class, 'mostrarRoles'])->name('roles');

Route::get('/login', function () {
    return view('logeo');
})->name('login');

Route::post('/login', [AuthController::class, 'iniciarSesion'])->name('login.post');

Route::get('/logout', [AuthController::class, 'cerrarSesion'])->name('logout');


// ==========================================
// MÓDULO ADMINISTRADOR
// ==========================================

Route::middleware(['auth', 'role:1'])->prefix('admin')->group(function () {

    Route::get('/', function () {
        return view('admin.dashboard');
    })->name('admin.dashboard');

    // Usuarios
    Route::get('/usuarios', [UsuarioController::class, 'index'])->name('admin.usuarios');
    Route::get('/usuarios/crear', [UsuarioController::class, 'create'])->name('admin.usuarios.create');
    Route::post('/usuarios', [UsuarioController::class, 'store'])->name('admin.usuarios.store');
    Route::get('/usuarios/{id}/editar', [UsuarioController::class, 'edit'])->name('admin.usuarios.edit');
    Route::put('/usuarios/{id}', [UsuarioController::class, 'update'])->name('admin.usuarios.update');

    // Alumnos
    Route::get('/alumnos', [AlumnoController::class, 'index'])->name('admin.alumnos');
    Route::get('/alumnos/crear', [AlumnoController::class, 'create'])->name('admin.alumnos.create');
    Route::post('/alumnos', [AlumnoController::class, 'store'])->name('admin.alumnos.store');
    Route::get('/alumnos/{id}/editar', [AlumnoController::class, 'edit'])->name('admin.alumnos.edit');
    Route::put('/alumnos/{id}', [AlumnoController::class, 'update'])->name('admin.alumnos.update');

    // Docentes
    Route::get('/docentes', [DocenteController::class, 'index'])->name('admin.docentes');
    Route::get('/docentes/crear', [DocenteController::class, 'create'])->name('admin.docentes.create');
    Route::post('/docentes', [DocenteController::class, 'store'])->name('admin.docentes.store');
    Route::get('/docentes/{id}/editar', [DocenteController::class, 'edit'])->name('admin.docentes.edit');
    Route::put('/docentes/{id}', [DocenteController::class, 'update'])->name('admin.docentes.update');

    // Materias
    Route::get('/materias', [MateriaController::class, 'index'])->name('admin.materias');

    // Pagos
    Route::get('/pagos', [PagoController::class, 'index'])->name('admin.pagos');
    Route::put('/pagos/{id}/estado', [PagoController::class, 'updateEstado'])->name('admin.pagos.estado');

    // Expedientes
    Route::get('/revision-inscripciones', [ExpedienteController::class, 'inscripciones'])->name('admin.revision.inscripciones');
    Route::get('/revision-reinscripciones', [ExpedienteController::class, 'reinscripciones'])->name('admin.revision.reinscripciones');
    Route::get('/revision-egresos', [ExpedienteController::class, 'egresos'])->name('admin.revision.egresos');
    Route::put('/documento/{id}/estado', [ExpedienteController::class, 'updateEstado'])->name('admin.documentos.estado');

});


// ==========================================
// MÓDULO ALUMNO
// ==========================================

Route::middleware(['auth', 'role:3'])->prefix('alumno')->group(function () {

    // Panel principal
    Route::get('/', function () {
        return view('alumno.dashboard');
    })->name('alumno.dashboard');

    // Materias
    Route::get('/materias', [MateriaAController::class, 'index'])->name('alumno.materias');

    // Solicitudes
    Route::get('/solicitudes', [AlumnoSolicitudController::class, 'index'])->name('alumno.solicitudes');
    Route::get('/solicitudes/nueva', [AlumnoSolicitudController::class, 'create'])->name('alumno.solicitudes.create');
    Route::post('/solicitudes', [AlumnoSolicitudController::class, 'store'])->name('alumno.solicitudes.store');

    // Expediente
    Route::get('/mi-expediente', [DocumentoController::class, 'index'])->name('alumno.documentos');
    Route::post('/mi-expediente/subir', [DocumentoController::class, 'store'])->name('alumno.documentos.store');

});


// ==========================================
// MÓDULO DOCENTE
// ==========================================

Route::middleware(['auth', 'role:2'])->prefix('docente')->group(function () {

    // ======================================
    // PANEL PRINCIPAL DEL DOCENTE
    // ======================================

    Route::get('/', function () {
        return view('docente.dashboard');
    })->name('docente.dashboard');


    // ======================================
    // MIS MATERIAS - CONEXIÓN CON MYSQL
    // ======================================

    Route::get('/materias', function () {

        // Identificar al docente que inició sesión
        $docente = \App\Models\Docente::where(
            'id_usuario',
            auth()->id()
        )->first();

        // Consultar únicamente sus materias asignadas
        if ($docente) {
            $materias = $docente->materias()
                ->orderBy('materias.semestre')
                ->orderBy('materias.nombre')
                ->get();
        } else {
            $materias = collect();
        }

        // Enviar las materias a la vista
        return view('docente.materias', compact('materias'));

    })->name('docente.materias');


    // ======================================
    // MIS GRUPOS - CONEXIÓN CON MYSQL
    // ======================================

    Route::get('/grupos', function () {

        // Identificar al docente que inició sesión
        $docente = \App\Models\Docente::where(
            'id_usuario',
            auth()->id()
        )->first();

        // Consultar sus grupos, materias y periodos escolares
        if ($docente) {
            $asignaciones = $docente->asignacionesGrupos()
                ->with(['materia', 'periodo'])
                ->orderBy('semestre')
                ->orderBy('grupo')
                ->get();
        } else {
            // Si no tiene registro de docente
            $asignaciones = collect();
        }

        // Enviar sus grupos a la vista
        return view('docente.grupos', compact('asignaciones'));

    })->name('docente.grupos');


    // ======================================
    // MI PERFIL - CONEXIÓN CON MYSQL
    // ======================================

    Route::get('/perfil', function () {

        // Buscar los datos del docente que inició sesión
        $docente = \App\Models\Docente::where(
            'id_usuario',
            auth()->id()
        )->first();

        // Enviar los datos a la vista
        return view('docente.perfil', compact('docente'));

    })->name('docente.perfil');

});

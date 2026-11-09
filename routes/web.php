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

Route::get('/', [AuthController::class, 'mostrarRoles'])->name('roles');
Route::get('/login', function () {
    return view('logeo');
})->name('login');
Route::post('/login', [AuthController::class, 'iniciarSesion'])->name('login.post');
Route::get('/logout', [AuthController::class, 'cerrarSesion'])->name('logout');

Route::middleware(['auth', 'role:1'])->prefix('admin')->group(function () {
    Route::get('/', function () {
        return view('admin.dashboard');
    })->name('admin.dashboard');

    Route::get('/usuarios', [UsuarioController::class, 'index'])->name('admin.usuarios');
    Route::get('/usuarios/crear', [UsuarioController::class, 'create'])->name('admin.usuarios.create');
    Route::post('/usuarios', [UsuarioController::class, 'store'])->name('admin.usuarios.store');

    Route::get('/alumnos', [AlumnoController::class, 'index'])->name('admin.alumnos');
    Route::get('/alumnos/crear', [AlumnoController::class, 'create'])->name('admin.alumnos.create');
    Route::post('/alumnos', [AlumnoController::class, 'store'])->name('admin.alumnos.store');

    Route::get('/docentes', [DocenteController::class, 'index'])->name('admin.docentes');
    Route::get('/docentes/crear', [DocenteController::class, 'create'])->name('admin.docentes.create');
    Route::post('/docentes', [DocenteController::class, 'store'])->name('admin.docentes.store');

    Route::get('/materias', [MateriaController::class, 'index'])->name('admin.materias');

    Route::get('/pagos', [PagoController::class, 'index'])->name('admin.pagos');
    Route::put('/pagos/{id}/estado', [PagoController::class, 'updateEstado'])->name('admin.pagos.estado');

    Route::get('/revision-inscripciones', [ExpedienteController::class, 'inscripciones'])->name('admin.revision.inscripciones');
    Route::get('/revision-reinscripciones', [ExpedienteController::class, 'reinscripciones'])->name('admin.revision.reinscripciones');
    Route::get('/revision-egresos', [ExpedienteController::class, 'egresos'])->name('admin.revision.egresos');
    Route::put('/documento/{id}/estado', [ExpedienteController::class, 'updateEstado'])->name('admin.documentos.estado');
});

Route::middleware(['auth', 'role:3'])->prefix('alumno')->group(function () {
    Route::get('/', function () {
        return view('alumno.dashboard');
    })->name('alumno.dashboard');

    Route::get('/materias', [MateriaAController::class, 'index'])->name('alumno.materias');
    Route::get('/solicitudes', [AlumnoSolicitudController::class, 'index'])->name('alumno.solicitudes');
    Route::get('/solicitudes/nueva', [AlumnoSolicitudController::class, 'create'])->name('alumno.solicitudes.create');
    Route::post('/solicitudes', [AlumnoSolicitudController::class, 'store'])->name('alumno.solicitudes.store');

    Route::get('/mi-expediente', [DocumentoController::class, 'index'])->name('alumno.documentos');
    Route::post('/mi-expediente/subir', [DocumentoController::class, 'store'])->name('alumno.documentos.store');
});


Route::middleware(['auth', 'role:2'])->prefix('docente')->group(function () {

    // Panel principal del docente
    Route::get('/', function () {
        return view('docente.dashboard');
    })->name('docente.dashboard');

    // Mis Materias
    Route::get('/materias', function () {
        return view('docente.materias');
    })->name('docente.materias');

    // Mis Grupos
    Route::get('/grupos', function () {
        return view('docente.grupos');
    })->name('docente.grupos');

    // Mi Perfil
    
    // Mi Perfil
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

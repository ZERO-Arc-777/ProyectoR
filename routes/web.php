
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

use App\Http\Controllers\Docente\MateriaDController;
use App\Http\Controllers\Docente\GrupoController;
use App\Http\Controllers\Docente\PerfilController;
use App\Http\Controllers\DocumentoPrivadoController;

/*
|--------------------------------------------------------------------------
| RUTAS PÚBLICAS
|--------------------------------------------------------------------------
*/

// Pantalla principal para seleccionar el rol
Route::get('/', [AuthController::class, 'mostrarRoles'])
    ->name('roles');

// Formulario de inicio de sesión
Route::get('/login', function () {
    return view('logeo');
})->name('login');

// Procesar el inicio de sesión
Route::post('/login', [AuthController::class, 'iniciarSesion'])
    ->name('login.post');

/*
|--------------------------------------------------------------------------
| CIERRE DE SESIÓN SEGURO
|--------------------------------------------------------------------------
|
| Se permite únicamente POST.
| La protección CSRF es aplicada por el middleware web de Laravel.
| Se exige que el usuario esté autenticado.
|
*/

Route::post('/logout', [AuthController::class, 'cerrarSesion'])
    ->middleware('auth')
    ->name('logout.seguro');


/*
|--------------------------------------------------------------------------
| RUTAS DEL ADMINISTRADOR
|--------------------------------------------------------------------------
|
| Solo usuarios autenticados con rol 1.
|
*/

Route::middleware(['auth', 'role:1'])
    ->prefix('admin')
    ->group(function () {

    // Panel principal
    Route::get('/', function () {
        return view('admin.dashboard');
    })->name('admin.dashboard');


    /*
    |--------------------------------------------------------------------------
    | USUARIOS
    |--------------------------------------------------------------------------
    */

    Route::get('/usuarios', [UsuarioController::class, 'index'])
        ->name('admin.usuarios');

    Route::get('/usuarios/crear', [UsuarioController::class, 'create'])
        ->name('admin.usuarios.create');

    Route::post('/usuarios', [UsuarioController::class, 'store'])
        ->name('admin.usuarios.store');

    Route::get('/usuarios/{id}/editar', [UsuarioController::class, 'edit'])
        ->name('admin.usuarios.edit');

    Route::put('/usuarios/{id}', [UsuarioController::class, 'update'])
        ->name('admin.usuarios.update');


    /*
    |--------------------------------------------------------------------------
    | ALUMNOS
    |--------------------------------------------------------------------------
    */

    Route::get('/alumnos', [AlumnoController::class, 'index'])
        ->name('admin.alumnos');

    Route::get('/alumnos/crear', [AlumnoController::class, 'create'])
        ->name('admin.alumnos.create');

    Route::post('/alumnos', [AlumnoController::class, 'store'])
        ->name('admin.alumnos.store');

    Route::get('/alumnos/{id}/editar', [AlumnoController::class, 'edit'])
        ->name('admin.alumnos.edit');

    Route::put('/alumnos/{id}', [AlumnoController::class, 'update'])
        ->name('admin.alumnos.update');


    /*
    |--------------------------------------------------------------------------
    | DOCENTES
    |--------------------------------------------------------------------------
    */

    Route::get('/docentes', [DocenteController::class, 'index'])
        ->name('admin.docentes');

    Route::get('/docentes/crear', [DocenteController::class, 'create'])
        ->name('admin.docentes.create');

    Route::post('/docentes', [DocenteController::class, 'store'])
        ->name('admin.docentes.store');

    Route::get('/docentes/{id}/editar', [DocenteController::class, 'edit'])
        ->name('admin.docentes.edit');

    Route::put('/docentes/{id}', [DocenteController::class, 'update'])
        ->name('admin.docentes.update');


    /*
    |--------------------------------------------------------------------------
    | MATERIAS Y PERIODO ACADÉMICO
    |--------------------------------------------------------------------------
    */

    Route::get('/materias', [MateriaController::class, 'index'])
        ->name('admin.materias');

    Route::get('/materias/create', [MateriaController::class, 'create'])
        ->name('admin.materias.create');

    Route::post('/materias', [MateriaController::class, 'store'])
        ->name('admin.materias.store');

    Route::get('/materias/{id}/edit', [MateriaController::class, 'edit'])
        ->name('admin.materias.edit');

    Route::put('/materias/{id}', [MateriaController::class, 'update'])
        ->name('admin.materias.update');

    Route::post('/materias/periodo', [MateriaController::class, 'actualizarPeriodo'])
        ->name('admin.materias.periodo');


    /*
    |--------------------------------------------------------------------------
    | PAGOS
    |--------------------------------------------------------------------------
    */

    Route::get('/pagos', [PagoController::class, 'index'])
        ->name('admin.pagos');

    Route::put('/pagos/{id_solicitud}/estado', [PagoController::class, 'updateEstado'])
        ->name('admin.pagos.updateEstado');


    /*
    |--------------------------------------------------------------------------
    | EXPEDIENTES Y DOCUMENTOS
    |--------------------------------------------------------------------------
    */

    Route::get('/revision-inscripciones', [ExpedienteController::class, 'inscripciones'])
        ->name('admin.revision.inscripciones');

    Route::get('/revision-reinscripciones', [ExpedienteController::class, 'reinscripciones'])
        ->name('admin.revision.reinscripciones');

    Route::get('/revision-egresos', [ExpedienteController::class, 'egresos'])
        ->name('admin.revision.egresos');

    Route::put('/documento/{id}/estado', [ExpedienteController::class, 'updateEstado'])
        ->name('admin.documentos.estado');

});


/*
|--------------------------------------------------------------------------
| RUTAS DEL ALUMNO
|--------------------------------------------------------------------------
|
| Solo usuarios autenticados con rol 3.
|
*/

Route::middleware(['auth', 'role:3'])
    ->prefix('alumno')
    ->group(function () {

    // Panel principal
    Route::get('/', function () {
        return view('alumno.dashboard');
    })->name('alumno.dashboard');

    // Materias
    Route::get('/materias', [MateriaAController::class, 'index'])
        ->name('alumno.materias');

    // Solicitudes
    Route::get('/solicitudes', [AlumnoSolicitudController::class, 'index'])
        ->name('alumno.solicitudes');

    Route::get('/solicitudes/nueva', [AlumnoSolicitudController::class, 'create'])
        ->name('alumno.solicitudes.create');

    Route::post('/solicitudes', [AlumnoSolicitudController::class, 'store'])
        ->name('alumno.solicitudes.store');

    // Expediente personal
    Route::get('/mi-expediente', [DocumentoController::class, 'index'])
        ->name('alumno.documentos');

    Route::post('/mi-expediente/subir', [DocumentoController::class, 'store'])
        ->name('alumno.documentos.store');

});

/*
|--------------------------------------------------------------------------
| VISUALIZACIÓN SEGURA DE DOCUMENTOS PDF
|--------------------------------------------------------------------------
|
| Requiere una sesión autenticada.
| El controlador comprueba el rol y la propiedad del documento.
|
*/

Route::get(
    '/documentos/{id_documento}/ver',
    [DocumentoPrivadoController::class, 'ver']
)
    ->middleware('auth')
    ->whereNumber('id_documento')
    ->name('documentos.ver');

/*
|--------------------------------------------------------------------------
| RUTAS DEL DOCENTE
|--------------------------------------------------------------------------
|
| Solo usuarios autenticados con rol 2.
|
*/

Route::middleware(['auth', 'role:2'])
    ->prefix('docente')
    ->group(function () {

    // Panel principal
    Route::get('/', function () {
        return view('docente.dashboard');
    })->name('docente.dashboard');

    // Materias asignadas
    Route::get('/materias', [MateriaDController::class, 'index'])
        ->name('docente.materias');

    // Grupos asignados
    Route::get('/grupos', [GrupoController::class, 'index'])
        ->name('docente.grupos');

    // Perfil del docente
    Route::get('/perfil', [PerfilController::class, 'index'])
        ->name('docente.perfil');

});


/*
|--------------------------------------------------------------------------
| DESCARGA DE LISTAS DE ASISTENCIA DEL DOCENTE
|--------------------------------------------------------------------------
|
| Requiere autenticación y rol docente.
| El identificador de asignación debe ser numérico.
|
*/

Route::get(
    '/docente/lista-asistencia/{asignacion}',
    [\App\Http\Controllers\Docente\ListaAsistenciaController::class, 'descargar']
)
    ->middleware(['auth', 'role:2'])
    ->whereNumber('asignacion')
    ->name('docente.lista-asistencia');

<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;

// ==========================================
// CONTROLADORES DE ADMINISTRACIÓN
// ==========================================

use App\Http\Controllers\Admin\UsuarioController;
use App\Http\Controllers\Admin\AlumnoController;
use App\Http\Controllers\Admin\DocenteController;
use App\Http\Controllers\Admin\MateriaController;
use App\Http\Controllers\Admin\PagoController;
use App\Http\Controllers\Admin\ExpedienteController;
use App\Http\Controllers\Admin\PeriodoController;

// ==========================================
// CONTROLADORES DE ALUMNOS
// ==========================================

use App\Http\Controllers\Alumno\DocumentoController;
use App\Http\Controllers\Alumno\MateriaAController;
use App\Http\Controllers\Alumno\SolicitudController as AlumnoSolicitudController;

// ==========================================
// CONTROLADORES DE DOCENTES
// ==========================================

use App\Http\Controllers\Docente\MateriaDController;
use App\Http\Controllers\Docente\GrupoController;
use App\Http\Controllers\Docente\PerfilController;
use App\Http\Controllers\Docente\ListaAsistenciaController;

// ==========================================
// CONTROLADOR DE DOCUMENTOS PRIVADOS
// ==========================================

use App\Http\Controllers\DocumentoPrivadoController;


/*
|--------------------------------------------------------------------------
| RUTAS PÚBLICAS
|--------------------------------------------------------------------------
*/

// Pantalla principal para seleccionar el rol.
Route::get('/', [AuthController::class, 'mostrarRoles'])
    ->name('roles');

// Formulario de inicio de sesión.
Route::get('/login', [AuthController::class, 'mostrarLogin'])
    ->name('login');

// Procesar el inicio de sesión.
Route::post('/login', [AuthController::class, 'iniciarSesion'])
    ->name('login.post');


/*
|--------------------------------------------------------------------------
| CIERRE DE SESIÓN SEGURO
|--------------------------------------------------------------------------
*/

Route::post('/logout', [AuthController::class, 'cerrarSesion'])
    ->middleware('auth')
    ->name('logout.seguro');


/*
|--------------------------------------------------------------------------
| RUTAS DEL ADMINISTRADOR
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'role:1'])
    ->prefix('admin')
    ->group(function () {

        // PANEL PRINCIPAL
        Route::get('/', function () {
            return view('admin.dashboard');
        })->name('admin.dashboard');

        // USUARIOS
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

        // ALUMNOS
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

        // DOCENTES
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

        Route::post('/docentes/asignaciones/{id_asignacion}/sustituir', [DocenteController::class, 'sustituirAsignacion'])
            ->name('admin.docentes.asignaciones.sustituir');

        // PERIODO ESCOLAR
        Route::get('/periodo-escolar', [PeriodoController::class, 'index'])
            ->name('admin.periodo.index');

        Route::post('/periodo-escolar/abrir', [PeriodoController::class, 'abrirCiclo'])
            ->name('admin.periodo.abrir');

        Route::post('/periodo-escolar/corregir', [PeriodoController::class, 'corregirCiclo'])
            ->name('admin.periodo.corregir');

        Route::post('/periodo-escolar/cerrar', [PeriodoController::class, 'cerrarCiclo'])
            ->name('admin.periodo.cerrar');

        // MATERIAS
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

        // PAGOS
        Route::get('/pagos', [PagoController::class, 'index'])
            ->name('admin.pagos');

        Route::put('/pagos/{id_solicitud}/estado', [PagoController::class, 'updateEstado'])
            ->whereNumber('id_solicitud')
            ->name('admin.pagos.updateEstado');

        // CATÁLOGO DE PAGOS (SERVICIOS)
        Route::get('/pagos/catalogo', [PagoController::class, 'catalogo'])
            ->name('admin.pagos.catalogo');

        Route::post('/pagos/catalogo', [PagoController::class, 'storeServicio'])
            ->name('admin.pagos.catalogo.store');

        Route::put('/pagos/catalogo/{id_servicio}', [PagoController::class, 'updateServicio'])
            ->whereNumber('id_servicio')
            ->name('admin.pagos.catalogo.update');

        Route::delete('/pagos/catalogo/{id_servicio}', [PagoController::class, 'destroyServicio'])
            ->whereNumber('id_servicio')
            ->name('admin.pagos.catalogo.destroy');

        // EXPEDIENTES Y DOCUMENTOS
        Route::get(
            '/revision-inscripciones',
            [ExpedienteController::class, 'inscripciones']
        )->name('admin.revision.inscripciones');

        Route::get(
            '/revision-reinscripciones',
            [ExpedienteController::class, 'reinscripciones']
        )->name('admin.revision.reinscripciones');

        Route::get(
            '/revision-egresos',
            [ExpedienteController::class, 'egresos']
        )->name('admin.revision.egresos');

        Route::put(
            '/documento/{id}/estado',
            [ExpedienteController::class, 'updateEstado']
        )->name('admin.documentos.estado');
    });


/*
|--------------------------------------------------------------------------
| RUTAS DEL ALUMNO
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'role:3'])
    ->prefix('alumno')
    ->group(function () {

        // PANEL PRINCIPAL
        Route::get('/', function () {
            return view('alumno.dashboard');
        })->name('alumno.dashboard');

        // MATERIAS, KÁRDEX, DETALLE POR MATERIA Y BOLETAS PDF
        Route::get('/materias', [MateriaAController::class, 'index'])
            ->name('alumno.materias');

        Route::get('/materias/boleta-pdf', [MateriaAController::class, 'descargarBoleta'])
            ->name('alumno.materias.boleta');

        Route::get('/materias/{id_materia}', [MateriaAController::class, 'detalle'])
            ->whereNumber('id_materia')
            ->name('alumno.materias.detalle');

        // SOLICITUDES
        Route::get(
            '/solicitudes',
            [AlumnoSolicitudController::class, 'index']
        )->name('alumno.solicitudes');

        Route::get(
            '/solicitudes/nueva',
            [AlumnoSolicitudController::class, 'create']
        )->name('alumno.solicitudes.create');

        Route::post(
            '/solicitudes',
            [AlumnoSolicitudController::class, 'store']
        )->name('alumno.solicitudes.store');

        // EXPEDIENTE PERSONAL
        Route::get(
            '/mi-expediente',
            [DocumentoController::class, 'index']
        )->name('alumno.documentos');

        Route::post(
            '/mi-expediente/subir',
            [DocumentoController::class, 'store']
        )->name('alumno.documentos.store');
    });


/*
|--------------------------------------------------------------------------
| VISUALIZACIÓN SEGURA DE DOCUMENTOS PDF
|--------------------------------------------------------------------------
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
*/

Route::middleware(['auth', 'role:2'])
    ->prefix('docente')
    ->group(function () {

        // PANEL PRINCIPAL
        Route::get('/', function () {
            return view('docente.dashboard');
        })->name('docente.dashboard');

        // MATERIAS Y CALIFICACIONES
        Route::get(
            '/materias',
            [MateriaDController::class, 'index']
        )->name('docente.materias');

        Route::get(
            '/materias/{asignacion}/calificar',
            [MateriaDController::class, 'calificar']
        )->name('docente.materias.calificar');

        Route::post(
            '/materias/{asignacion}/calificar',
            [MateriaDController::class, 'guardarCalificaciones']
        )->name('docente.materias.guardar');

        // GRUPOS ASIGNADOS
        Route::get(
            '/grupos',
            [GrupoController::class, 'index']
        )->name('docente.grupos');

        Route::get(
            '/grupos/{asignacion}/alumnos',
            [GrupoController::class, 'verAlumnos']
        )->name('docente.grupos.listas');

        // PERFIL DEL DOCENTE
        Route::get(
            '/perfil',
            [PerfilController::class, 'index']
        )->name('docente.perfil');

        // LISTAS DE ASISTENCIA
        Route::get(
            '/lista-asistencia/{asignacion}',
            [ListaAsistenciaController::class, 'descargar']
        )
            ->whereNumber('asignacion')
            ->name('docente.lista-asistencia');
    }
);

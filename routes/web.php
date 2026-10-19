<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Admin\UsuarioController;
use App\Http\Controllers\Admin\AlumnoController;
use App\Http\Controllers\Admin\DocenteController;
use App\Http\Controllers\Admin\AspiranteController;
use App\Http\Controllers\Admin\InscripcionController;
use App\Http\Controllers\Admin\ReinscripcionController;
use App\Http\Controllers\Admin\MateriaController;
use App\Http\Controllers\Admin\PeriodoController;
use App\Http\Controllers\Admin\HistorialController;
use App\Http\Controllers\Admin\PagoController;
use App\Http\Controllers\Admin\ConstanciaController;
use App\Http\Controllers\Admin\ExpedienteController;
use App\Http\Controllers\Admin\BajaController;
use App\Http\Controllers\Admin\EgresadoController;

use App\Http\Controllers\Alumno\MateriaAController;

Route::get('/', [AuthController::class, 'mostrarRoles'])->name('roles');
Route::get('/login', function () {
    return view('logeo');
})->name('login');
Route::post('/login', [AuthController::class, 'iniciarSesion'])->name('login.post');
Route::get('/logout', [AuthController::class, 'cerrarSesion'])->name('logout');

Route::prefix('admin')->group(function () {
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

    Route::get('/aspirantes', [AspiranteController::class, 'index'])->name('admin.aspirantes');
    Route::get('/inscripciones', [InscripcionController::class, 'index'])->name('admin.inscripciones');
    Route::get('/reinscripciones', [ReinscripcionController::class, 'index'])->name('admin.reinscripciones');
    Route::get('/materias', [MateriaController::class, 'index'])->name('admin.materias');
    Route::get('/periodos', [PeriodoController::class, 'index'])->name('admin.periodo');
    Route::get('/historial', [HistorialController::class, 'index'])->name('admin.historial');
    Route::get('/pagos', [PagoController::class, 'index'])->name('admin.pagos');
    Route::get('/constancias', [ConstanciaController::class, 'index'])->name('admin.constancias');
    Route::get('/expedientes', [ExpedienteController::class, 'index'])->name('admin.expedientes');
    Route::get('/bajas', [BajaController::class, 'index'])->name('admin.bajas');
    Route::get('/egresados', [EgresadoController::class, 'index'])->name('admin.egresados');
});

Route::prefix('alumno')->group(function () {
    Route::get('/', function () {
        return view('alumno.dashboard');
    })->name('alumno.dashboard');

    Route::get('/materias', [MateriaAController::class, 'index'])->name('alumno.materias');
});

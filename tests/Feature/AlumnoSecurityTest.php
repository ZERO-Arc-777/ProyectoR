<?php

namespace Tests\Feature;

use App\Models\User;
use Tests\TestCase;

class AlumnoSecurityTest extends TestCase
{
    // Un visitante no puede registrar alumnos.
    public function test_invitado_no_puede_registrar_alumnos(): void
    {
        $this->assertSame('sqlite', config('database.default'));
        $this->assertSame(
            ':memory:',
            config('database.connections.sqlite.database')
        );

        $response = $this->post('/admin/alumnos', [
            'matricula' => 'PRUEBA-001',
            'nombres' => 'Alumno Prueba',
        ]);

        $response->assertRedirectToRoute('login');
    }

    // Un alumno no puede registrar expedientes.
    public function test_alumno_no_puede_registrar_alumnos(): void
    {
        $alumno = new User();

        $alumno->id_usuario = 999991;
        $alumno->id_rol = 3;
        $alumno->estado = 'ACTIVO';

        $this->actingAs($alumno);

        $response = $this->post('/admin/alumnos', [
            'matricula' => 'PRUEBA-002',
            'nombres' => 'Alumno Prueba',
        ]);

        $response->assertStatus(403);
    }

    // Un docente no puede modificar expedientes de alumnos.
    public function test_docente_no_puede_modificar_alumnos(): void
    {
        $docente = new User();

        $docente->id_usuario = 999992;
        $docente->id_rol = 2;
        $docente->estado = 'ACTIVO';

        $this->actingAs($docente);

        $response = $this->put('/admin/alumnos/PRUEBA-001', [
            'nombres' => 'Modificación no autorizada',
        ]);

        $response->assertStatus(403);
    }

    // Un administrador inactivo no puede modificar alumnos.
    public function test_administrador_inactivo_no_puede_modificar_alumnos(): void
    {
        $administrador = new User();

        $administrador->id_usuario = 999993;
        $administrador->id_rol = 1;
        $administrador->estado = 'INACTIVO';

        $this->actingAs($administrador);

        $response = $this->put('/admin/alumnos/PRUEBA-001', [
            'nombres' => 'Modificación no autorizada',
        ]);

        $response->assertStatus(403);
    }

    // Un alumno no puede vincularse a una cuenta administrativa.
    public function test_no_permite_vincular_cuenta_administrativa_a_alumno(): void
    {
        // Confirmar que utilizamos SQLite en memoria.
        $this->assertSame('sqlite', config('database.default'));
        $this->assertSame(
            ':memory:',
            config('database.connections.sqlite.database')
        );

        // Crear las tablas necesarias únicamente en SQLite.
        \Illuminate\Support\Facades\Schema::create('usuarios', function ($table) {
            $table->id('id_usuario');
            $table->unsignedBigInteger('id_rol');
            $table->string('nombre', 100);
            $table->string('correo', 100);
            $table->string('contrasena', 255);
            $table->string('estado')->default('ACTIVO');
        });

        \Illuminate\Support\Facades\Schema::create('alumnos', function ($table) {
            $table->id('id_alumno');
            $table->unsignedBigInteger('id_usuario')->nullable()->unique();
            $table->string('matricula', 30)->unique();
            $table->string('nombres', 100);
            $table->string('apellido_paterno', 100);
            $table->string('apellido_materno', 100)->nullable();
            $table->string('curp', 18)->unique();
            $table->unsignedTinyInteger('semestre');
            $table->char('grupo', 1);
            $table->string('estado')->default('ACTIVO');
            $table->timestamps();
        });

        // Crear administrador ficticio en la base temporal.
        $administrador = User::create([
            'nombre' => 'Administrador Prueba',
            'correo' => 'admin.alumnos@example.com',
            'contrasena' => bcrypt('ClaveTemporalSegura123!'),
            'id_rol' => 1,
            'estado' => 'ACTIVO',
        ]);

        $this->actingAs($administrador);

        // Intentar vincular la cuenta administrativa
        // con un expediente de alumno.
        $response = $this->post('/admin/alumnos', [
            'id_usuario' => $administrador->id_usuario,
            'matricula' => 'MAT-PRUEBA-001',
            'nombres' => 'Alumno',
            'apellido_paterno' => 'Prueba',
            'curp' => 'ABCD123456HXYZAB01',
            'semestre' => 1,
            'grupo' => 'A',
        ]);

        // Laravel debe rechazar la cuenta por su rol.
        $response->assertSessionHasErrors('id_usuario');

        // No debe crearse ningún expediente.
        $this->assertDatabaseCount('alumnos', 0);
    }

    // Verificar que Laravel rechace una CURP incorrecta.
    public function test_rechaza_curp_con_formato_incorrecto(): void
    {
        // Comprobar que utilizamos SQLite en memoria.
        $this->assertSame('sqlite', config('database.default'));
        $this->assertSame(
            ':memory:',
            config('database.connections.sqlite.database')
        );

        // Crear tablas temporales.
        \Illuminate\Support\Facades\Schema::create('usuarios', function ($table) {
            $table->id('id_usuario');
            $table->unsignedBigInteger('id_rol');
            $table->string('nombre', 100);
            $table->string('correo', 100);
            $table->string('contrasena', 255);
            $table->string('estado')->default('ACTIVO');
        });

        \Illuminate\Support\Facades\Schema::create('alumnos', function ($table) {
            $table->id('id_alumno');
            $table->unsignedBigInteger('id_usuario')->nullable()->unique();
            $table->string('matricula', 30)->unique();
            $table->string('nombres', 100);
            $table->string('apellido_paterno', 100);
            $table->string('apellido_materno', 100)->nullable();
            $table->string('curp', 18)->unique();
            $table->unsignedTinyInteger('semestre');
            $table->char('grupo', 1);
            $table->string('estado')->default('ACTIVO');
            $table->timestamps();
        });

        // Crear cuentas ficticias.
        $administrador = User::create([
            'nombre' => 'Administrador Prueba',
            'correo' => 'admin.curp@example.com',
            'contrasena' => bcrypt('ClaveTemporalSegura123!'),
            'id_rol' => 1,
            'estado' => 'ACTIVO',
        ]);

        $cuentaAlumno = User::create([
            'nombre' => 'Alumno Prueba',
            'correo' => 'alumno.curp@example.com',
            'contrasena' => bcrypt('ClaveTemporalSegura456!'),
            'id_rol' => 3,
            'estado' => 'ACTIVO',
        ]);

        $this->actingAs($administrador);

        // Enviar una CURP incorrecta.
        $response = $this->post('/admin/alumnos', [
            'id_usuario' => $cuentaAlumno->id_usuario,
            'matricula' => 'MAT-PRUEBA-002',
            'nombres' => 'Alumno',
            'apellido_paterno' => 'Prueba',
            'curp' => 'ABC123',
            'semestre' => 1,
            'grupo' => 'A',
        ]);

        // El registro debe rechazarse.
        $response->assertSessionHasErrors('curp');

        // No debe crearse ningún expediente.
        $this->assertDatabaseCount('alumnos', 0);
    }

    // Impedir registrar matrículas duplicadas.
    public function test_rechaza_matriculas_duplicadas(): void
    {
        // Verificar el aislamiento de la base de datos.
        $this->assertSame('sqlite', config('database.default'));
        $this->assertSame(
            ':memory:',
            config('database.connections.sqlite.database')
        );

        // Crear tablas únicamente en SQLite temporal.
        \Illuminate\Support\Facades\Schema::create('usuarios', function ($table) {
            $table->id('id_usuario');
            $table->unsignedBigInteger('id_rol');
            $table->string('nombre', 100);
            $table->string('correo', 100);
            $table->string('contrasena', 255);
            $table->string('estado')->default('ACTIVO');
        });

        \Illuminate\Support\Facades\Schema::create('alumnos', function ($table) {
            $table->id('id_alumno');
            $table->unsignedBigInteger('id_usuario')->nullable()->unique();
            $table->string('matricula', 30)->unique();
            $table->string('nombres', 100);
            $table->string('apellido_paterno', 100);
            $table->string('apellido_materno', 100)->nullable();
            $table->string('curp', 18)->unique();
            $table->unsignedTinyInteger('semestre');
            $table->char('grupo', 1);
            $table->string('estado')->default('ACTIVO');
            $table->timestamps();
        });

        // Crear un administrador y dos cuentas de alumnos.
        $administrador = User::create([
            'nombre' => 'Administrador Prueba',
            'correo' => 'admin.matricula@example.com',
            'contrasena' => bcrypt('ClaveTemporalSegura123!'),
            'id_rol' => 1,
            'estado' => 'ACTIVO',
        ]);

        $cuentaAlumno1 = User::create([
            'nombre' => 'Alumno Uno',
            'correo' => 'alumno1.matricula@example.com',
            'contrasena' => bcrypt('ClaveTemporalSegura456!'),
            'id_rol' => 3,
            'estado' => 'ACTIVO',
        ]);

        $cuentaAlumno2 = User::create([
            'nombre' => 'Alumno Dos',
            'correo' => 'alumno2.matricula@example.com',
            'contrasena' => bcrypt('ClaveTemporalSegura789!'),
            'id_rol' => 3,
            'estado' => 'ACTIVO',
        ]);

        // Registrar el primer expediente en SQLite.
        \App\Models\Alumno::create([
            'id_usuario' => $cuentaAlumno1->id_usuario,
            'matricula' => 'MAT-PRUEBA-001',
            'nombres' => 'Alumno',
            'apellido_paterno' => 'Uno',
            'curp' => 'ABCD123456HXYZAB01',
            'semestre' => 1,
            'grupo' => 'A',
            'estado' => 'ACTIVO',
        ]);

        $this->actingAs($administrador);

        // Intentar registrar otro alumno con la misma matrícula.
        $response = $this->post('/admin/alumnos', [
            'id_usuario' => $cuentaAlumno2->id_usuario,
            'matricula' => 'MAT-PRUEBA-001',
            'nombres' => 'Alumno',
            'apellido_paterno' => 'Dos',
            'curp' => 'EFGH123456MXYZAB01',
            'semestre' => 2,
            'grupo' => 'B',
        ]);

        // Laravel debe rechazar la matrícula repetida.
        $response->assertSessionHasErrors('matricula');

        // Solo debe existir el expediente original.
        $this->assertDatabaseCount('alumnos', 1);
    }

    // Impedir vincular dos expedientes a una misma cuenta.
    public function test_rechaza_cuenta_duplicada_al_editar_alumno(): void
    {
        // Verificar que se utiliza SQLite temporal.
        $this->assertSame('sqlite', config('database.default'));
        $this->assertSame(':memory:', config('database.connections.sqlite.database'));

        // Crear tablas temporales.
        \Illuminate\Support\Facades\Schema::create('usuarios', function ($table) {
            $table->id('id_usuario');
            $table->unsignedBigInteger('id_rol');
            $table->string('nombre', 100);
            $table->string('correo', 100);
            $table->string('contrasena', 255);
            $table->string('estado')->default('ACTIVO');
        });

        \Illuminate\Support\Facades\Schema::create('alumnos', function ($table) {
            $table->id('id_alumno');
            $table->unsignedBigInteger('id_usuario')->nullable()->unique();
            $table->string('matricula', 30)->unique();
            $table->string('nombres', 100);
            $table->string('apellido_paterno', 100);
            $table->string('apellido_materno', 100)->nullable();
            $table->string('curp', 18)->unique();
            $table->unsignedTinyInteger('semestre');
            $table->char('grupo', 1);
            $table->string('estado')->default('ACTIVO');
            $table->timestamps();
        });

        // Crear cuentas ficticias.
        $administrador = User::create([
            'nombre' => 'Administrador Prueba',
            'correo' => 'admin.edicion@example.com',
            'contrasena' => bcrypt('ClaveTemporalSegura123!'),
            'id_rol' => 1,
            'estado' => 'ACTIVO',
        ]);

        $cuentaAlumno1 = User::create([
            'nombre' => 'Alumno Uno',
            'correo' => 'alumno1.edicion@example.com',
            'contrasena' => bcrypt('ClaveTemporalSegura456!'),
            'id_rol' => 3,
            'estado' => 'ACTIVO',
        ]);

        $cuentaAlumno2 = User::create([
            'nombre' => 'Alumno Dos',
            'correo' => 'alumno2.edicion@example.com',
            'contrasena' => bcrypt('ClaveTemporalSegura789!'),
            'id_rol' => 3,
            'estado' => 'ACTIVO',
        ]);

        // Registrar dos expedientes ficticios.
        \App\Models\Alumno::create([
            'id_usuario' => $cuentaAlumno1->id_usuario,
            'matricula' => 'MAT-EDIT-001',
            'nombres' => 'Alumno',
            'apellido_paterno' => 'Uno',
            'curp' => 'ABCD123456HXYZAB01',
            'semestre' => 1,
            'grupo' => 'A',
            'estado' => 'ACTIVO',
        ]);

        \App\Models\Alumno::create([
            'id_usuario' => $cuentaAlumno2->id_usuario,
            'matricula' => 'MAT-EDIT-002',
            'nombres' => 'Alumno',
            'apellido_paterno' => 'Dos',
            'curp' => 'EFGH123456MXYZAB01',
            'semestre' => 2,
            'grupo' => 'B',
            'estado' => 'ACTIVO',
        ]);

        $this->actingAs($administrador);

        // Intentar asignar al segundo alumno la cuenta del primero.
        $response = $this->put('/admin/alumnos/MAT-EDIT-002', [
            'id_usuario' => $cuentaAlumno1->id_usuario,
            'nombres' => 'Alumno',
            'apellido_paterno' => 'Dos',
            'curp' => 'EFGH123456MXYZAB01',
            'semestre' => 2,
            'grupo' => 'B',
        ]);

        // Laravel debe rechazar la cuenta duplicada.
        $response->assertSessionHasErrors('id_usuario');

        // El segundo alumno debe conservar su cuenta original.
        $this->assertDatabaseHas('alumnos', [
            'matricula' => 'MAT-EDIT-002',
            'id_usuario' => $cuentaAlumno2->id_usuario,
        ]);
    }

    // Impedir asignar una CURP que ya pertenece a otro alumno.
    public function test_rechaza_curp_duplicada_al_editar(): void
    {
        // Comprobar que utilizamos SQLite en memoria.
        $this->assertSame('sqlite', config('database.default'));
        $this->assertSame(
            ':memory:',
            config('database.connections.sqlite.database')
        );

        // Crear tablas temporales.
        \Illuminate\Support\Facades\Schema::create('usuarios', function ($table) {
            $table->id('id_usuario');
            $table->unsignedBigInteger('id_rol');
            $table->string('nombre', 100);
            $table->string('correo', 100);
            $table->string('contrasena', 255);
            $table->string('estado')->default('ACTIVO');
        });

        \Illuminate\Support\Facades\Schema::create('alumnos', function ($table) {
            $table->id('id_alumno');
            $table->unsignedBigInteger('id_usuario')->nullable()->unique();
            $table->string('matricula', 30)->unique();
            $table->string('nombres', 100);
            $table->string('apellido_paterno', 100);
            $table->string('apellido_materno', 100)->nullable();
            $table->string('curp', 18)->unique();
            $table->unsignedTinyInteger('semestre');
            $table->char('grupo', 1);
            $table->string('estado')->default('ACTIVO');
            $table->timestamps();
        });

        // Crear un administrador y dos cuentas de alumnos.
        $administrador = User::create([
            'nombre' => 'Administrador Prueba',
            'correo' => 'admin.curpedit@example.com',
            'contrasena' => bcrypt('ClaveTemporalSegura123!'),
            'id_rol' => 1,
            'estado' => 'ACTIVO',
        ]);

        $cuentaAlumno1 = User::create([
            'nombre' => 'Alumno Uno',
            'correo' => 'alumno1.curpedit@example.com',
            'contrasena' => bcrypt('ClaveTemporalSegura456!'),
            'id_rol' => 3,
            'estado' => 'ACTIVO',
        ]);

        $cuentaAlumno2 = User::create([
            'nombre' => 'Alumno Dos',
            'correo' => 'alumno2.curpedit@example.com',
            'contrasena' => bcrypt('ClaveTemporalSegura789!'),
            'id_rol' => 3,
            'estado' => 'ACTIVO',
        ]);

        // Crear dos expedientes ficticios.
        \App\Models\Alumno::create([
            'id_usuario' => $cuentaAlumno1->id_usuario,
            'matricula' => 'MAT-CURP-001',
            'nombres' => 'Alumno',
            'apellido_paterno' => 'Uno',
            'curp' => 'ABCD123456HXYZAB01',
            'semestre' => 1,
            'grupo' => 'A',
            'estado' => 'ACTIVO',
        ]);

        \App\Models\Alumno::create([
            'id_usuario' => $cuentaAlumno2->id_usuario,
            'matricula' => 'MAT-CURP-002',
            'nombres' => 'Alumno',
            'apellido_paterno' => 'Dos',
            'curp' => 'EFGH123456MXYZAB01',
            'semestre' => 2,
            'grupo' => 'B',
            'estado' => 'ACTIVO',
        ]);

        $this->actingAs($administrador);

        // Intentar asignar al segundo alumno la CURP del primero.
        $response = $this->put('/admin/alumnos/MAT-CURP-002', [
            'id_usuario' => $cuentaAlumno2->id_usuario,
            'nombres' => 'Alumno',
            'apellido_paterno' => 'Dos',
            'curp' => 'ABCD123456HXYZAB01',
            'semestre' => 2,
            'grupo' => 'B',
        ]);

        // La CURP duplicada debe rechazarse.
        $response->assertSessionHasErrors('curp');

        // El segundo expediente debe conservar su CURP original.
        $this->assertDatabaseHas('alumnos', [
            'matricula' => 'MAT-CURP-002',
            'curp' => 'EFGH123456MXYZAB01',
        ]);
    }

    // Rechazar semestres fuera del rango permitido.
    public function test_rechaza_semestre_invalido(): void
    {
        // Verificar que utilizamos SQLite en memoria.
        $this->assertSame('sqlite', config('database.default'));
        $this->assertSame(
            ':memory:',
            config('database.connections.sqlite.database')
        );

        // Crear tablas temporales.
        \Illuminate\Support\Facades\Schema::create('usuarios', function ($table) {
            $table->id('id_usuario');
            $table->unsignedBigInteger('id_rol');
            $table->string('nombre', 100);
            $table->string('correo', 100);
            $table->string('contrasena', 255);
            $table->string('estado')->default('ACTIVO');
        });

        \Illuminate\Support\Facades\Schema::create('alumnos', function ($table) {
            $table->id('id_alumno');
            $table->unsignedBigInteger('id_usuario')->nullable()->unique();
            $table->string('matricula', 30)->unique();
            $table->string('nombres', 100);
            $table->string('apellido_paterno', 100);
            $table->string('apellido_materno', 100)->nullable();
            $table->string('curp', 18)->unique();
            $table->unsignedTinyInteger('semestre');
            $table->char('grupo', 1);
            $table->string('estado')->default('ACTIVO');
            $table->timestamps();
        });

        // Crear usuarios ficticios.
        $administrador = User::create([
            'nombre' => 'Administrador Prueba',
            'correo' => 'admin.semestre@example.com',
            'contrasena' => bcrypt('ClaveTemporalSegura123!'),
            'id_rol' => 1,
            'estado' => 'ACTIVO',
        ]);

        $cuentaAlumno = User::create([
            'nombre' => 'Alumno Prueba',
            'correo' => 'alumno.semestre@example.com',
            'contrasena' => bcrypt('ClaveTemporalSegura456!'),
            'id_rol' => 3,
            'estado' => 'ACTIVO',
        ]);

        $this->actingAs($administrador);

        // Intentar registrar un alumno en séptimo semestre.
        $response = $this->post('/admin/alumnos', [
            'id_usuario' => $cuentaAlumno->id_usuario,
            'matricula' => 'MAT-SEM-001',
            'nombres' => 'Alumno',
            'apellido_paterno' => 'Prueba',
            'curp' => 'ABCD123456HXYZAB01',
            'semestre' => 7,
            'grupo' => 'A',
        ]);

        // Laravel debe rechazar el semestre incorrecto.
        $response->assertSessionHasErrors('semestre');

        // No debe registrarse ningún expediente.
        $this->assertDatabaseCount('alumnos', 0);
    }

    // Verificar que el sistema acepte los seis semestres.
    public function test_permite_registrar_alumnos_en_los_seis_semestres(): void
    {
        // Comprobar el aislamiento de la base de datos.
        $this->assertSame('sqlite', config('database.default'));
        $this->assertSame(
            ':memory:',
            config('database.connections.sqlite.database')
        );

        // Crear tablas temporales.
        \Illuminate\Support\Facades\Schema::create('usuarios', function ($table) {
            $table->id('id_usuario');
            $table->unsignedBigInteger('id_rol');
            $table->string('nombre', 100);
            $table->string('correo', 100);
            $table->string('contrasena', 255);
            $table->string('estado')->default('ACTIVO');
        });

        \Illuminate\Support\Facades\Schema::create('alumnos', function ($table) {
            $table->id('id_alumno');
            $table->unsignedBigInteger('id_usuario')->nullable()->unique();
            $table->string('matricula', 30)->unique();
            $table->string('nombres', 100);
            $table->string('apellido_paterno', 100);
            $table->string('apellido_materno', 100)->nullable();
            $table->string('curp', 18)->unique();
            $table->unsignedTinyInteger('semestre');
            $table->char('grupo', 1);
            $table->string('estado')->default('ACTIVO');
            $table->timestamps();
        });

        // Crear administrador temporal.
        $administrador = User::create([
            'nombre' => 'Administrador Prueba',
            'correo' => 'admin.semestres@example.com',
            'contrasena' => bcrypt('ClaveTemporalSegura123!'),
            'id_rol' => 1,
            'estado' => 'ACTIVO',
        ]);

        $this->actingAs($administrador);

        // Probar los seis semestres.
        foreach ([1, 2, 3, 4, 5, 6] as $semestre) {

            // Crear una cuenta de alumno diferente.
            $cuentaAlumno = User::create([
                'nombre' => 'Alumno Semestre ' . $semestre,
                'correo' => 'alumno.semestre' . $semestre . '@example.com',
                'contrasena' => bcrypt('ClaveTemporalSegura123!'),
                'id_rol' => 3,
                'estado' => 'ACTIVO',
            ]);

            // CURP ficticia con estructura admitida por la validación.
            $curp = 'ABCD123456HXYZAB0' . $semestre;

            // Enviar el registro al controlador real.
            $response = $this->post('/admin/alumnos', [
                'id_usuario' => $cuentaAlumno->id_usuario,
                'matricula' => 'MAT-SEM-' . $semestre,
                'nombres' => 'Alumno',
                'apellido_paterno' => 'Prueba',
                'curp' => $curp,
                'semestre' => $semestre,
                'grupo' => 'A',
            ]);

            // Debe aceptar el registro.
            $response->assertRedirect(route('admin.alumnos'));

            // Comprobar que se guardó el semestre correcto.
            $this->assertDatabaseHas('alumnos', [
                'matricula' => 'MAT-SEM-' . $semestre,
                'semestre' => $semestre,
            ]);
        }

        // Deben existir seis expedientes temporales.
        $this->assertDatabaseCount('alumnos', 6);
    }

    // Comprobar que un administrador puede editar un alumno correctamente.
    public function test_administrador_puede_actualizar_semestre_y_grupo(): void
    {
        // Garantizar que la prueba utilice SQLite en memoria.
        $this->assertSame('sqlite', config('database.default'));
        $this->assertSame(
            ':memory:',
            config('database.connections.sqlite.database')
        );

        // Crear las tablas temporales necesarias.
        \Illuminate\Support\Facades\Schema::create('usuarios', function ($table) {
            $table->id('id_usuario');
            $table->unsignedBigInteger('id_rol');
            $table->string('nombre', 100);
            $table->string('correo', 100);
            $table->string('contrasena', 255);
            $table->string('estado')->default('ACTIVO');
        });

        \Illuminate\Support\Facades\Schema::create('alumnos', function ($table) {
            $table->id('id_alumno');
            $table->unsignedBigInteger('id_usuario')->nullable()->unique();
            $table->string('matricula', 30)->unique();
            $table->string('nombres', 100);
            $table->string('apellido_paterno', 100);
            $table->string('apellido_materno', 100)->nullable();
            $table->string('curp', 18)->unique();
            $table->unsignedTinyInteger('semestre');
            $table->char('grupo', 1);
            $table->string('estado')->default('ACTIVO');
            $table->timestamps();
        });

        // Crear administrador ficticio.
        $administrador = User::create([
            'nombre' => 'Administrador Prueba',
            'correo' => 'admin.actualizacion@example.com',
            'contrasena' => bcrypt('ClaveTemporalSegura123!'),
            'id_rol' => 1,
            'estado' => 'ACTIVO',
        ]);

        // Crear cuenta ficticia de alumno.
        $cuentaAlumno = User::create([
            'nombre' => 'Alumno Prueba',
            'correo' => 'alumno.actualizacion@example.com',
            'contrasena' => bcrypt('ClaveTemporalSegura456!'),
            'id_rol' => 3,
            'estado' => 'ACTIVO',
        ]);

        // Crear expediente de alumno.
        \App\Models\Alumno::create([
            'id_usuario' => $cuentaAlumno->id_usuario,
            'matricula' => 'MAT-ACT-001',
            'nombres' => 'Alumno',
            'apellido_paterno' => 'Prueba',
            'curp' => 'ABCD123456HXYZAB01',
            'semestre' => 1,
            'grupo' => 'A',
            'estado' => 'ACTIVO',
        ]);

        $this->actingAs($administrador);

        // Cambiar únicamente semestre y grupo.
        $response = $this->put('/admin/alumnos/MAT-ACT-001', [
            'id_usuario' => $cuentaAlumno->id_usuario,
            'nombres' => 'Alumno',
            'apellido_paterno' => 'Prueba',
            'curp' => 'ABCD123456HXYZAB01',
            'semestre' => 2,
            'grupo' => 'B',
        ]);

        // El controlador debe aceptar la actualización.
        $response->assertRedirect(route('admin.alumnos'));

        // Verificar los nuevos valores y los datos conservados.
        $this->assertDatabaseHas('alumnos', [
            'matricula' => 'MAT-ACT-001',
            'id_usuario' => $cuentaAlumno->id_usuario,
            'curp' => 'ABCD123456HXYZAB01',
            'semestre' => 2,
            'grupo' => 'B',
        ]);

        // Confirmar que no se creó otro expediente.
        $this->assertDatabaseCount('alumnos', 1);
    }
}
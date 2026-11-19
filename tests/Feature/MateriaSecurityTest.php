<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;

class MateriaSecurityTest extends TestCase
{
    // Verificar que un alumno no pueda registrar materias.
    public function test_alumno_no_puede_registrar_materias(): void
    {
        // Crear un usuario ficticio con rol de alumno.
        $alumno = new User();

        $alumno->id_usuario = 999;
        $alumno->id_rol = 3;
        $alumno->estado = 'ACTIVO';

        // Simular una sesión iniciada como alumno.
        $this->actingAs($alumno);

        // Intentar registrar una materia desde la ruta administrativa.
        $response = $this->post(
            route('admin.materias.store'),
            [
                'nombre' => 'MATEMATICAS DE PRUEBA',
                'semestre' => 1,
            ]
        );

        // El middleware debe rechazar la solicitud.
        $response->assertForbidden();
    }

    // Verificar que un docente no pueda registrar materias.
    public function test_docente_no_puede_registrar_materias(): void
    {
        // Crear un usuario ficticio con rol de docente.
        $docente = new User();

        $docente->id_usuario = 998;
        $docente->id_rol = 2;
        $docente->estado = 'ACTIVO';

        // Simular una sesión iniciada como docente.
        $this->actingAs($docente);

        // Intentar registrar una materia desde la ruta administrativa.
        $response = $this->post(
            route('admin.materias.store'),
            [
                'nombre' => 'MATEMATICAS DE PRUEBA',
                'semestre' => 1,
            ]
        );

        // El middleware debe rechazar la solicitud.
        $response->assertForbidden();
    }

    // Verificar que un visitante no pueda registrar materias.
    public function test_visitante_no_puede_registrar_materias(): void
    {
        // Confirmar que no existe una sesión autenticada.
        $this->assertGuest();

        // Intentar registrar una materia sin iniciar sesión.
        $response = $this->post(
            route('admin.materias.store'),
            [
                'nombre' => 'MATEMATICAS DE PRUEBA',
                'semestre' => 1,
            ]
        );

        // El sistema debe redirigir al inicio de sesión.
        $response->assertRedirect(route('login'));
    }

    // Impedir que un administrador inactivo registre materias.
    public function test_administrador_inactivo_no_puede_registrar_materias(): void
    {
        // Crear administrador ficticio inactivo.
        $administrador = new User();

        $administrador->id_usuario = 997;
        $administrador->id_rol = 1;
        $administrador->estado = 'INACTIVO';

        // Simular una sesión como administrador inactivo.
        $this->actingAs($administrador);

        // Intentar registrar una materia.
        $response = $this->post(
            route('admin.materias.store'),
            [
                'nombre' => 'MATEMATICAS DE PRUEBA',
                'semestre' => 1,
            ]
        );

        // El middleware debe rechazar la solicitud.
        $response->assertForbidden();
    }

    // Rechazar nombres de materias superiores a 100 caracteres.
    public function test_rechaza_nombre_de_materia_demasiado_largo(): void
    {
        // Confirmar que utilizamos SQLite en memoria.
        $this->assertSame('sqlite', config('database.default'));
        $this->assertSame(
            ':memory:',
            config('database.connections.sqlite.database')
        );

        // Crear una tabla temporal de materias.
        \Illuminate\Support\Facades\Schema::create('materias', function ($table) {
            $table->id('id_materia');
            $table->string('nombre', 100);
            $table->integer('semestre');
            $table->string('estado')->default('ACTIVA');
        });

        // Crear un administrador ficticio activo.
        $administrador = new User();
        $administrador->id_usuario = 996;
        $administrador->id_rol = 1;
        $administrador->estado = 'ACTIVO';

        $this->actingAs($administrador);

        // Intentar registrar un nombre de 101 caracteres.
        $response = $this->post(
            route('admin.materias.store'),
            [
                'nombre' => str_repeat('A', 101),
                'semestre' => 1,
            ]
        );

        // Laravel debe rechazar el nombre demasiado largo.
        $response->assertSessionHasErrors('nombre');

        // No debe registrarse ninguna materia.
        $this->assertDatabaseCount('materias', 0);
    }

    // Rechazar materias con semestres fuera del rango permitido.
    public function test_rechaza_semestre_invalido_al_registrar_materia(): void
    {
        // Confirmar que utilizamos SQLite en memoria.
        $this->assertSame('sqlite', config('database.default'));
        $this->assertSame(
            ':memory:',
            config('database.connections.sqlite.database')
        );

        // Crear la tabla temporal de materias.
        \Illuminate\Support\Facades\Schema::create('materias', function ($table) {
            $table->id('id_materia');
            $table->string('nombre', 100);
            $table->integer('semestre');
            $table->string('estado')->default('ACTIVA');
        });

        // Simular un administrador activo.
        $administrador = new User();
        $administrador->id_usuario = 996;
        $administrador->id_rol = 1;
        $administrador->estado = 'ACTIVO';

        $this->actingAs($administrador);

        // Intentar registrar una materia en séptimo semestre.
        $response = $this->post(
            route('admin.materias.store'),
            [
                'nombre' => 'MATEMATICAS DE PRUEBA',
                'semestre' => 7,
            ]
        );

        // Laravel debe rechazar el semestre incorrecto.
        $response->assertSessionHasErrors('semestre');

        // No debe crearse ninguna materia.
        $this->assertDatabaseCount('materias', 0);
    }

    // Verificar que un administrador pueda registrar una materia válida.
    public function test_administrador_puede_registrar_materia_valida(): void
    {
        // Confirmar que utilizamos SQLite en memoria.
        $this->assertSame('sqlite', config('database.default'));
        $this->assertSame(
            ':memory:',
            config('database.connections.sqlite.database')
        );

        // Crear la tabla temporal de materias.
        \Illuminate\Support\Facades\Schema::create('materias', function ($table) {
            $table->id('id_materia');
            $table->string('nombre', 100);
            $table->integer('semestre');
            $table->string('estado')->default('ACTIVA');
        });

        // Simular un administrador activo.
        $administrador = new User();
        $administrador->id_usuario = 996;
        $administrador->id_rol = 1;
        $administrador->estado = 'ACTIVO';

        $this->actingAs($administrador);

        // Registrar una materia válida.
        $response = $this->post(
            route('admin.materias.store'),
            [
                'nombre' => 'Matematicas de prueba',
                'semestre' => 2,
            ]
        );

        // Comprobar que el controlador aceptó el registro.
        $response->assertRedirect(route('admin.materias'));
        $response->assertSessionHas('success');

        // Verificar que el nombre se guardó en mayúsculas.
        $this->assertDatabaseHas('materias', [
            'nombre' => 'MATEMATICAS DE PRUEBA',
            'semestre' => 2,
            'estado' => 'ACTIVA',
        ]);
        // Confirmar que existe una sola materia.
        $this->assertDatabaseCount('materias', 1);
    }

    // Verificar que un administrador pueda modificar una materia.
    public function test_administrador_puede_modificar_materia(): void
    {
        // Garantizar que utilizamos SQLite en memoria.
        $this->assertSame('sqlite', config('database.default'));
        $this->assertSame(
            ':memory:',
            config('database.connections.sqlite.database')
        );

        // Crear la tabla temporal de materias.
        \Illuminate\Support\Facades\Schema::create('materias', function ($table) {
            $table->id('id_materia');
            $table->string('nombre', 100);
            $table->integer('semestre');
            $table->string('estado')->default('ACTIVA');
        });

        // Registrar una materia ficticia.
        $idMateria = \Illuminate\Support\Facades\DB::table('materias')->insertGetId([
            'nombre' => 'MATEMATICAS I',
            'semestre' => 1,
            'estado' => 'ACTIVA',
        ]);

        // Simular una sesión administrativa.
        $administrador = new User();
        $administrador->id_usuario = 996;
        $administrador->id_rol = 1;
        $administrador->estado = 'ACTIVO';

        $this->actingAs($administrador);

        // Enviar una actualización válida.
        $response = $this->put(
            route('admin.materias.update', ['id' => $idMateria]),
            [
                'nombre' => 'Matematicas II',
                'semestre' => 2,
                'estado' => 'INACTIVA',
            ]
        );

        // Confirmar que la actualización fue aceptada.
        $response->assertRedirect(route('admin.materias'));
        $response->assertSessionHas('success');

        // Verificar que se guardaron los nuevos datos.
        $this->assertDatabaseHas('materias', [
            'id_materia' => $idMateria,
            'nombre' => 'MATEMATICAS II',
            'semestre' => 2,
            'estado' => 'INACTIVA',
        ]);

        // La actualización no debe crear otra materia.
        $this->assertDatabaseCount('materias', 1);
    }

    // Verificar que un administrador pueda actualizar el periodo escolar.
    public function test_administrador_puede_actualizar_periodo_escolar(): void
    {
        // Confirmar que utilizamos SQLite en memoria.
        $this->assertSame('sqlite', config('database.default'));
        $this->assertSame(
            ':memory:',
            config('database.connections.sqlite.database')
        );

        // Crear la tabla temporal de configuraciones.
        \Illuminate\Support\Facades\Schema::create('configuraciones', function ($table) {
            $table->id('id_configuracion');
            $table->string('clave')->unique();
            $table->string('valor');
            $table->timestamps();
        });

        // Crear administrador ficticio activo.
        $administrador = new User();
        $administrador->id_usuario = 996;
        $administrador->id_rol = 1;
        $administrador->estado = 'ACTIVO';

        $this->actingAs($administrador);

        // Enviar un periodo escolar válido.
        $response = $this->post(
            route('admin.materias.periodo'),
            [
                'periodo_actual' => 'Febrero-Julio 2027',
            ]
        );

        // Confirmar que la actualización fue aceptada.
        $response->assertSessionHasNoErrors();
        $response->assertSessionHas('success');

        // Comprobar que el periodo se guardó correctamente.
        $this->assertDatabaseHas('configuraciones', [
            'clave' => 'periodo_actual',
            'valor' => 'Febrero-Julio 2027',
        ]);

        // Confirmar que solo existe una configuración.
        $this->assertDatabaseCount('configuraciones', 1);
    }

    // Rechazar periodos escolares superiores a 255 caracteres.
    public function test_rechaza_periodo_escolar_demasiado_largo(): void
    {
        // Verificar el uso de SQLite en memoria.
        $this->assertSame('sqlite', config('database.default'));
        $this->assertSame(
            ':memory:',
            config('database.connections.sqlite.database')
        );

        // Crear una tabla temporal de configuraciones.
        \Illuminate\Support\Facades\Schema::create('configuraciones', function ($table) {
            $table->id('id_configuracion');
            $table->string('clave')->unique();
            $table->string('valor', 255);
            $table->timestamps();
        });

        // Registrar un periodo ficticio inicial.
        \Illuminate\Support\Facades\DB::table('configuraciones')->insert([
            'clave' => 'periodo_actual',
            'valor' => 'Agosto-Diciembre 2026',
        ]);

        // Simular un administrador activo.
        $administrador = new User();
        $administrador->id_usuario = 996;
        $administrador->id_rol = 1;
        $administrador->estado = 'ACTIVO';

        $this->actingAs($administrador);

        // Intentar guardar un periodo de 256 caracteres.
        $response = $this->post(
            route('admin.materias.periodo'),
            [
                'periodo_actual' => str_repeat('A', 256),
            ]
        );

        // Laravel debe rechazar el valor demasiado largo.
        $response->assertSessionHasErrors('periodo_actual');

        // El periodo original debe permanecer intacto.
        $this->assertDatabaseHas('configuraciones', [
            'clave' => 'periodo_actual',
            'valor' => 'Agosto-Diciembre 2026',
        ]);

        // No debe crearse una configuración adicional.
        $this->assertDatabaseCount('configuraciones', 1);
    }

}

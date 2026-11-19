<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Tests\TestCase;

class UsuarioSecurityTest extends TestCase
{
    public function test_administrador_no_puede_desactivar_su_propia_cuenta(): void
    {
        // Comprobar que estamos usando SQLite temporal.
        $this->assertSame('sqlite', config('database.default'));
        $this->assertSame(
            ':memory:',
            config('database.connections.sqlite.database')
        );

        // Crear tabla temporal necesaria para la prueba.
        Schema::create('roles', function (Blueprint $table) {
            $table->id('id_rol');
        });

        Schema::create('usuarios', function (Blueprint $table) {
            $table->id('id_usuario');
            $table->unsignedBigInteger('id_rol');
            $table->string('nombre', 100);
            $table->string('correo', 100)->unique();
            $table->string('contrasena', 255);
            $table->enum('estado', ['ACTIVO', 'INACTIVO'])
                ->default('ACTIVO');
        });

        // Crear un rol administrador.
        \DB::table('roles')->insert([
            'id_rol' => 1,
        ]);

        // Crear administrador solo en SQLite temporal.
        $administrador = User::create([
            'nombre' => 'Administrador Prueba',
            'correo' => 'admin.prueba@example.com',
            'contrasena' => bcrypt('ClaveSeguraTemporal123!'),
            'id_rol' => 1,
            'estado' => 'ACTIVO',
        ]);

        $this->actingAs($administrador);

        // Intentar desactivar su propia cuenta.
        $response = $this->put(
            '/admin/usuarios/' . $administrador->id_usuario,
            [
                'nombre' => 'Administrador Prueba',
                'correo' => 'admin.prueba@example.com',
                'id_rol' => 1,
                'estado' => 'INACTIVO',
            ]
        );

        // La modificación debe rechazarse.
        $response->assertSessionHasErrors('estado');

        // Comprobar que sigue activo en SQLite.
        $this->assertDatabaseHas('usuarios', [
            'id_usuario' => $administrador->id_usuario,
            'estado' => 'ACTIVO',
        ]);
    }

    // No permitir correos electrónicos duplicados.
    public function test_no_permite_correos_duplicados_al_editar(): void
    {
        // Confirmar que estamos utilizando SQLite en memoria.
        $this->assertSame('sqlite', config('database.default'));
        $this->assertSame(
            ':memory:',
            config('database.connections.sqlite.database')
        );

        // Crear las tablas necesarias para esta prueba.
        \Illuminate\Support\Facades\Schema::create('roles', function ($table) {
            $table->id('id_rol');
        });

        \Illuminate\Support\Facades\Schema::create('usuarios', function ($table) {
            $table->id('id_usuario');
            $table->unsignedBigInteger('id_rol');
            $table->string('nombre', 100);
            $table->string('correo', 100)->unique();
            $table->string('contrasena', 255);
            $table->enum('estado', ['ACTIVO', 'INACTIVO'])->default('ACTIVO');
        });

        \DB::table('roles')->insert(['id_rol' => 1]);

        // Crear administrador.
        $administrador = User::create([
            'nombre' => 'Administrador Prueba',
            'correo' => 'admin.prueba@example.com',
            'contrasena' => bcrypt('ClaveTemporalSegura123!'),
            'id_rol' => 1,
            'estado' => 'ACTIVO',
        ]);

        // Crear otro usuario con un correo diferente.
        $otroUsuario = User::create([
            'nombre' => 'Usuario Prueba',
            'correo' => 'usuario.prueba@example.com',
            'contrasena' => bcrypt('OtraClaveSegura123!'),
            'id_rol' => 1,
            'estado' => 'ACTIVO',
        ]);

        $this->actingAs($administrador);

        // Intentar utilizar un correo que ya existe.
        $response = $this->put(
            '/admin/usuarios/' . $otroUsuario->id_usuario,
            [
                'nombre' => 'Usuario Prueba',
                'correo' => 'admin.prueba@example.com',
                'id_rol' => 1,
                'estado' => 'ACTIVO',
            ]
        );

        // Debe rechazarse por correo duplicado.
        $response->assertSessionHasErrors('correo');

        // El correo anterior debe permanecer sin cambios.
        $this->assertDatabaseHas('usuarios', [
            'id_usuario' => $otroUsuario->id_usuario,
            'correo' => 'usuario.prueba@example.com',
        ]);
    }

    // Impedir asignar roles que no existen.
    public function test_no_permite_asignar_roles_inexistentes(): void
    {
        // Verificar el entorno de pruebas.
        $this->assertSame('sqlite', config('database.default'));
        $this->assertSame(
            ':memory:',
            config('database.connections.sqlite.database')
        );

        // Crear tablas únicamente en SQLite temporal.
        \Illuminate\Support\Facades\Schema::create('roles', function ($table) {
            $table->id('id_rol');
        });

        \Illuminate\Support\Facades\Schema::create('usuarios', function ($table) {
            $table->id('id_usuario');
            $table->unsignedBigInteger('id_rol');
            $table->string('nombre', 100);
            $table->string('correo', 100)->unique();
            $table->string('contrasena', 255);
            $table->enum('estado', ['ACTIVO', 'INACTIVO'])
                ->default('ACTIVO');
        });

        // Registrar únicamente el rol administrador.
        \DB::table('roles')->insert([
            'id_rol' => 1,
        ]);

        // Crear un administrador de prueba.
        $administrador = User::create([
            'nombre' => 'Administrador Prueba',
            'correo' => 'admin.roles@example.com',
            'contrasena' => bcrypt('ClaveTemporalSegura123!'),
            'id_rol' => 1,
            'estado' => 'ACTIVO',
        ]);

        $this->actingAs($administrador);

        // Intentar asignar un rol inexistente.
        $response = $this->put(
            '/admin/usuarios/' . $administrador->id_usuario,
            [
                'nombre' => 'Administrador Prueba',
                'correo' => 'admin.roles@example.com',
                'id_rol' => 999,
                'estado' => 'ACTIVO',
            ]
        );

        // Debe rechazarse la modificación.
        $response->assertSessionHasErrors('id_rol');

        // El administrador debe conservar su rol.
        $this->assertDatabaseHas('usuarios', [
            'id_usuario' => $administrador->id_usuario,
            'id_rol' => 1,
            'estado' => 'ACTIVO',
        ]);
    }

    // Verificar que una nueva contraseña se almacene cifrada.
    public function test_contrasena_se_guarda_con_hash_al_editar(): void
    {
        // Verificar que usamos SQLite en memoria.
        $this->assertSame('sqlite', config('database.default'));
        $this->assertSame(
            ':memory:',
            config('database.connections.sqlite.database')
        );

        // Crear tablas temporales de prueba.
        \Illuminate\Support\Facades\Schema::create('roles', function ($table) {
            $table->id('id_rol');
        });

        \Illuminate\Support\Facades\Schema::create('usuarios', function ($table) {
            $table->id('id_usuario');
            $table->unsignedBigInteger('id_rol');
            $table->string('nombre', 100);
            $table->string('correo', 100)->unique();
            $table->string('contrasena', 255);
            $table->enum('estado', ['ACTIVO', 'INACTIVO'])
                ->default('ACTIVO');
        });

        // Crear los roles de administrador y alumno.
        \DB::table('roles')->insert([
            ['id_rol' => 1],
            ['id_rol' => 3],
        ]);

        // Crear un administrador de prueba.
        $administrador = User::create([
            'nombre' => 'Administrador Prueba',
            'correo' => 'admin.hash@example.com',
            'contrasena' => bcrypt('ClaveAnteriorAdmin123!'),
            'id_rol' => 1,
            'estado' => 'ACTIVO',
        ]);

        // Crear un alumno de prueba.
        $alumno = User::create([
            'nombre' => 'Alumno Prueba',
            'correo' => 'alumno.hash@example.com',
            'contrasena' => bcrypt('ClaveAnteriorAlumno123!'),
            'id_rol' => 3,
            'estado' => 'ACTIVO',
        ]);

        $this->actingAs($administrador);

        $nuevaContrasena = 'NuevaClaveSegura123!';

        // Cambiar la contraseña del alumno.
        $response = $this->put(
            '/admin/usuarios/' . $alumno->id_usuario,
            [
                'nombre' => 'Alumno Prueba',
                'correo' => 'alumno.hash@example.com',
                'id_rol' => 3,
                'estado' => 'ACTIVO',
                'password' => $nuevaContrasena,
            ]
        );

        // Confirmar que la modificación fue aceptada.
        $response->assertRedirect(route('admin.usuarios'));

        // Consultar la contraseña almacenada.
        $alumno->refresh();

        // Comprobar que no se guardó en texto plano.
        $this->assertNotSame(
            $nuevaContrasena,
            $alumno->contrasena
        );

        // Comprobar que el hash corresponde a la contraseña.
        $this->assertTrue(
            \Illuminate\Support\Facades\Hash::check(
                $nuevaContrasena,
                $alumno->contrasena
            )
        );
    }

    // Impedir desactivar al último administrador activo.
    public function test_no_permite_desactivar_al_ultimo_administrador(): void
    {
        // Verificar el entorno de pruebas.
        $this->assertSame('sqlite', config('database.default'));
        $this->assertSame(
            ':memory:',
            config('database.connections.sqlite.database')
        );

        // Crear tablas temporales.
        \Illuminate\Support\Facades\Schema::create('roles', function ($table) {
            $table->id('id_rol');
        });

        \Illuminate\Support\Facades\Schema::create('usuarios', function ($table) {
            $table->id('id_usuario');
            $table->unsignedBigInteger('id_rol');
            $table->string('nombre', 100);
            $table->string('correo', 100)->unique();
            $table->string('contrasena', 255);
            $table->enum('estado', ['ACTIVO', 'INACTIVO'])
                ->default('ACTIVO');
        });

        \DB::table('roles')->insert([
            ['id_rol' => 1],
            ['id_rol' => 3],
        ]);

        // Único administrador activo.
        $administrador = User::create([
            'nombre' => 'Administrador Principal',
            'correo' => 'principal.prueba@example.com',
            'contrasena' => bcrypt('ClavePrincipal123!'),
            'id_rol' => 1,
            'estado' => 'ACTIVO',
        ]);

        // Administrador inactivo.
        $administradorInactivo = User::create([
            'nombre' => 'Administrador Inactivo',
            'correo' => 'inactivo.prueba@example.com',
            'contrasena' => bcrypt('ClaveInactiva123!'),
            'id_rol' => 1,
            'estado' => 'INACTIVO',
        ]);

        $this->actingAs($administrador);

        // Intentar quitarle el rol de administrador.
        $response = $this->put(
            '/admin/usuarios/' . $administrador->id_usuario,
            [
                'nombre' => 'Administrador Principal',
                'correo' => 'principal.prueba@example.com',
                'id_rol' => 3,
                'estado' => 'ACTIVO',
            ]
        );

        // La modificación debe rechazarse.
        $response->assertSessionHasErrors();

        // La cuenta debe conservar su rol y estado.
        $this->assertDatabaseHas('usuarios', [
            'id_usuario' => $administrador->id_usuario,
            'id_rol' => 1,
            'estado' => 'ACTIVO',
        ]);

        // La otra cuenta debe seguir inactiva.
        $this->assertDatabaseHas('usuarios', [
            'id_usuario' => $administradorInactivo->id_usuario,
            'estado' => 'INACTIVO',
        ]);
    }
}
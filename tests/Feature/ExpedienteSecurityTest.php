<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;

class ExpedienteSecurityTest extends TestCase
{
    public function test_alumno_no_puede_modificar_estado_de_documento(): void
    {
        // Crear un usuario ficticio de tipo alumno.
        $alumno = new User();

        $alumno->id_usuario = 999;
        $alumno->id_rol = 3;
        $alumno->estado = 'ACTIVO';

        // Simular una sesión iniciada como alumno.
        $this->actingAs($alumno);

        // Intentar aprobar un documento desde la ruta administrativa.
        $response = $this->put(
            route('admin.documentos.estado', ['id' => 1]),
            [
                'estado_validacion' => 'APROBADO',
                'observaciones' => 'Intento de modificación no autorizada',
            ]
        );

        // El servidor debe rechazar la operación.
        $response->assertForbidden();
    }

    // Verificar que un docente no pueda modificar documentos.
    public function test_docente_no_puede_modificar_estado_de_documento(): void
    {
        // Crear un docente ficticio.
        $docente = new User();

        $docente->id_usuario = 998;
        $docente->id_rol = 2;
        $docente->estado = 'ACTIVO';

        // Iniciar sesión como docente.
        $this->actingAs($docente);

        // Intentar aprobar un documento administrativo.
        $response = $this->put(
            route('admin.documentos.estado', ['id' => 1]),
            [
                'estado_validacion' => 'APROBADO',
                'observaciones' => 'Intento de modificación por docente',
            ]
        );

        // El servidor debe denegar el acceso.
        $response->assertForbidden();
    }

    // Impedir que visitantes modifiquen documentos.
    public function test_visitante_no_puede_modificar_estado_de_documento(): void
    {
        // Garantizar que no exista una sesión autenticada.
        $this->assertGuest();

        // Intentar aprobar un documento sin iniciar sesión.
        $response = $this->put(
            route('admin.documentos.estado', ['id' => 1]),
            [
                'estado_validacion' => 'APROBADO',
                'observaciones' => 'Intento sin autenticación',
            ]
        );

        // El sistema debe redirigir al inicio de sesión.
        $response->assertRedirect(route('login'));
    }

    // Impedir modificaciones de administradores inactivos.
    public function test_administrador_inactivo_no_puede_modificar_documentos(): void
    {
        // Crear un administrador ficticio inactivo.
        $administrador = new User();

        $administrador->id_usuario = 997;
        $administrador->id_rol = 1;
        $administrador->estado = 'INACTIVO';

        // Simular una sesión como administrador inactivo.
        $this->actingAs($administrador);

        // Intentar aprobar un documento.
        $response = $this->put(
            route('admin.documentos.estado', ['id' => 1]),
            [
                'estado_validacion' => 'APROBADO',
                'observaciones' => 'Intento de administrador inactivo',
            ]
        );

        // El middleware debe rechazar la operación.
        $response->assertForbidden();
    }

    // Impedir que se asignen estados no permitidos.
    public function test_rechaza_estado_de_documento_invalido(): void
    {
        // Confirmar que trabajamos con SQLite en memoria.
        $this->assertSame('sqlite', config('database.default'));
        $this->assertSame(
            ':memory:',
            config('database.connections.sqlite.database')
        );

        // Crear una tabla temporal de documentos.
        \Illuminate\Support\Facades\Schema::create('documentos', function ($table) {
            $table->id('id_documento');
            $table->unsignedBigInteger('id_alumno');
            $table->string('tipo_documento', 100);
            $table->string('proceso');
            $table->string('ruta_archivo');
            $table->string('estado_validacion')->default('PENDIENTE');
            $table->text('observaciones')->nullable();
            $table->timestamps();
        });

        // Crear un documento ficticio.
        \Illuminate\Support\Facades\DB::table('documentos')->insert([
            'id_documento' => 1,
            'id_alumno' => 1,
            'tipo_documento' => 'Acta de nacimiento',
            'proceso' => 'INSCRIPCION',
            'ruta_archivo' => 'pruebas/acta.pdf',
            'estado_validacion' => 'PENDIENTE',
            'observaciones' => null,
        ]);

        // Simular un administrador activo.
        $administrador = new User();
        $administrador->id_usuario = 996;
        $administrador->id_rol = 1;
        $administrador->estado = 'ACTIVO';

        $this->actingAs($administrador);

        // Intentar asignar un estado no permitido.
        $response = $this->put(
            route('admin.documentos.estado', ['id' => 1]),
            [
                'estado_validacion' => 'ELIMINADO',
                'observaciones' => 'Prueba de estado inválido',
            ]
        );

        // El controlador debe rechazar el estado.
        $response->assertSessionHasErrors('estado_validacion');

        // El documento debe permanecer sin modificaciones.
        $this->assertDatabaseHas('documentos', [
            'id_documento' => 1,
            'estado_validacion' => 'PENDIENTE',
            'observaciones' => null,
        ]);
    }

    // Verificar que un administrador activo pueda aprobar un documento.
    public function test_administrador_puede_aprobar_documento(): void
    {
        // Verificar que utilizamos SQLite en memoria.
        $this->assertSame('sqlite', config('database.default'));
        $this->assertSame(
            ':memory:',
            config('database.connections.sqlite.database')
        );

        // Crear una tabla temporal de documentos.
        \Illuminate\Support\Facades\Schema::create('documentos', function ($table) {
            $table->id('id_documento');
            $table->unsignedBigInteger('id_alumno');
            $table->string('tipo_documento', 100);
            $table->string('proceso');
            $table->string('ruta_archivo');
            $table->string('estado_validacion')->default('PENDIENTE');
            $table->text('observaciones')->nullable();
            $table->timestamps();
        });

        // Crear documento ficticio.
        \Illuminate\Support\Facades\DB::table('documentos')->insert([
            'id_documento' => 1,
            'id_alumno' => 1,
            'tipo_documento' => 'Acta de nacimiento',
            'proceso' => 'INSCRIPCION',
            'ruta_archivo' => 'pruebas/acta.pdf',
            'estado_validacion' => 'PENDIENTE',
            'observaciones' => null,
        ]);

        // Crear administrador ficticio activo.
        $administrador = new User();
        $administrador->id_usuario = 996;
        $administrador->id_rol = 1;
        $administrador->estado = 'ACTIVO';

        $this->actingAs($administrador);

        // Aprobar el documento.
        $response = $this->put(
            route('admin.documentos.estado', ['id' => 1]),
            [
                'estado_validacion' => 'APROBADO',
                'observaciones' => 'Documento revisado correctamente',
            ]
        );

        // Confirmar que la solicitud fue aceptada.
        $response->assertSessionHasNoErrors();
        $response->assertSessionHas('success');

        // Comprobar que se actualizaron los datos.
        $this->assertDatabaseHas('documentos', [
            'id_documento' => 1,
            'estado_validacion' => 'APROBADO',
            'observaciones' => 'Documento revisado correctamente',
        ]);

        // Confirmar que no se creó otro documento.
        $this->assertDatabaseCount('documentos', 1);
    }

    // Rechazar observaciones que superen los 5000 caracteres.
    public function test_rechaza_observaciones_demasiado_extensas(): void
    {
        // Comprobar que utilizamos SQLite en memoria.
        $this->assertSame('sqlite', config('database.default'));
        $this->assertSame(
            ':memory:',
            config('database.connections.sqlite.database')
        );

        // Crear una tabla temporal de documentos.
        \Illuminate\Support\Facades\Schema::create('documentos', function ($table) {
            $table->id('id_documento');
            $table->unsignedBigInteger('id_alumno');
            $table->string('tipo_documento', 100);
            $table->string('proceso');
            $table->string('ruta_archivo');
            $table->string('estado_validacion')->default('PENDIENTE');
            $table->text('observaciones')->nullable();
            $table->timestamps();
        });

        // Crear un documento de prueba.
        \Illuminate\Support\Facades\DB::table('documentos')->insert([
            'id_documento' => 1,
            'id_alumno' => 1,
            'tipo_documento' => 'Acta de nacimiento',
            'proceso' => 'INSCRIPCION',
            'ruta_archivo' => 'pruebas/acta.pdf',
            'estado_validacion' => 'PENDIENTE',
            'observaciones' => 'Observación original',
        ]);

        // Crear un administrador ficticio activo.
        $administrador = new User();
        $administrador->id_usuario = 996;
        $administrador->id_rol = 1;
        $administrador->estado = 'ACTIVO';

        $this->actingAs($administrador);

        // Enviar una observación de 5001 caracteres.
        $response = $this->put(
            route('admin.documentos.estado', ['id' => 1]),
            [
                'estado_validacion' => 'APROBADO',
                'observaciones' => str_repeat('A', 5001),
            ]
        );

        // Laravel debe rechazar el texto demasiado largo.
        $response->assertSessionHasErrors('observaciones');

        // El documento debe conservar sus valores originales.
        $this->assertDatabaseHas('documentos', [
            'id_documento' => 1,
            'estado_validacion' => 'PENDIENTE',
            'observaciones' => 'Observación original',
        ]);
    }

    // Verificar que un administrador pueda rechazar un documento.
    public function test_administrador_puede_rechazar_documento(): void
    {
        // Verificar que utilizamos SQLite en memoria.
        $this->assertSame('sqlite', config('database.default'));
        $this->assertSame(
            ':memory:',
            config('database.connections.sqlite.database')
        );

        // Crear una tabla temporal de documentos.
        \Illuminate\Support\Facades\Schema::create('documentos', function ($table) {
            $table->id('id_documento');
            $table->unsignedBigInteger('id_alumno');
            $table->string('tipo_documento', 100);
            $table->string('proceso');
            $table->string('ruta_archivo');
            $table->string('estado_validacion')->default('PENDIENTE');
            $table->text('observaciones')->nullable();
            $table->timestamps();
        });

        // Crear un documento ficticio.
        \Illuminate\Support\Facades\DB::table('documentos')->insert([
            'id_documento' => 1,
            'id_alumno' => 1,
            'tipo_documento' => 'Acta de nacimiento',
            'proceso' => 'INSCRIPCION',
            'ruta_archivo' => 'pruebas/acta.pdf',
            'estado_validacion' => 'PENDIENTE',
            'observaciones' => null,
        ]);

        // Crear un administrador ficticio activo.
        $administrador = new User();
        $administrador->id_usuario = 996;
        $administrador->id_rol = 1;
        $administrador->estado = 'ACTIVO';

        $this->actingAs($administrador);

        // Rechazar el documento con observaciones.
        $response = $this->put(
            route('admin.documentos.estado', ['id' => 1]),
            [
                'estado_validacion' => 'RECHAZADO',
                'observaciones' => 'El documento es ilegible.',
            ]
        );

        // Confirmar que la operación fue aceptada.
        $response->assertSessionHasNoErrors();
        $response->assertSessionHas('success');

        // Verificar que el documento fue rechazado.
        $this->assertDatabaseHas('documentos', [
            'id_documento' => 1,
            'estado_validacion' => 'RECHAZADO',
            'observaciones' => 'El documento es ilegible.',
        ]);

        // Confirmar que no se creó otro documento.
        $this->assertDatabaseCount('documentos', 1);
    }

}

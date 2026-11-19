<?php

namespace Tests\Feature;

use Tests\TestCase;

class DocumentoPrivadoSecurityTest extends TestCase
{
    // Impedir que visitantes consulten documentos privados.
    public function test_visitante_no_puede_visualizar_documento_privado(): void
    {
        // Confirmar que no existe una sesión autenticada.
        $this->assertGuest();

        // Intentar visualizar un PDF sin iniciar sesión.
        $response = $this->get(
            route('documentos.ver', [
                'id_documento' => 1,
            ])
        );

        // Laravel debe redirigir al inicio de sesión.
        $response->assertRedirect(route('login'));
    }

    // Impedir que docentes consulten documentos de alumnos.
    public function test_docente_no_puede_visualizar_documento_privado(): void
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

        // Registrar un documento ficticio.
        \Illuminate\Support\Facades\DB::table('documentos')->insert([
            'id_documento' => 1,
            'id_alumno' => 1,
            'tipo_documento' => 'Acta de nacimiento',
            'proceso' => 'INSCRIPCION',
            'ruta_archivo' => 'expedientes/prueba/acta.pdf',
            'estado_validacion' => 'PENDIENTE',
        ]);

        // Simular una cuenta de docente activa.
        $docente = new \App\Models\User();
        $docente->id_usuario = 998;
        $docente->id_rol = 2;
        $docente->estado = 'ACTIVO';

        $this->actingAs($docente);

        // Intentar abrir el documento desde la ruta protegida.
        $response = $this->get(
            route('documentos.ver', [
                'id_documento' => 1,
            ])
        );

        // El controlador debe rechazar el acceso.
        $response->assertForbidden();

        // El registro debe permanecer intacto.
        $this->assertDatabaseHas('documentos', [
            'id_documento' => 1,
            'id_alumno' => 1,
            'estado_validacion' => 'PENDIENTE',
        ]);
    }

    // Impedir que un alumno consulte documentos de otro estudiante.
    public function test_alumno_no_puede_visualizar_documento_ajeno(): void
    {
        // Garantizar el uso de SQLite en memoria.
        $this->assertSame('sqlite', config('database.default'));
        $this->assertSame(
            ':memory:',
            config('database.connections.sqlite.database')
        );

        // Crear la tabla temporal de alumnos.
        \Illuminate\Support\Facades\Schema::create('alumnos', function ($table) {
            $table->id('id_alumno');
            $table->unsignedBigInteger('id_usuario')->unique();
            $table->string('matricula', 30)->unique();
        });

        // Crear la tabla temporal de documentos.
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

        // Crear dos expedientes ficticios.
        \Illuminate\Support\Facades\DB::table('alumnos')->insert([
            [
                'id_alumno' => 1,
                'id_usuario' => 101,
                'matricula' => 'MAT-ALUMNO-001',
            ],
            [
                'id_alumno' => 2,
                'id_usuario' => 102,
                'matricula' => 'MAT-ALUMNO-002',
            ],
        ]);

        // Registrar un documento perteneciente al segundo alumno.
        \Illuminate\Support\Facades\DB::table('documentos')->insert([
            'id_documento' => 1,
            'id_alumno' => 2,
            'tipo_documento' => 'Acta de nacimiento',
            'proceso' => 'INSCRIPCION',
            'ruta_archivo' => 'expedientes/prueba/acta.pdf',
            'estado_validacion' => 'PENDIENTE',
        ]);

        // Simular la sesión del primer alumno.
        $alumno = new \App\Models\User();
        $alumno->id_usuario = 101;
        $alumno->id_rol = 3;
        $alumno->estado = 'ACTIVO';

        $this->actingAs($alumno);

        // Intentar visualizar el documento del segundo alumno.
        $response = $this->get(
            route('documentos.ver', [
                'id_documento' => 1,
            ])
        );

        // Debe rechazarse el acceso al documento ajeno.
        $response->assertForbidden();

        // El documento debe permanecer sin modificaciones.
        $this->assertDatabaseHas('documentos', [
            'id_documento' => 1,
            'id_alumno' => 2,
            'estado_validacion' => 'PENDIENTE',
        ]);
    }

    // Permitir que un alumno consulte su propio documento privado.
    public function test_alumno_puede_visualizar_su_propio_documento(): void
    {
        // Garantizar que utilizamos SQLite en memoria.
        $this->assertSame('sqlite', config('database.default'));
        $this->assertSame(
            ':memory:',
            config('database.connections.sqlite.database')
        );

        // Simular almacenamiento privado sin utilizar archivos reales.
        \Illuminate\Support\Facades\Storage::fake('local');

        // Crear tablas temporales.
        \Illuminate\Support\Facades\Schema::create('alumnos', function ($table) {
            $table->id('id_alumno');
            $table->unsignedBigInteger('id_usuario')->unique();
            $table->string('matricula', 30)->unique();
        });

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

        // Crear expediente ficticio del alumno.
        \Illuminate\Support\Facades\DB::table('alumnos')->insert([
            'id_alumno' => 1,
            'id_usuario' => 101,
            'matricula' => 'MAT-PRUEBA-001',
        ]);

        // Crear un archivo PDF ficticio en almacenamiento temporal.
        $ruta = 'expedientes/MAT-PRUEBA-001/prueba.pdf';

        \Illuminate\Support\Facades\Storage::disk('local')->put(
            $ruta,
            "%PDF-1.4\n% Documento de prueba\n"
        );

        // Registrar el documento en SQLite.
        \Illuminate\Support\Facades\DB::table('documentos')->insert([
            'id_documento' => 1,
            'id_alumno' => 1,
            'tipo_documento' => 'Acta de nacimiento',
            'proceso' => 'INSCRIPCION',
            'ruta_archivo' => $ruta,
            'estado_validacion' => 'PENDIENTE',
        ]);

        // Simular una sesión del alumno propietario.
        $alumno = new \App\Models\User();
        $alumno->id_usuario = 101;
        $alumno->id_rol = 3;
        $alumno->estado = 'ACTIVO';

        $this->actingAs($alumno);

        // Solicitar su propio documento.
        $response = $this->get(
            route('documentos.ver', [
                'id_documento' => 1,
            ])
        );

        // Confirmar que el servidor permite visualizar el PDF.
        $response->assertOk();
        $response->assertHeader('Content-Type', 'application/pdf');
        $response->assertHeader(
            'X-Content-Type-Options',
            'nosniff'
        );

        // El documento debe permanecer registrado.
        $this->assertDatabaseHas('documentos', [
            'id_documento' => 1,
            'id_alumno' => 1,
            'ruta_archivo' => $ruta,
        ]);
    }

    // Permitir que un administrador activo revise documentos privados.
    public function test_administrador_puede_visualizar_documento_privado(): void
    {
        // Confirmar que usamos SQLite en memoria.
        $this->assertSame('sqlite', config('database.default'));
        $this->assertSame(
            ':memory:',
            config('database.connections.sqlite.database')
        );

        // Simular el almacenamiento privado.
        \Illuminate\Support\Facades\Storage::fake('local');

        // Crear tabla temporal de documentos.
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

        // Crear un PDF ficticio en almacenamiento temporal.
        $ruta = 'expedientes/MAT-PRUEBA-001/acta.pdf';

        \Illuminate\Support\Facades\Storage::disk('local')->put(
            $ruta,
            "%PDF-1.4\n% Documento de prueba\n"
        );

        // Registrar el documento ficticio.
        \Illuminate\Support\Facades\DB::table('documentos')->insert([
            'id_documento' => 1,
            'id_alumno' => 1,
            'tipo_documento' => 'Acta de nacimiento',
            'proceso' => 'INSCRIPCION',
            'ruta_archivo' => $ruta,
            'estado_validacion' => 'PENDIENTE',
        ]);

        // Simular un administrador activo.
        $administrador = new \App\Models\User();
        $administrador->id_usuario = 996;
        $administrador->id_rol = 1;
        $administrador->estado = 'ACTIVO';

        $this->actingAs($administrador);

        // Solicitar el documento.
        $response = $this->get(
            route('documentos.ver', [
                'id_documento' => 1,
            ])
        );

        // Confirmar que el administrador puede visualizarlo.
        $response->assertOk();
        $response->assertHeader('Content-Type', 'application/pdf');
        $response->assertHeader(
            'X-Content-Type-Options',
            'nosniff'
        );

        // Comprobar que el documento sigue registrado.
        $this->assertDatabaseHas('documentos', [
            'id_documento' => 1,
            'id_alumno' => 1,
            'ruta_archivo' => $ruta,
        ]);
    }

    // Verificar que los PDF se guarden en almacenamiento privado.
    public function test_alumno_sube_pdf_al_almacenamiento_privado(): void
    {
        // Asegurar que la base de datos de pruebas es SQLite en memoria.
        $this->assertSame('sqlite', config('database.default'));
        $this->assertSame(
            ':memory:',
            config('database.connections.sqlite.database')
        );

        // Simular los discos sin utilizar archivos reales.
        \Illuminate\Support\Facades\Storage::fake('local');
        \Illuminate\Support\Facades\Storage::fake('public');

        // Crear tablas temporales para esta prueba.
        \Illuminate\Support\Facades\Schema::create('alumnos', function ($table) {
            $table->id('id_alumno');
            $table->unsignedBigInteger('id_usuario')->unique();
            $table->string('matricula', 30)->unique();
        });

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

        // Crear un alumno ficticio.
        \Illuminate\Support\Facades\DB::table('alumnos')->insert([
            'id_alumno' => 1,
            'id_usuario' => 101,
            'matricula' => 'MAT-PRUEBA-001',
        ]);

        // Simular la sesión del alumno.
        $usuario = new \App\Models\User();
        $usuario->id_usuario = 101;
        $usuario->id_rol = 3;
        $usuario->estado = 'ACTIVO';

        $this->actingAs($usuario);

        // Crear un PDF temporal válido para las reglas de Laravel.
        $pdf = \Illuminate\Http\UploadedFile::fake()->createWithContent(
            'acta.pdf',
            "%PDF-1.4\n1 0 obj\n<< /Type /Catalog >>\nendobj\n%%EOF"
        );

        // Enviar el archivo a la ruta real del módulo.
        $response = $this->post(
            route('alumno.documentos.store'),
            [
                'tipo_documento' => 'ACTA_NACIMIENTO',
                'proceso' => 'INSCRIPCION',
                'archivo' => $pdf,
            ]
        );

        // Confirmar que la subida fue aceptada.
        $response->assertSessionHasNoErrors();
        $response->assertSessionHas('success');

        // Consultar el documento registrado.
        $documento = \Illuminate\Support\Facades\DB::table('documentos')
            ->where('id_alumno', 1)
            ->first();

        $this->assertNotNull($documento);
        $this->assertSame('PENDIENTE', $documento->estado_validacion);

        // Comprobar que el archivo existe exclusivamente en el disco privado.
        \Illuminate\Support\Facades\Storage::disk('local')
            ->assertExists($documento->ruta_archivo);

        \Illuminate\Support\Facades\Storage::disk('public')
            ->assertMissing($documento->ruta_archivo);
    }

    // Verificar el reemplazo de un documento rechazado.
    public function test_alumno_puede_reemplazar_documento_rechazado(): void
    {
        // Utilizar exclusivamente SQLite en memoria.
        $this->assertSame('sqlite', config('database.default'));
        $this->assertSame(
            ':memory:',
            config('database.connections.sqlite.database')
        );

        // Simular el almacenamiento privado.
        \Illuminate\Support\Facades\Storage::fake('local');

        // Crear tablas temporales.
        \Illuminate\Support\Facades\Schema::create('alumnos', function ($table) {
            $table->id('id_alumno');
            $table->unsignedBigInteger('id_usuario')->unique();
            $table->string('matricula', 30)->unique();
        });

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

        // Registrar un alumno de prueba.
        \Illuminate\Support\Facades\DB::table('alumnos')->insert([
            'id_alumno' => 1,
            'id_usuario' => 101,
            'matricula' => 'MAT-PRUEBA-001',
        ]);

        // Crear un PDF anterior, rechazado.
        $rutaAnterior = 'expedientes/MAT-PRUEBA-001/anterior.pdf';

        \Illuminate\Support\Facades\Storage::disk('local')->put(
            $rutaAnterior,
            "%PDF-1.4\n% Documento anterior\n"
        );

        // Registrar el documento rechazado.
        \Illuminate\Support\Facades\DB::table('documentos')->insert([
            'id_documento' => 1,
            'id_alumno' => 1,
            'tipo_documento' => 'ACTA_NACIMIENTO',
            'proceso' => 'INSCRIPCION',
            'ruta_archivo' => $rutaAnterior,
            'estado_validacion' => 'RECHAZADO',
        ]);

        // Simular una sesión de alumno activo.
        $usuario = new \App\Models\User();
        $usuario->id_usuario = 101;
        $usuario->id_rol = 3;
        $usuario->estado = 'ACTIVO';

        $this->actingAs($usuario);

        // Preparar el PDF de reemplazo.
        $nuevoPdf = \Illuminate\Http\UploadedFile::fake()->createWithContent(
            'nuevo.pdf',
            "%PDF-1.4\n1 0 obj\n<< /Type /Catalog >>\nendobj\n%%EOF"
        );

        // Subir el nuevo documento.
        $response = $this->post(
            route('alumno.documentos.store'),
            [
                'tipo_documento' => 'ACTA_NACIMIENTO',
                'proceso' => 'INSCRIPCION',
                'archivo' => $nuevoPdf,
            ]
        );

        $response->assertSessionHasNoErrors();
        $response->assertSessionHas('success');

        // Debe quedar exactamente un documento para este alumno.
        $this->assertSame(
            1,
            \Illuminate\Support\Facades\DB::table('documentos')
                ->where('id_alumno', 1)
                ->count()
        );

        // Comprobar que el registro corresponde al nuevo PDF.
        $documento = \Illuminate\Support\Facades\DB::table('documentos')
            ->where('id_alumno', 1)
            ->first();

        $this->assertSame('PENDIENTE', $documento->estado_validacion);
        $this->assertNotSame($rutaAnterior, $documento->ruta_archivo);

        // El nuevo PDF debe existir en almacenamiento privado.
        \Illuminate\Support\Facades\Storage::disk('local')
            ->assertExists($documento->ruta_archivo);

        // El archivo rechazado anterior debe haberse eliminado.
        \Illuminate\Support\Facades\Storage::disk('local')
            ->assertMissing($rutaAnterior);
    }

    // Conservar el documento rechazado si falla el nuevo almacenamiento.
    public function test_reemplazo_fallido_conserva_documento_anterior(): void
    {
        $this->assertSame('sqlite', config('database.default'));
        $this->assertSame(
            ':memory:',
            config('database.connections.sqlite.database')
        );

        \Illuminate\Support\Facades\Storage::fake('local');

        \Illuminate\Support\Facades\Schema::create('alumnos', function ($table) {
            $table->id('id_alumno');
            $table->unsignedBigInteger('id_usuario')->unique();
            $table->string('matricula', 30)->unique();
        });

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

        \Illuminate\Support\Facades\DB::table('alumnos')->insert([
            'id_alumno' => 1,
            'id_usuario' => 101,
            'matricula' => 'MAT-PRUEBA-001',
        ]);

        $rutaAnterior = 'expedientes/MAT-PRUEBA-001/anterior.pdf';

        \Illuminate\Support\Facades\Storage::disk('local')->put(
            $rutaAnterior,
            "%PDF-1.4\n% Documento anterior\n"
        );

        \Illuminate\Support\Facades\DB::table('documentos')->insert([
            'id_documento' => 1,
            'id_alumno' => 1,
            'tipo_documento' => 'ACTA_NACIMIENTO',
            'proceso' => 'INSCRIPCION',
            'ruta_archivo' => $rutaAnterior,
            'estado_validacion' => 'RECHAZADO',
        ]);

        $usuario = new \App\Models\User();
        $usuario->id_usuario = 101;
        $usuario->id_rol = 3;
        $usuario->estado = 'ACTIVO';

        $this->actingAs($usuario);

        // Forzar que falle la operación de guardado.
        // Simular un fallo al guardar el nuevo PDF.
        // Conservar el disco simulado y su archivo anterior.
        $disco = \Illuminate\Support\Facades\Storage::disk('local');

        $discoSimulado = \Mockery::mock(
            \Illuminate\Filesystem\FilesystemAdapter::class
        )->makePartial();

        $discoSimulado->shouldReceive('putFileAs')
            ->andReturn(false);

        // Las demás operaciones se delegan al disco simulado original.
        $discoSimulado->shouldReceive('exists')
            ->andReturnUsing(fn ($ruta) => $disco->exists($ruta));

        $discoSimulado->shouldReceive('delete')
            ->andReturnUsing(fn ($ruta) => $disco->delete($ruta));

        $discoSimulado->shouldReceive('path')
            ->andReturnUsing(fn ($ruta) => $disco->path($ruta));

        \Illuminate\Support\Facades\Storage::set(
            'local',
            $discoSimulado
        );


        $nuevoPdf = \Illuminate\Http\UploadedFile::fake()->createWithContent(
            'nuevo.pdf',
            "%PDF-1.4\n1 0 obj\n<< /Type /Catalog >>\nendobj\n%%EOF"
        );

        // No dejamos que las excepciones interrumpan las verificaciones.
        $this->withoutExceptionHandling();

        try {
            $this->post(route('alumno.documentos.store'), [
                'tipo_documento' => 'ACTA_NACIMIENTO',
                'proceso' => 'INSCRIPCION',
                'archivo' => $nuevoPdf,
            ]);
        } catch (\Throwable $e) {
            // El resultado del fallo puede ser una excepción.
        }

        // El documento anterior debe seguir registrado.
        $this->assertDatabaseHas('documentos', [
            'id_documento' => 1,
            'id_alumno' => 1,
            'ruta_archivo' => $rutaAnterior,
            'estado_validacion' => 'RECHAZADO',
        ]);

        // El archivo anterior también debe seguir existiendo.
        \Illuminate\Support\Facades\Storage::disk('local')
            ->assertExists($rutaAnterior);
    }

    // Rechazar archivos PHP y evitar que se guarden como documentos.
    public function test_alumno_no_puede_subir_archivo_php(): void
    {
        // Asegurar que usamos SQLite en memoria.
        $this->assertSame('sqlite', config('database.default'));
        $this->assertSame(
            ':memory:',
            config('database.connections.sqlite.database')
        );

        // Simular almacenamiento privado y público.
        \Illuminate\Support\Facades\Storage::fake('local');
        \Illuminate\Support\Facades\Storage::fake('public');

        // Crear una tabla temporal de alumnos.
        \Illuminate\Support\Facades\Schema::create('alumnos', function ($table) {
            $table->id('id_alumno');
            $table->unsignedBigInteger('id_usuario')->unique();
            $table->string('matricula', 30)->unique();
        });

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

        // Registrar un alumno ficticio.
        \Illuminate\Support\Facades\DB::table('alumnos')->insert([
            'id_alumno' => 1,
            'id_usuario' => 101,
            'matricula' => 'MAT-PRUEBA-001',
        ]);

        // Simular una sesión de alumno activo.
        $usuario = new \App\Models\User();
        $usuario->id_usuario = 101;
        $usuario->id_rol = 3;
        $usuario->estado = 'ACTIVO';

        $this->actingAs($usuario);

        // Crear un archivo PHP ficticio.
        $archivo = \Illuminate\Http\UploadedFile::fake()->createWithContent(
            'archivo.php',
            '<?php echo "prueba"; ?>'
        );

        // Intentar subirlo como documento escolar.
        $response = $this->post(
            route('alumno.documentos.store'),
            [
                'tipo_documento' => 'ACTA_NACIMIENTO',
                'proceso' => 'INSCRIPCION',
                'archivo' => $archivo,
            ]
        );

        // Debe rechazarse por la validación del archivo.
        $response->assertSessionHasErrors('archivo');

        // No debe existir ningún documento registrado.
        $this->assertSame(
            0,
            \Illuminate\Support\Facades\DB::table('documentos')->count()
        );

        // No debe haberse almacenado ningún archivo.
        $this->assertSame(
            [],
            \Illuminate\Support\Facades\Storage::disk('local')
                ->allFiles('expedientes')
        );

        $this->assertSame(
            [],
            \Illuminate\Support\Facades\Storage::disk('public')
                ->allFiles('expedientes')
        );
    }

    // Impedir reemplazar documentos pendientes o aprobados.
    public function test_alumno_no_puede_reemplazar_documentos_pendientes_o_aprobados(): void
    {
        // Utilizar exclusivamente SQLite en memoria.
        $this->assertSame('sqlite', config('database.default'));
        $this->assertSame(
            ':memory:',
            config('database.connections.sqlite.database')
        );

        // Simular el almacenamiento privado.
        \Illuminate\Support\Facades\Storage::fake('local');

        // Crear las tablas necesarias.
        \Illuminate\Support\Facades\Schema::create('alumnos', function ($table) {
            $table->id('id_alumno');
            $table->unsignedBigInteger('id_usuario')->unique();
            $table->string('matricula', 30)->unique();
        });

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

        // Crear un alumno ficticio.
        \Illuminate\Support\Facades\DB::table('alumnos')->insert([
            'id_alumno' => 1,
            'id_usuario' => 101,
            'matricula' => 'MAT-PRUEBA-001',
        ]);

        // Simular un alumno autenticado.
        $usuario = new \App\Models\User();
        $usuario->id_usuario = 101;
        $usuario->id_rol = 3;
        $usuario->estado = 'ACTIVO';

        $this->actingAs($usuario);

        foreach (['PENDIENTE', 'APROBADO'] as $estado) {
            // Limpiar solo los datos ficticios de esta prueba.
            \Illuminate\Support\Facades\DB::table('documentos')->delete();

            $rutaAnterior = 'expedientes/MAT-PRUEBA-001/' .
                strtolower($estado) . '.pdf';

            \Illuminate\Support\Facades\Storage::disk('local')->put(
                $rutaAnterior,
                "%PDF-1.4\n% Documento de prueba\n"
            );

            // Registrar el documento existente.
            \Illuminate\Support\Facades\DB::table('documentos')->insert([
                'id_documento' => 1,
                'id_alumno' => 1,
                'tipo_documento' => 'ACTA_NACIMIENTO',
                'proceso' => 'INSCRIPCION',
                'ruta_archivo' => $rutaAnterior,
                'estado_validacion' => $estado,
            ]);

            // Intentar subir un PDF de reemplazo.
            $nuevoPdf = \Illuminate\Http\UploadedFile::fake()
                ->createWithContent(
                    'nuevo.pdf',
                    "%PDF-1.4\n1 0 obj\n<< /Type /Catalog >>\nendobj\n%%EOF"
                );

            $response = $this->post(
                route('alumno.documentos.store'),
                [
                    'tipo_documento' => 'ACTA_NACIMIENTO',
                    'proceso' => 'INSCRIPCION',
                    'archivo' => $nuevoPdf,
                ]
            );

            // El reemplazo debe rechazarse.
            $response->assertSessionHasErrors('archivo');

            // El registro anterior debe permanecer intacto.
            $this->assertDatabaseHas('documentos', [
                'id_documento' => 1,
                'id_alumno' => 1,
                'ruta_archivo' => $rutaAnterior,
                'estado_validacion' => $estado,
            ]);

            $this->assertSame(
                1,
                \Illuminate\Support\Facades\DB::table('documentos')->count()
            );

            // El PDF anterior debe conservarse.
            \Illuminate\Support\Facades\Storage::disk('local')
                ->assertExists($rutaAnterior);

            // No debe haberse guardado otro archivo.
            $this->assertCount(
                1,
                \Illuminate\Support\Facades\Storage::disk('local')
                    ->allFiles('expedientes')
            );

            // Retirar el archivo ficticio para la siguiente iteración.
            \Illuminate\Support\Facades\Storage::disk('local')
                ->delete($rutaAnterior);
        }
    }

    // Impedir que cuentas inactivas visualicen documentos privados.
    public function test_usuario_inactivo_no_puede_visualizar_documento_privado(): void
    {
        // Garantizar el uso de SQLite en memoria.
        $this->assertSame('sqlite', config('database.default'));
        $this->assertSame(
            ':memory:',
            config('database.connections.sqlite.database')
        );

        // Crear la tabla temporal de documentos.
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

        // Registrar un documento ficticio.
        \Illuminate\Support\Facades\DB::table('documentos')->insert([
            'id_documento' => 1,
            'id_alumno' => 1,
            'tipo_documento' => 'ACTA_NACIMIENTO',
            'proceso' => 'INSCRIPCION',
            'ruta_archivo' => 'expedientes/prueba/acta.pdf',
            'estado_validacion' => 'PENDIENTE',
        ]);

        // Comprobar tanto un administrador como un alumno inactivos.
        foreach ([1, 3] as $rol) {
            $usuario = new \App\Models\User();
            $usuario->id_usuario = 100 + $rol;
            $usuario->id_rol = $rol;
            $usuario->estado = 'INACTIVO';

            $this->actingAs($usuario);

            $response = $this->get(
                route('documentos.ver', [
                    'id_documento' => 1,
                ])
            );

            // El acceso debe estar prohibido.
            $response->assertForbidden();
        }

        // El documento debe permanecer intacto.
        $this->assertDatabaseHas('documentos', [
            'id_documento' => 1,
            'ruta_archivo' => 'expedientes/prueba/acta.pdf',
            'estado_validacion' => 'PENDIENTE',
        ]);
    }

    // Devolver 404 cuando un PDF privado no existe físicamente.
    public function test_documento_sin_archivo_devuelve_404(): void
    {
        // Garantizar que las pruebas usan SQLite en memoria.
        $this->assertSame('sqlite', config('database.default'));
        $this->assertSame(
            ':memory:',
            config('database.connections.sqlite.database')
        );

        // Simular almacenamiento privado, sin archivos reales.
        \Illuminate\Support\Facades\Storage::fake('local');

        // Crear la tabla temporal de documentos.
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

        // Registrar un documento cuya ruta apunta a un PDF inexistente.
        $rutaInexistente = 'expedientes/prueba/no-existe.pdf';

        \Illuminate\Support\Facades\DB::table('documentos')->insert([
            'id_documento' => 1,
            'id_alumno' => 1,
            'tipo_documento' => 'ACTA_NACIMIENTO',
            'proceso' => 'INSCRIPCION',
            'ruta_archivo' => $rutaInexistente,
            'estado_validacion' => 'PENDIENTE',
        ]);

        // Simular un administrador activo.
        $administrador = new \App\Models\User();
        $administrador->id_usuario = 996;
        $administrador->id_rol = 1;
        $administrador->estado = 'ACTIVO';

        $this->actingAs($administrador);

        // Solicitar el documento cuyo archivo no existe.
        $response = $this->get(
            route('documentos.ver', [
                'id_documento' => 1,
            ])
        );

        // Debe responder 404, no 200 ni 500.
        $response->assertNotFound();

        // El documento debe continuar registrado.
        $this->assertDatabaseHas('documentos', [
            'id_documento' => 1,
            'ruta_archivo' => $rutaInexistente,
            'estado_validacion' => 'PENDIENTE',
        ]);

        // Confirmar que no se creó ningún PDF.
        \Illuminate\Support\Facades\Storage::disk('local')
            ->assertMissing($rutaInexistente);
    }

    // Verificar que un conflicto de unicidad no cause pérdida de documentos.
    public function test_conflicto_de_duplicidad_conserva_documento_existente(): void
    {
        $this->assertSame('sqlite', config('database.default'));
        $this->assertSame(
            ':memory:',
            config('database.connections.sqlite.database')
        );

        \Illuminate\Support\Facades\Storage::fake('local');

        \Illuminate\Support\Facades\Schema::create('alumnos', function ($table) {
            $table->id('id_alumno');
            $table->unsignedBigInteger('id_usuario')->unique();
            $table->string('matricula', 30)->unique();
        });

        \Illuminate\Support\Facades\Schema::create('documentos', function ($table) {
            $table->id('id_documento');
            $table->unsignedBigInteger('id_alumno');
            $table->string('tipo_documento', 100);
            $table->string('proceso');
            $table->string('ruta_archivo');
            $table->string('estado_validacion')->default('PENDIENTE');
            $table->text('observaciones')->nullable();
            $table->timestamps();

            $table->unique(
                ['id_alumno', 'tipo_documento', 'proceso'],
                'documentos_alumno_tipo_proceso_unique'
            );
        });

        \Illuminate\Support\Facades\DB::table('alumnos')->insert([
            'id_alumno' => 1,
            'id_usuario' => 101,
            'matricula' => 'MAT-PRUEBA-001',
        ]);

        // Crear un documento existente de otro alumno.
        \Illuminate\Support\Facades\DB::table('documentos')->insert([
            'id_documento' => 1,
            'id_alumno' => 1,
            'tipo_documento' => 'ACTA_NACIMIENTO',
            'proceso' => 'INSCRIPCION',
            'ruta_archivo' => 'expedientes/MAT-PRUEBA-001/original.pdf',
            'estado_validacion' => 'PENDIENTE',
        ]);

        // Confirmar que SQLite impide insertar un registro duplicado.
        try {
            \Illuminate\Support\Facades\DB::table('documentos')->insert([
                'id_alumno' => 1,
                'tipo_documento' => 'ACTA_NACIMIENTO',
                'proceso' => 'INSCRIPCION',
                'ruta_archivo' => 'expedientes/MAT-PRUEBA-001/duplicado.pdf',
                'estado_validacion' => 'PENDIENTE',
            ]);

            $this->fail('La base de datos permitió un documento duplicado.');
        } catch (\Illuminate\Database\UniqueConstraintViolationException $e) {
            // La restricción única debe impedir la inserción.
            $this->assertTrue(true);
        }

        // Comprobar que permanece el documento original.
        $this->assertDatabaseHas('documentos', [
            'id_documento' => 1,
            'id_alumno' => 1,
            'ruta_archivo' => 'expedientes/MAT-PRUEBA-001/original.pdf',
            'estado_validacion' => 'PENDIENTE',
        ]);

        $this->assertSame(
            1,
            \Illuminate\Support\Facades\DB::table('documentos')->count()
        );
    }

    // Verificar la reversión de una transacción ante un conflicto de unicidad.
    public function test_conflicto_sqlite_revierte_transaccion_y_limpia_archivo_nuevo(): void
    {
        // Garantizar el uso de SQLite en memoria.
        $this->assertSame('sqlite', config('database.default'));
        $this->assertSame(
            ':memory:',
            config('database.connections.sqlite.database')
        );

        // Simular el almacenamiento privado.
        \Illuminate\Support\Facades\Storage::fake('local');

        // Crear las tablas temporales necesarias.
        \Illuminate\Support\Facades\Schema::create('alumnos', function ($table) {
            $table->id('id_alumno');
            $table->unsignedBigInteger('id_usuario')->unique();
            $table->string('matricula', 30)->unique();
        });

        \Illuminate\Support\Facades\Schema::create('documentos', function ($table) {
            $table->id('id_documento');
            $table->unsignedBigInteger('id_alumno');
            $table->string('tipo_documento', 100);
            $table->string('proceso');
            $table->string('ruta_archivo');
            $table->string('estado_validacion')->default('PENDIENTE');
            $table->text('observaciones')->nullable();
            $table->timestamps();

            $table->unique(
                ['id_alumno', 'tipo_documento', 'proceso'],
                'documentos_alumno_tipo_proceso_unique'
            );
        });

        // Crear un alumno ficticio.
        \Illuminate\Support\Facades\DB::table('alumnos')->insert([
            'id_alumno' => 1,
            'id_usuario' => 101,
            'matricula' => 'MAT-PRUEBA-001',
        ]);

        // Simular una sesión de alumno activo.
        $usuario = new \App\Models\User();
        $usuario->id_usuario = 101;
        $usuario->id_rol = 3;
        $usuario->estado = 'ACTIVO';

        $this->actingAs($usuario);

        $rutaOriginal = 'expedientes/MAT-PRUEBA-001/original.pdf';

        // Simular que aparece un registro competidor
        // antes de guardar el documento de la solicitud.
        \App\Models\Documento::creating(function ($documento) use ($rutaOriginal) {
            \Illuminate\Support\Facades\Storage::disk('local')->put(
                $rutaOriginal,
                "%PDF-1.4\n% Documento original de prueba\n"
            );

            \Illuminate\Support\Facades\DB::table('documentos')->insert([
                'id_alumno' => $documento->id_alumno,
                'tipo_documento' => $documento->tipo_documento,
                'proceso' => $documento->proceso,
                'ruta_archivo' => $rutaOriginal,
                'estado_validacion' => 'PENDIENTE',
            ]);
        });

        // Preparar un PDF ficticio.
        $nuevoPdf = \Illuminate\Http\UploadedFile::fake()->createWithContent(
            'nuevo.pdf',
            "%PDF-1.4\n1 0 obj\n<< /Type /Catalog >>\nendobj\n%%EOF"
        );

        // Permitir capturar la excepción de unicidad.
        $this->withoutExceptionHandling();

        $excepcion = null;

        try {
            $this->post(
                route('alumno.documentos.store'),
                [
                    'tipo_documento' => 'ACTA_NACIMIENTO',
                    'proceso' => 'INSCRIPCION',
                    'archivo' => $nuevoPdf,
                ]
            );
        } catch (\Illuminate\Database\UniqueConstraintViolationException $e) {
            $excepcion = $e;
        }

        // Confirmar que ocurrió un conflicto de unicidad.
        $this->assertInstanceOf(
            \Illuminate\Database\UniqueConstraintViolationException::class,
            $excepcion
        );

        // La transacción SQLite debe revertir ambas inserciones.
        $this->assertDatabaseCount('documentos', 0);

        // No deben quedar transacciones abiertas.
        $this->assertSame(
            0,
            \Illuminate\Support\Facades\DB::transactionLevel()
        );

        // El archivo original simulado permanece.
        // El archivo de la solicitud fallida debe haberse eliminado.
        $this->assertSame(
            [$rutaOriginal],
            \Illuminate\Support\Facades\Storage::disk('local')
                ->allFiles('expedientes')
        );
    }

    // Comprobar el manejo del error 1205 durante un reemplazo.
    public function test_error_1205_conserva_documento_y_muestra_mensaje(): void
    {
        // Garantizar que no usamos la base de datos real.
        $this->assertSame('sqlite', config('database.default'));
        $this->assertSame(
            ':memory:',
            config('database.connections.sqlite.database')
        );

        // Utilizar almacenamiento privado simulado.
        \Illuminate\Support\Facades\Storage::fake('local');

        // Crear las tablas temporales.
        \Illuminate\Support\Facades\Schema::create('alumnos', function ($table) {
            $table->id('id_alumno');
            $table->unsignedBigInteger('id_usuario')->unique();
            $table->string('matricula', 30)->unique();
        });

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

        // Crear un alumno ficticio.
        \Illuminate\Support\Facades\DB::table('alumnos')->insert([
            'id_alumno' => 1,
            'id_usuario' => 101,
            'matricula' => 'MAT-PRUEBA-001',
        ]);

        // Crear el PDF anterior.
        $rutaAnterior = 'expedientes/MAT-PRUEBA-001/original.pdf';

        \Illuminate\Support\Facades\Storage::disk('local')->put(
            $rutaAnterior,
            "%PDF-1.4\n% Documento anterior de prueba\n"
        );

        // Registrar el documento rechazado.
        \Illuminate\Support\Facades\DB::table('documentos')->insert([
            'id_documento' => 1,
            'id_alumno' => 1,
            'tipo_documento' => 'ACTA_NACIMIENTO',
            'proceso' => 'INSCRIPCION',
            'ruta_archivo' => $rutaAnterior,
            'estado_validacion' => 'RECHAZADO',
        ]);

        // Simular un alumno activo.
        $usuario = new \App\Models\User();
        $usuario->id_usuario = 101;
        $usuario->id_rol = 3;
        $usuario->estado = 'ACTIVO';

        $this->actingAs($usuario);

        // Simular el error 1205 cuando Eloquent intenta
        // actualizar el documento durante la transacción.
        \App\Models\Documento::updating(function () {
            $pdo = new \PDOException(
                'Lock wait timeout exceeded'
            );

            $pdo->errorInfo = [
                'HY000',
                1205,
                'Lock wait timeout exceeded',
            ];

            throw new \Illuminate\Database\QueryException(
                'mysql',
                'UPDATE documentos SET ruta_archivo = ?',
                [],
                $pdo
            );
        });

        // Preparar el nuevo archivo ficticio.
        $nuevoPdf = \Illuminate\Http\UploadedFile::fake()
            ->createWithContent(
                'nuevo.pdf',
                "%PDF-1.4\n1 0 obj\n<< /Type /Catalog >>\nendobj\n%%EOF"
            );

        // Intentar reemplazar el documento.
        $response = $this->post(
            route('alumno.documentos.store'),
            [
                'tipo_documento' => 'ACTA_NACIMIENTO',
                'proceso' => 'INSCRIPCION',
                'archivo' => $nuevoPdf,
            ]
        );

        // El alumno debe recibir un error comprensible.
        $response->assertSessionHasErrors([
            'archivo' => 'El documento está siendo procesado por otra solicitud. Intenta nuevamente en unos momentos.',
        ]);

        // El documento original debe permanecer intacto.
        $this->assertDatabaseHas('documentos', [
            'id_documento' => 1,
            'id_alumno' => 1,
            'ruta_archivo' => $rutaAnterior,
            'estado_validacion' => 'RECHAZADO',
        ]);

        // No deben existir registros adicionales.
        $this->assertDatabaseCount('documentos', 1);

        // Debe conservarse el archivo original.
        \Illuminate\Support\Facades\Storage::disk('local')
            ->assertExists($rutaAnterior);

        // No debe quedar almacenado el PDF nuevo.
        $this->assertSame(
            [$rutaAnterior],
            \Illuminate\Support\Facades\Storage::disk('local')
                ->allFiles('expedientes')
        );

        // La transacción debe haber terminado.
        $this->assertSame(
            0,
            \Illuminate\Support\Facades\DB::transactionLevel()
        );
    }

    // Verificar que Laravel reintente una transacción ante un bloqueo temporal.
    public function test_laravel_reintenta_transaccion_ante_error_temporal(): void
    {
        // Garantizar que utilizamos SQLite en memoria.
        $this->assertSame('sqlite', config('database.default'));
        $this->assertSame(
            ':memory:',
            config('database.connections.sqlite.database')
        );

        $intentos = 0;

        // Simular un error de concurrencia en el primer intento.
        $resultado = \Illuminate\Support\Facades\DB::transaction(
            function () use (&$intentos) {
                $intentos++;

                if ($intentos === 1) {
                    throw new \RuntimeException('database is locked');
                }

                return 'Operacion completada';
            },
            3
        );

        // La transacción debe completarse correctamente.
        $this->assertSame('Operacion completada', $resultado);

        // Laravel debe haber realizado exactamente dos intentos.
        $this->assertSame(2, $intentos);

        // No debe quedar ninguna transacción abierta.
        $this->assertSame(
            0,
            \Illuminate\Support\Facades\DB::transactionLevel()
        );
    }

}
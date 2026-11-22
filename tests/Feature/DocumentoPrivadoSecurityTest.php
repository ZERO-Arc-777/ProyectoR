<?php

namespace Tests\Feature;

use Tests\TestCase;

class DocumentoPrivadoSecurityTest extends TestCase
{
    private function crearTablaConfiguraciones(string $periodo = 'Agosto 2026 - Diciembre 2026'): void
    {
        \Illuminate\Support\Facades\Schema::create('configuraciones', function ($table) {
            $table->id('id_configuracion');
            $table->string('clave')->unique();
            $table->string('valor');
            $table->timestamps();
        });

        \Illuminate\Support\Facades\DB::table('configuraciones')->insert([
            'clave' => 'periodo_actual',
            'valor' => $periodo,
        ]);
    }

    private function crearTablaDocumentos(bool $conIndiceUnico = false): void
    {
        \Illuminate\Support\Facades\Schema::create('documentos', function ($table) use ($conIndiceUnico) {
            $table->id('id_documento');
            $table->unsignedBigInteger('id_alumno');
            $table->string('tipo_documento', 100);
            $table->string('proceso');
            $table->string('periodo', 255)->default('No definido');
            $table->string('ruta_archivo');
            $table->string('estado_validacion')->default('PENDIENTE');
            $table->text('observaciones')->nullable();
            $table->timestamps();

            if ($conIndiceUnico) {
                $table->unique(
                    ['id_alumno', 'tipo_documento', 'proceso', 'periodo'],
                    'documentos_alumno_tipo_proceso_periodo_unique'
                );
            }
        });
    }

    // Impedir que visitantes consulten documentos privados.
    public function test_visitante_no_puede_visualizar_documento_privado(): void
    {
        $this->assertGuest();

        $response = $this->get(
            route('documentos.ver', [
                'id_documento' => 1,
            ])
        );

        $response->assertRedirect(route('login'));
    }

    // Impedir que docentes consulten documentos de alumnos.
    public function test_docente_no_puede_visualizar_documento_privado(): void
    {
        $this->assertSame('sqlite', config('database.default'));
        $this->assertSame(':memory:', config('database.connections.sqlite.database'));

        $this->crearTablaDocumentos();

        \Illuminate\Support\Facades\DB::table('documentos')->insert([
            'id_documento' => 1,
            'id_alumno' => 1,
            'tipo_documento' => 'Acta de nacimiento',
            'proceso' => 'INSCRIPCION',
            'periodo' => 'Agosto 2026 - Diciembre 2026',
            'ruta_archivo' => 'expedientes/prueba/acta.pdf',
            'estado_validacion' => 'PENDIENTE',
        ]);

        $docente = new \App\Models\User();
        $docente->id_usuario = 998;
        $docente->id_rol = 2;
        $docente->estado = 'ACTIVO';

        $this->actingAs($docente);

        $response = $this->get(
            route('documentos.ver', [
                'id_documento' => 1,
            ])
        );

        $response->assertForbidden();

        $this->assertDatabaseHas('documentos', [
            'id_documento' => 1,
            'id_alumno' => 1,
            'estado_validacion' => 'PENDIENTE',
        ]);
    }

    // Impedir que un alumno consulte documentos de otro estudiante.
    public function test_alumno_no_puede_visualizar_documento_ajeno(): void
    {
        $this->assertSame('sqlite', config('database.default'));
        $this->assertSame(':memory:', config('database.connections.sqlite.database'));

        \Illuminate\Support\Facades\Schema::create('alumnos', function ($table) {
            $table->id('id_alumno');
            $table->unsignedBigInteger('id_usuario')->unique();
            $table->string('matricula', 30)->unique();
        });

        $this->crearTablaDocumentos();

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

        \Illuminate\Support\Facades\DB::table('documentos')->insert([
            'id_documento' => 1,
            'id_alumno' => 2,
            'tipo_documento' => 'Acta de nacimiento',
            'proceso' => 'INSCRIPCION',
            'periodo' => 'Agosto 2026 - Diciembre 2026',
            'ruta_archivo' => 'expedientes/prueba/acta.pdf',
            'estado_validacion' => 'PENDIENTE',
        ]);

        $alumno = new \App\Models\User();
        $alumno->id_usuario = 101;
        $alumno->id_rol = 3;
        $alumno->estado = 'ACTIVO';

        $this->actingAs($alumno);

        $response = $this->get(
            route('documentos.ver', [
                'id_documento' => 1,
            ])
        );

        $response->assertForbidden();

        $this->assertDatabaseHas('documentos', [
            'id_documento' => 1,
            'id_alumno' => 2,
            'estado_validacion' => 'PENDIENTE',
        ]);
    }

    // Permitir que un alumno consulte su propio documento privado.
    public function test_alumno_puede_visualizar_su_propio_documento(): void
    {
        $this->assertSame('sqlite', config('database.default'));
        $this->assertSame(':memory:', config('database.connections.sqlite.database'));

        \Illuminate\Support\Facades\Storage::fake('local');

        \Illuminate\Support\Facades\Schema::create('alumnos', function ($table) {
            $table->id('id_alumno');
            $table->unsignedBigInteger('id_usuario')->unique();
            $table->string('matricula', 30)->unique();
        });

        $this->crearTablaDocumentos();

        \Illuminate\Support\Facades\DB::table('alumnos')->insert([
            'id_alumno' => 1,
            'id_usuario' => 101,
            'matricula' => 'MAT-PRUEBA-001',
        ]);

        $ruta = 'expedientes/MAT-PRUEBA-001/prueba.pdf';

        \Illuminate\Support\Facades\Storage::disk('local')->put(
            $ruta,
            "%PDF-1.4\n% Documento de prueba\n"
        );

        \Illuminate\Support\Facades\DB::table('documentos')->insert([
            'id_documento' => 1,
            'id_alumno' => 1,
            'tipo_documento' => 'Acta de nacimiento',
            'proceso' => 'INSCRIPCION',
            'periodo' => 'Agosto 2026 - Diciembre 2026',
            'ruta_archivo' => $ruta,
            'estado_validacion' => 'PENDIENTE',
        ]);

        $alumno = new \App\Models\User();
        $alumno->id_usuario = 101;
        $alumno->id_rol = 3;
        $alumno->estado = 'ACTIVO';

        $this->actingAs($alumno);

        $response = $this->get(
            route('documentos.ver', [
                'id_documento' => 1,
            ])
        );

        $response->assertOk();
        $response->assertHeader('Content-Type', 'application/pdf');
        $response->assertHeader('X-Content-Type-Options', 'nosniff');

        $this->assertDatabaseHas('documentos', [
            'id_documento' => 1,
            'id_alumno' => 1,
            'ruta_archivo' => $ruta,
        ]);
    }

    // Permitir que un administrador activo revise documentos privados.
    public function test_administrador_puede_visualizar_documento_privado(): void
    {
        $this->assertSame('sqlite', config('database.default'));
        $this->assertSame(':memory:', config('database.connections.sqlite.database'));

        \Illuminate\Support\Facades\Storage::fake('local');

        $this->crearTablaDocumentos();

        $ruta = 'expedientes/MAT-PRUEBA-001/acta.pdf';

        \Illuminate\Support\Facades\Storage::disk('local')->put(
            $ruta,
            "%PDF-1.4\n% Documento de prueba\n"
        );

        \Illuminate\Support\Facades\DB::table('documentos')->insert([
            'id_documento' => 1,
            'id_alumno' => 1,
            'tipo_documento' => 'Acta de nacimiento',
            'proceso' => 'INSCRIPCION',
            'periodo' => 'Agosto 2026 - Diciembre 2026',
            'ruta_archivo' => $ruta,
            'estado_validacion' => 'PENDIENTE',
        ]);

        $administrador = new \App\Models\User();
        $administrador->id_usuario = 996;
        $administrador->id_rol = 1;
        $administrador->estado = 'ACTIVO';

        $this->actingAs($administrador);

        $response = $this->get(
            route('documentos.ver', [
                'id_documento' => 1,
            ])
        );

        $response->assertOk();
        $response->assertHeader('Content-Type', 'application/pdf');
        $response->assertHeader('X-Content-Type-Options', 'nosniff');

        $this->assertDatabaseHas('documentos', [
            'id_documento' => 1,
            'id_alumno' => 1,
            'ruta_archivo' => $ruta,
        ]);
    }

    // Verificar que los PDF se guarden en almacenamiento privado.
    public function test_alumno_sube_pdf_al_almacenamiento_privado(): void
    {
        $this->assertSame('sqlite', config('database.default'));
        $this->assertSame(':memory:', config('database.connections.sqlite.database'));

        \Illuminate\Support\Facades\Storage::fake('local');
        \Illuminate\Support\Facades\Storage::fake('public');

        $this->crearTablaConfiguraciones('Agosto 2026 - Diciembre 2026');

        \Illuminate\Support\Facades\Schema::create('alumnos', function ($table) {
            $table->id('id_alumno');
            $table->unsignedBigInteger('id_usuario')->unique();
            $table->string('matricula', 30)->unique();
        });

        $this->crearTablaDocumentos(true);

        \Illuminate\Support\Facades\DB::table('alumnos')->insert([
            'id_alumno' => 1,
            'id_usuario' => 101,
            'matricula' => 'MAT-PRUEBA-001',
        ]);

        $usuario = new \App\Models\User();
        $usuario->id_usuario = 101;
        $usuario->id_rol = 3;
        $usuario->estado = 'ACTIVO';

        $this->actingAs($usuario);

        $pdf = \Illuminate\Http\UploadedFile::fake()->createWithContent(
            'acta.pdf',
            "%PDF-1.4\n1 0 obj\n<< /Type /Catalog >>\nendobj\n%%EOF"
        );

        $response = $this->post(
            route('alumno.documentos.store'),
            [
                'tipo_documento' => 'ACTA_NACIMIENTO',
                'proceso' => 'INSCRIPCION',
                'archivo' => $pdf,
            ]
        );

        $response->assertSessionHasNoErrors();
        $response->assertSessionHas('success');

        $documento = \Illuminate\Support\Facades\DB::table('documentos')
            ->where('id_alumno', 1)
            ->first();

        $this->assertNotNull($documento);
        $this->assertSame('PENDIENTE', $documento->estado_validacion);
        $this->assertSame('Agosto 2026 - Diciembre 2026', $documento->periodo);

        \Illuminate\Support\Facades\Storage::disk('local')
            ->assertExists($documento->ruta_archivo);

        \Illuminate\Support\Facades\Storage::disk('public')
            ->assertMissing($documento->ruta_archivo);
    }

    // Verificar el reemplazo de un documento rechazado.
    public function test_alumno_puede_reemplazar_documento_rechazado(): void
    {
        $this->assertSame('sqlite', config('database.default'));
        $this->assertSame(':memory:', config('database.connections.sqlite.database'));

        \Illuminate\Support\Facades\Storage::fake('local');

        $periodo = 'Agosto 2026 - Diciembre 2026';
        $this->crearTablaConfiguraciones($periodo);

        \Illuminate\Support\Facades\Schema::create('alumnos', function ($table) {
            $table->id('id_alumno');
            $table->unsignedBigInteger('id_usuario')->unique();
            $table->string('matricula', 30)->unique();
        });

        $this->crearTablaDocumentos(true);

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
            'periodo' => $periodo,
            'ruta_archivo' => $rutaAnterior,
            'estado_validacion' => 'RECHAZADO',
        ]);

        $usuario = new \App\Models\User();
        $usuario->id_usuario = 101;
        $usuario->id_rol = 3;
        $usuario->estado = 'ACTIVO';

        $this->actingAs($usuario);

        $nuevoPdf = \Illuminate\Http\UploadedFile::fake()->createWithContent(
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

        $response->assertSessionHasNoErrors();
        $response->assertSessionHas('success');

        $this->assertSame(
            1,
            \Illuminate\Support\Facades\DB::table('documentos')
                ->where('id_alumno', 1)
                ->count()
        );

        $documento = \Illuminate\Support\Facades\DB::table('documentos')
            ->where('id_alumno', 1)
            ->first();

        $this->assertSame('PENDIENTE', $documento->estado_validacion);
        $this->assertNotSame($rutaAnterior, $documento->ruta_archivo);

        \Illuminate\Support\Facades\Storage::disk('local')
            ->assertExists($documento->ruta_archivo);

        \Illuminate\Support\Facades\Storage::disk('local')
            ->assertMissing($rutaAnterior);
    }

    // Conservar el documento rechazado si falla el nuevo almacenamiento.
    public function test_reemplazo_fallido_conserva_documento_anterior(): void
    {
        $this->assertSame('sqlite', config('database.default'));
        $this->assertSame(':memory:', config('database.connections.sqlite.database'));

        \Illuminate\Support\Facades\Storage::fake('local');

        $periodo = 'Agosto 2026 - Diciembre 2026';
        $this->crearTablaConfiguraciones($periodo);

        \Illuminate\Support\Facades\Schema::create('alumnos', function ($table) {
            $table->id('id_alumno');
            $table->unsignedBigInteger('id_usuario')->unique();
            $table->string('matricula', 30)->unique();
        });

        $this->crearTablaDocumentos(true);

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
            'periodo' => $periodo,
            'ruta_archivo' => $rutaAnterior,
            'estado_validacion' => 'RECHAZADO',
        ]);

        $usuario = new \App\Models\User();
        $usuario->id_usuario = 101;
        $usuario->id_rol = 3;
        $usuario->estado = 'ACTIVO';

        $this->actingAs($usuario);

        $disco = \Illuminate\Support\Facades\Storage::disk('local');

        $discoSimulado = \Mockery::mock(
            \Illuminate\Filesystem\FilesystemAdapter::class
        )->makePartial();

        $discoSimulado->shouldReceive('putFileAs')
            ->andReturn(false);

        $discoSimulado->shouldReceive('exists')
            ->andReturnUsing(fn ($ruta) => $disco->exists($ruta));

        $discoSimulado->shouldReceive('delete')
            ->andReturnUsing(fn ($ruta) => $disco->delete($ruta));

        $discoSimulado->shouldReceive('path')
            ->andReturnUsing(fn ($ruta) => $disco->path($ruta));

        \Illuminate\Support\Facades\Storage::set('local', $discoSimulado);

        $nuevoPdf = \Illuminate\Http\UploadedFile::fake()->createWithContent(
            'nuevo.pdf',
            "%PDF-1.4\n1 0 obj\n<< /Type /Catalog >>\nendobj\n%%EOF"
        );

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

        $this->assertDatabaseHas('documentos', [
            'id_documento' => 1,
            'id_alumno' => 1,
            'ruta_archivo' => $rutaAnterior,
            'estado_validacion' => 'RECHAZADO',
        ]);

        \Illuminate\Support\Facades\Storage::disk('local')
            ->assertExists($rutaAnterior);
    }

    // Rechazar archivos PHP y evitar que se guarden como documentos.
    public function test_alumno_no_puede_subir_archivo_php(): void
    {
        $this->assertSame('sqlite', config('database.default'));
        $this->assertSame(':memory:', config('database.connections.sqlite.database'));

        \Illuminate\Support\Facades\Storage::fake('local');
        \Illuminate\Support\Facades\Storage::fake('public');

        $this->crearTablaConfiguraciones();

        \Illuminate\Support\Facades\Schema::create('alumnos', function ($table) {
            $table->id('id_alumno');
            $table->unsignedBigInteger('id_usuario')->unique();
            $table->string('matricula', 30)->unique();
        });

        $this->crearTablaDocumentos(true);

        \Illuminate\Support\Facades\DB::table('alumnos')->insert([
            'id_alumno' => 1,
            'id_usuario' => 101,
            'matricula' => 'MAT-PRUEBA-001',
        ]);

        $usuario = new \App\Models\User();
        $usuario->id_usuario = 101;
        $usuario->id_rol = 3;
        $usuario->estado = 'ACTIVO';

        $this->actingAs($usuario);

        $archivo = \Illuminate\Http\UploadedFile::fake()->createWithContent(
            'archivo.php',
            '<?php echo "prueba"; ?>'
        );

        $response = $this->post(
            route('alumno.documentos.store'),
            [
                'tipo_documento' => 'ACTA_NACIMIENTO',
                'proceso' => 'INSCRIPCION',
                'archivo' => $archivo,
            ]
        );

        $response->assertSessionHasErrors('archivo');

        $this->assertSame(
            0,
            \Illuminate\Support\Facades\DB::table('documentos')->count()
        );

        $this->assertSame(
            [],
            \Illuminate\Support\Facades\Storage::disk('local')->allFiles('expedientes')
        );

        $this->assertSame(
            [],
            \Illuminate\Support\Facades\Storage::disk('public')->allFiles('expedientes')
        );
    }

    // Impedir reemplazar documentos pendientes o aprobados del mismo periodo.
    public function test_alumno_no_puede_reemplazar_documentos_pendientes_o_aprobados(): void
    {
        $this->assertSame('sqlite', config('database.default'));
        $this->assertSame(':memory:', config('database.connections.sqlite.database'));

        \Illuminate\Support\Facades\Storage::fake('local');

        $periodo = 'Agosto 2026 - Diciembre 2026';
        $this->crearTablaConfiguraciones($periodo);

        \Illuminate\Support\Facades\Schema::create('alumnos', function ($table) {
            $table->id('id_alumno');
            $table->unsignedBigInteger('id_usuario')->unique();
            $table->string('matricula', 30)->unique();
        });

        $this->crearTablaDocumentos(true);

        \Illuminate\Support\Facades\DB::table('alumnos')->insert([
            'id_alumno' => 1,
            'id_usuario' => 101,
            'matricula' => 'MAT-PRUEBA-001',
        ]);

        $usuario = new \App\Models\User();
        $usuario->id_usuario = 101;
        $usuario->id_rol = 3;
        $usuario->estado = 'ACTIVO';

        $this->actingAs($usuario);

        foreach (['PENDIENTE', 'APROBADO'] as $estado) {
            \Illuminate\Support\Facades\DB::table('documentos')->delete();

            $rutaAnterior = 'expedientes/MAT-PRUEBA-001/' . strtolower($estado) . '.pdf';

            \Illuminate\Support\Facades\Storage::disk('local')->put(
                $rutaAnterior,
                "%PDF-1.4\n% Documento de prueba\n"
            );

            \Illuminate\Support\Facades\DB::table('documentos')->insert([
                'id_documento' => 1,
                'id_alumno' => 1,
                'tipo_documento' => 'ACTA_NACIMIENTO',
                'proceso' => 'INSCRIPCION',
                'periodo' => $periodo,
                'ruta_archivo' => $rutaAnterior,
                'estado_validacion' => $estado,
            ]);

            $nuevoPdf = \Illuminate\Http\UploadedFile::fake()->createWithContent(
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

            $response->assertSessionHasErrors('archivo');

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

            \Illuminate\Support\Facades\Storage::disk('local')
                ->assertExists($rutaAnterior);

            $this->assertCount(
                1,
                \Illuminate\Support\Facades\Storage::disk('local')->allFiles('expedientes')
            );

            \Illuminate\Support\Facades\Storage::disk('local')->delete($rutaAnterior);
        }
    }

    // Impedir que cuentas inactivas visualicen documentos privados.
    public function test_usuario_inactivo_no_puede_visualizar_documento_privado(): void
    {
        $this->assertSame('sqlite', config('database.default'));
        $this->assertSame(':memory:', config('database.connections.sqlite.database'));

        $this->crearTablaDocumentos();

        \Illuminate\Support\Facades\DB::table('documentos')->insert([
            'id_documento' => 1,
            'id_alumno' => 1,
            'tipo_documento' => 'ACTA_NACIMIENTO',
            'proceso' => 'INSCRIPCION',
            'periodo' => 'Agosto 2026 - Diciembre 2026',
            'ruta_archivo' => 'expedientes/prueba/acta.pdf',
            'estado_validacion' => 'PENDIENTE',
        ]);

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

            $response->assertForbidden();
        }

        $this->assertDatabaseHas('documentos', [
            'id_documento' => 1,
            'ruta_archivo' => 'expedientes/prueba/acta.pdf',
            'estado_validacion' => 'PENDIENTE',
        ]);
    }

    // Devolver 404 cuando un PDF privado no existe físicamente.
    public function test_documento_sin_archivo_devuelve_404(): void
    {
        $this->assertSame('sqlite', config('database.default'));
        $this->assertSame(':memory:', config('database.connections.sqlite.database'));

        \Illuminate\Support\Facades\Storage::fake('local');

        $this->crearTablaDocumentos();

        $rutaInexistente = 'expedientes/prueba/no-existe.pdf';

        \Illuminate\Support\Facades\DB::table('documentos')->insert([
            'id_documento' => 1,
            'id_alumno' => 1,
            'tipo_documento' => 'ACTA_NACIMIENTO',
            'proceso' => 'INSCRIPCION',
            'periodo' => 'Agosto 2026 - Diciembre 2026',
            'ruta_archivo' => $rutaInexistente,
            'estado_validacion' => 'PENDIENTE',
        ]);

        $administrador = new \App\Models\User();
        $administrador->id_usuario = 996;
        $administrador->id_rol = 1;
        $administrador->estado = 'ACTIVO';

        $this->actingAs($administrador);

        $response = $this->get(
            route('documentos.ver', [
                'id_documento' => 1,
            ])
        );

        $response->assertNotFound();

        $this->assertDatabaseHas('documentos', [
            'id_documento' => 1,
            'ruta_archivo' => $rutaInexistente,
            'estado_validacion' => 'PENDIENTE',
        ]);

        \Illuminate\Support\Facades\Storage::disk('local')
            ->assertMissing($rutaInexistente);
    }

    // Verificar que un conflicto de unicidad no cause pérdida de documentos.
    public function test_conflicto_de_duplicidad_conserva_documento_existente(): void
    {
        $this->assertSame('sqlite', config('database.default'));
        $this->assertSame(':memory:', config('database.connections.sqlite.database'));

        \Illuminate\Support\Facades\Storage::fake('local');

        \Illuminate\Support\Facades\Schema::create('alumnos', function ($table) {
            $table->id('id_alumno');
            $table->unsignedBigInteger('id_usuario')->unique();
            $table->string('matricula', 30)->unique();
        });

        $this->crearTablaDocumentos(true);

        \Illuminate\Support\Facades\DB::table('alumnos')->insert([
            'id_alumno' => 1,
            'id_usuario' => 101,
            'matricula' => 'MAT-PRUEBA-001',
        ]);

        \Illuminate\Support\Facades\DB::table('documentos')->insert([
            'id_documento' => 1,
            'id_alumno' => 1,
            'tipo_documento' => 'ACTA_NACIMIENTO',
            'proceso' => 'INSCRIPCION',
            'periodo' => 'Agosto 2026 - Diciembre 2026',
            'ruta_archivo' => 'expedientes/MAT-PRUEBA-001/original.pdf',
            'estado_validacion' => 'PENDIENTE',
        ]);

        try {
            \Illuminate\Support\Facades\DB::table('documentos')->insert([
                'id_alumno' => 1,
                'tipo_documento' => 'ACTA_NACIMIENTO',
                'proceso' => 'INSCRIPCION',
                'periodo' => 'Agosto 2026 - Diciembre 2026',
                'ruta_archivo' => 'expedientes/MAT-PRUEBA-001/duplicado.pdf',
                'estado_validacion' => 'PENDIENTE',
            ]);

            $this->fail('La base de datos permitió un documento duplicado.');
        } catch (\Illuminate\Database\UniqueConstraintViolationException $e) {
            $this->assertTrue(true);
        }

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
        $this->assertSame('sqlite', config('database.default'));
        $this->assertSame(':memory:', config('database.connections.sqlite.database'));

        \Illuminate\Support\Facades\Storage::fake('local');

        $this->crearTablaConfiguraciones('Agosto 2026 - Diciembre 2026');

        \Illuminate\Support\Facades\Schema::create('alumnos', function ($table) {
            $table->id('id_alumno');
            $table->unsignedBigInteger('id_usuario')->unique();
            $table->string('matricula', 30)->unique();
        });

        $this->crearTablaDocumentos(true);

        \Illuminate\Support\Facades\DB::table('alumnos')->insert([
            'id_alumno' => 1,
            'id_usuario' => 101,
            'matricula' => 'MAT-PRUEBA-001',
        ]);

        $usuario = new \App\Models\User();
        $usuario->id_usuario = 101;
        $usuario->id_rol = 3;
        $usuario->estado = 'ACTIVO';

        $this->actingAs($usuario);

        $rutaOriginal = 'expedientes/MAT-PRUEBA-001/original.pdf';

        \App\Models\Documento::creating(function ($documento) use ($rutaOriginal) {
            \Illuminate\Support\Facades\Storage::disk('local')->put(
                $rutaOriginal,
                "%PDF-1.4\n% Documento original de prueba\n"
            );

            \Illuminate\Support\Facades\DB::table('documentos')->insert([
                'id_alumno' => $documento->id_alumno,
                'tipo_documento' => $documento->tipo_documento,
                'proceso' => $documento->proceso,
                'periodo' => $documento->periodo,
                'ruta_archivo' => $rutaOriginal,
                'estado_validacion' => 'PENDIENTE',
            ]);
        });

        $nuevoPdf = \Illuminate\Http\UploadedFile::fake()->createWithContent(
            'nuevo.pdf',
            "%PDF-1.4\n1 0 obj\n<< /Type /Catalog >>\nendobj\n%%EOF"
        );

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

        $this->assertInstanceOf(
            \Illuminate\Database\UniqueConstraintViolationException::class,
            $excepcion
        );

        $this->assertDatabaseCount('documentos', 0);

        $this->assertSame(
            0,
            \Illuminate\Support\Facades\DB::transactionLevel()
        );

        $this->assertSame(
            [$rutaOriginal],
            \Illuminate\Support\Facades\Storage::disk('local')->allFiles('expedientes')
        );
    }

    // Comprobar el manejo del error 1205 durante un reemplazo.
    public function test_error_1205_conserva_documento_y_muestra_mensaje(): void
    {
        $this->assertSame('sqlite', config('database.default'));
        $this->assertSame(':memory:', config('database.connections.sqlite.database'));

        \Illuminate\Support\Facades\Storage::fake('local');

        $periodo = 'Agosto 2026 - Diciembre 2026';
        $this->crearTablaConfiguraciones($periodo);

        \Illuminate\Support\Facades\Schema::create('alumnos', function ($table) {
            $table->id('id_alumno');
            $table->unsignedBigInteger('id_usuario')->unique();
            $table->string('matricula', 30)->unique();
        });

        $this->crearTablaDocumentos(true);

        \Illuminate\Support\Facades\DB::table('alumnos')->insert([
            'id_alumno' => 1,
            'id_usuario' => 101,
            'matricula' => 'MAT-PRUEBA-001',
        ]);

        $rutaAnterior = 'expedientes/MAT-PRUEBA-001/original.pdf';

        \Illuminate\Support\Facades\Storage::disk('local')->put(
            $rutaAnterior,
            "%PDF-1.4\n% Documento anterior de prueba\n"
        );

        \Illuminate\Support\Facades\DB::table('documentos')->insert([
            'id_documento' => 1,
            'id_alumno' => 1,
            'tipo_documento' => 'ACTA_NACIMIENTO',
            'proceso' => 'INSCRIPCION',
            'periodo' => $periodo,
            'ruta_archivo' => $rutaAnterior,
            'estado_validacion' => 'RECHAZADO',
        ]);

        $usuario = new \App\Models\User();
        $usuario->id_usuario = 101;
        $usuario->id_rol = 3;
        $usuario->estado = 'ACTIVO';

        $this->actingAs($usuario);

        \App\Models\Documento::updating(function () {
            $pdo = new \PDOException('Lock wait timeout exceeded');

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

        $nuevoPdf = \Illuminate\Http\UploadedFile::fake()->createWithContent(
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

        $response->assertSessionHasErrors([
            'archivo' => 'El documento está siendo procesado por otra solicitud. Intenta nuevamente en unos momentos.',
        ]);

        $this->assertDatabaseHas('documentos', [
            'id_documento' => 1,
            'id_alumno' => 1,
            'ruta_archivo' => $rutaAnterior,
            'estado_validacion' => 'RECHAZADO',
        ]);

        $this->assertDatabaseCount('documentos', 1);

        \Illuminate\Support\Facades\Storage::disk('local')
            ->assertExists($rutaAnterior);

        $this->assertSame(
            [$rutaAnterior],
            \Illuminate\Support\Facades\Storage::disk('local')->allFiles('expedientes')
        );

        $this->assertSame(
            0,
            \Illuminate\Support\Facades\DB::transactionLevel()
        );
    }

    // Verificar que Laravel reintente una transacción ante un bloqueo temporal.
    public function test_laravel_reintenta_transaccion_ante_error_temporal(): void
    {
        $this->assertSame('sqlite', config('database.default'));
        $this->assertSame(':memory:', config('database.connections.sqlite.database'));

        $intentos = 0;

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

        $this->assertSame('Operacion completada', $resultado);
        $this->assertSame(2, $intentos);
        $this->assertSame(0, \Illuminate\Support\Facades\DB::transactionLevel());
    }

    // Permitir subir nuevamente un documento de reinscripción en un periodo escolar distinto.
    public function test_alumno_puede_subir_reinscripcion_en_nuevo_periodo(): void
    {
        $this->assertSame('sqlite', config('database.default'));
        $this->assertSame(':memory:', config('database.connections.sqlite.database'));

        \Illuminate\Support\Facades\Storage::fake('local');

        $this->crearTablaConfiguraciones('Febrero 2027 - Julio 2027');

        \Illuminate\Support\Facades\Schema::create('alumnos', function ($table) {
            $table->id('id_alumno');
            $table->unsignedBigInteger('id_usuario')->unique();
            $table->string('matricula', 30)->unique();
        });

        $this->crearTablaDocumentos(true);

        \Illuminate\Support\Facades\DB::table('alumnos')->insert([
            'id_alumno' => 1,
            'id_usuario' => 101,
            'matricula' => 'MAT-PRUEBA-001',
        ]);

        // Documento aprobado del periodo escolar anterior.
        \Illuminate\Support\Facades\DB::table('documentos')->insert([
            'id_documento' => 1,
            'id_alumno' => 1,
            'tipo_documento' => 'Comprobante de Domicilio',
            'proceso' => 'REINSCRIPCION',
            'periodo' => 'Agosto 2026 - Diciembre 2026',
            'ruta_archivo' => 'expedientes/MAT-PRUEBA-001/anterior.pdf',
            'estado_validacion' => 'APROBADO',
        ]);

        $usuario = new \App\Models\User();
        $usuario->id_usuario = 101;
        $usuario->id_rol = 3;
        $usuario->estado = 'ACTIVO';

        $this->actingAs($usuario);

        $nuevoPdf = \Illuminate\Http\UploadedFile::fake()->createWithContent(
            'comprobante_2027.pdf',
            "%PDF-1.4\n1 0 obj\n<< /Type /Catalog >>\nendobj\n%%EOF"
        );

        $response = $this->post(
            route('alumno.documentos.store'),
            [
                'tipo_documento' => 'Comprobante de Domicilio',
                'proceso' => 'REINSCRIPCION',
                'archivo' => $nuevoPdf,
            ]
        );

        $response->assertSessionHasNoErrors();
        $response->assertSessionHas('success');

        $this->assertDatabaseCount('documentos', 2);

        $this->assertDatabaseHas('documentos', [
            'id_alumno' => 1,
            'tipo_documento' => 'Comprobante de Domicilio',
            'proceso' => 'REINSCRIPCION',
            'periodo' => 'Febrero 2027 - Julio 2027',
            'estado_validacion' => 'PENDIENTE',
        ]);
    }
}

<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;

class PagoSecurityTest extends TestCase
{
    // Verificar que un alumno no pueda modificar estados de pago.
    public function test_alumno_no_puede_modificar_estado_de_pago(): void
    {
        // Crear un usuario ficticio con rol de alumno.
        $alumno = new User();

        $alumno->id_usuario = 999;
        $alumno->id_rol = 3;
        $alumno->estado = 'ACTIVO';

        // Simular una sesión iniciada como alumno.
        $this->actingAs($alumno);

        // Intentar marcar una solicitud como pagada.
        $response = $this->put(
            route('admin.pagos.updateEstado', ['id_solicitud' => 1]),
            [
                'estado' => 'PAGADA',
            ]
        );

        // El middleware debe rechazar la solicitud.
        $response->assertForbidden();
    }

    // Verificar que un docente no pueda modificar estados de pago.
    public function test_docente_no_puede_modificar_estado_de_pago(): void
    {
        // Crear un docente ficticio activo.
        $docente = new User();

        $docente->id_usuario = 998;
        $docente->id_rol = 2;
        $docente->estado = 'ACTIVO';

        // Simular una sesión iniciada como docente.
        $this->actingAs($docente);

        // Intentar modificar el estado de una solicitud.
        $response = $this->put(
            route('admin.pagos.updateEstado', ['id_solicitud' => 1]),
            [
                'estado' => 'PAGADA',
            ]
        );

        // El middleware debe rechazar la solicitud.
        $response->assertForbidden();
    }

    // Impedir que visitantes modifiquen estados de pago.
    public function test_visitante_no_puede_modificar_estado_de_pago(): void
    {
        // Comprobar que no existe una sesión autenticada.
        $this->assertGuest();

        // Intentar marcar una solicitud como pagada sin iniciar sesión.
        $response = $this->put(
            route('admin.pagos.updateEstado', ['id_solicitud' => 1]),
            [
                'estado' => 'PAGADA',
            ]
        );

        // Laravel debe redirigir al inicio de sesión.
        $response->assertRedirect(route('login'));
    }

    // Impedir que administradores inactivos modifiquen pagos.
    public function test_administrador_inactivo_no_puede_modificar_pago(): void
    {
        // Crear un administrador ficticio inactivo.
        $administrador = new User();

        $administrador->id_usuario = 997;
        $administrador->id_rol = 1;
        $administrador->estado = 'INACTIVO';

        // Simular una sesión como administrador inactivo.
        $this->actingAs($administrador);

        // Intentar marcar una solicitud como pagada.
        $response = $this->put(
            route('admin.pagos.updateEstado', ['id_solicitud' => 1]),
            [
                'estado' => 'PAGADA',
            ]
        );

        // El middleware debe rechazar la solicitud.
        $response->assertForbidden();
    }

    // Rechazar estados de pago no permitidos.
    public function test_rechaza_estado_de_pago_invalido(): void
    {
        // Comprobar que se utiliza SQLite en memoria.
        $this->assertSame('sqlite', config('database.default'));
        $this->assertSame(
            ':memory:',
            config('database.connections.sqlite.database')
        );

        // Crear una tabla temporal de solicitudes.
        \Illuminate\Support\Facades\Schema::create('solicitudes', function ($table) {
            $table->id('id_solicitud');
            $table->unsignedBigInteger('id_alumno');
            $table->string('folio', 20)->unique();
            $table->string('estado')->default('PENDIENTE');
            $table->decimal('total_pagar', 8, 2)->default(0);
            $table->timestamps();
        });

        // Registrar una solicitud ficticia.
        \Illuminate\Support\Facades\DB::table('solicitudes')->insert([
            'id_solicitud' => 1,
            'id_alumno' => 1,
            'folio' => 'PAGO-PRUEBA-001',
            'estado' => 'PENDIENTE',
            'total_pagar' => 150.00,
        ]);

        // Simular un administrador activo.
        $administrador = new User();
        $administrador->id_usuario = 996;
        $administrador->id_rol = 1;
        $administrador->estado = 'ACTIVO';

        $this->actingAs($administrador);

        // Intentar asignar un estado no permitido.
        $response = $this->put(
            route('admin.pagos.updateEstado', ['id_solicitud' => 1]),
            [
                'estado' => 'ELIMINADA',
            ]
        );

        // Laravel debe rechazar el estado incorrecto.
        $response->assertSessionHasErrors('estado');

        // La solicitud debe permanecer sin modificaciones.
        $this->assertDatabaseHas('solicitudes', [
            'id_solicitud' => 1,
            'folio' => 'PAGO-PRUEBA-001',
            'estado' => 'PENDIENTE',
            'total_pagar' => 150.00,
        ]);
    }

    // Verificar que un administrador pueda marcar una solicitud como PAGADA.
    public function test_administrador_puede_marcar_solicitud_como_pagada(): void
    {
        // Garantizar que trabajamos con SQLite en memoria.
        $this->assertSame('sqlite', config('database.default'));
        $this->assertSame(
            ':memory:',
            config('database.connections.sqlite.database')
        );

        // Crear la tabla temporal de solicitudes.
        \Illuminate\Support\Facades\Schema::create('solicitudes', function ($table) {
            $table->id('id_solicitud');
            $table->unsignedBigInteger('id_alumno');
            $table->string('folio', 20)->unique();
            $table->string('estado')->default('PENDIENTE');
            $table->decimal('total_pagar', 8, 2)->default(0);
            $table->timestamps();
        });

        // Crear una solicitud ficticia pendiente.
        \Illuminate\Support\Facades\DB::table('solicitudes')->insert([
            'id_solicitud' => 1,
            'id_alumno' => 1,
            'folio' => 'PAGO-PRUEBA-001',
            'estado' => 'PENDIENTE',
            'total_pagar' => 150.00,
        ]);

        // Simular un administrador activo.
        $administrador = new User();
        $administrador->id_usuario = 996;
        $administrador->id_rol = 1;
        $administrador->estado = 'ACTIVO';

        $this->actingAs($administrador);

        // Marcar la solicitud como PAGADA.
        $response = $this->put(
            route('admin.pagos.updateEstado', ['id_solicitud' => 1]),
            [
                'estado' => 'PAGADA',
            ]
        );

        // Confirmar que el controlador aceptó la modificación.
        $response->assertSessionHasNoErrors();
        $response->assertSessionHas('success');

        // Verificar el estado y que los demás datos se conservaron.
        $this->assertDatabaseHas('solicitudes', [
            'id_solicitud' => 1,
            'id_alumno' => 1,
            'folio' => 'PAGO-PRUEBA-001',
            'estado' => 'PAGADA',
            'total_pagar' => 150.00,
        ]);

        // Confirmar que no se creó otra solicitud.
        $this->assertDatabaseCount('solicitudes', 1);
    }

    // Verificar que un administrador pueda cancelar una solicitud.
    public function test_administrador_puede_cancelar_solicitud(): void
    {
        // Comprobar el uso de SQLite en memoria.
        $this->assertSame('sqlite', config('database.default'));
        $this->assertSame(
            ':memory:',
            config('database.connections.sqlite.database')
        );

        // Crear tabla temporal de solicitudes.
        \Illuminate\Support\Facades\Schema::create('solicitudes', function ($table) {
            $table->id('id_solicitud');
            $table->unsignedBigInteger('id_alumno');
            $table->string('folio', 20)->unique();
            $table->string('estado')->default('PENDIENTE');
            $table->decimal('total_pagar', 8, 2)->default(0);
            $table->timestamps();
        });

        // Crear una solicitud ficticia pendiente.
        \Illuminate\Support\Facades\DB::table('solicitudes')->insert([
            'id_solicitud' => 1,
            'id_alumno' => 1,
            'folio' => 'PAGO-PRUEBA-001',
            'estado' => 'PENDIENTE',
            'total_pagar' => 150.00,
        ]);

        // Simular un administrador activo.
        $administrador = new User();
        $administrador->id_usuario = 996;
        $administrador->id_rol = 1;
        $administrador->estado = 'ACTIVO';

        $this->actingAs($administrador);

        // Cancelar la solicitud.
        $response = $this->put(
            route('admin.pagos.updateEstado', ['id_solicitud' => 1]),
            [
                'estado' => 'CANCELADA',
            ]
        );

        // Confirmar que el controlador aceptó la operación.
        $response->assertSessionHasNoErrors();
        $response->assertSessionHas('success');

        // Verificar la cancelación y la integridad de los datos.
        $this->assertDatabaseHas('solicitudes', [
            'id_solicitud' => 1,
            'id_alumno' => 1,
            'folio' => 'PAGO-PRUEBA-001',
            'estado' => 'CANCELADA',
            'total_pagar' => 150.00,
        ]);

        // Confirmar que no se creó otra solicitud.
        $this->assertDatabaseCount('solicitudes', 1);
    }

    // Verificar que la búsqueda respete el estado seleccionado.
    public function test_filtro_de_pagos_respeta_estado_al_buscar_alumno(): void
    {
        // Garantizar que se utiliza SQLite en memoria.
        $this->assertSame('sqlite', config('database.default'));
        $this->assertSame(
            ':memory:',
            config('database.connections.sqlite.database')
        );

        // Crear tablas temporales.
        \Illuminate\Support\Facades\Schema::create('alumnos', function ($table) {
            $table->id('id_alumno');
            $table->string('matricula', 30);
            $table->string('nombres', 100);
            $table->string('apellido_paterno', 100);
        });

        \Illuminate\Support\Facades\Schema::create('solicitudes', function ($table) {
            $table->id('id_solicitud');
            $table->unsignedBigInteger('id_alumno');
            $table->string('folio', 20)->unique();
            $table->string('estado')->default('PENDIENTE');
            $table->decimal('total_pagar', 8, 2)->default(0);
            $table->timestamps();
        });

        \Illuminate\Support\Facades\Schema::create('servicios', function ($table) {
            $table->id('id_servicio');
            $table->string('articulo', 50)->nullable();
            $table->string('concepto', 255);
            $table->decimal('costo_servicio', 8, 2);
            $table->decimal('derechos', 8, 2);
            $table->decimal('total', 8, 2);
            $table->timestamps();
        });

        \Illuminate\Support\Facades\Schema::create('solicitud_detalles', function ($table) {
            $table->id('id_detalle');
            $table->unsignedBigInteger('id_solicitud');
            $table->unsignedBigInteger('id_servicio');
            $table->decimal('precio_cobrado', 8, 2);
            $table->timestamps();
        });

        // Crear un alumno ficticio.
        \Illuminate\Support\Facades\DB::table('alumnos')->insert([
            'id_alumno' => 1,
            'matricula' => 'MAT-PRUEBA-001',
            'nombres' => 'Alumno',
            'apellido_paterno' => 'Prueba',
        ]);

        // Crear tres solicitudes del mismo alumno.
        foreach ([
            ['PAGO-001', 'PENDIENTE'],
            ['PAGO-002', 'PAGADA'],
            ['PAGO-003', 'CANCELADA'],
        ] as [$folio, $estado]) {
            \Illuminate\Support\Facades\DB::table('solicitudes')->insert([
                'id_alumno' => 1,
                'folio' => $folio,
                'estado' => $estado,
                'total_pagar' => 150.00,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // Simular un administrador activo.
        $administrador = new User();
        $administrador->id_usuario = 996;
        $administrador->id_rol = 1;
        $administrador->estado = 'ACTIVO';

        $this->actingAs($administrador);

        // Buscar por matrícula filtrando únicamente PENDIENTE.
        $response = $this->get(route('admin.pagos', [
            'estado' => 'PENDIENTE',
            'buscar' => 'MAT-PRUEBA-001',
        ]));

        // La consulta debe regresar solamente el pago pendiente.
        $response->assertOk();

        $response->assertViewHas('solicitudes', function ($solicitudes) {
            return $solicitudes->total() === 1
                && $solicitudes->first()->folio === 'PAGO-001'
                && $solicitudes->first()->estado === 'PENDIENTE';
        });

        // Confirmar que las tres solicitudes originales permanecen.
        $this->assertDatabaseCount('solicitudes', 3);
    }

}

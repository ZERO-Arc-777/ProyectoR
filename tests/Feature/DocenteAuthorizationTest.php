<?php

namespace Tests\Feature;

use App\Models\User;
use Tests\TestCase;

class DocenteAuthorizationTest extends TestCase
{
    // Un visitante no puede modificar docentes.
    public function test_invitado_no_puede_actualizar_docentes(): void
    {
        $response = $this->put('/admin/docentes/1', [
            'nombres' => 'Cambio no autorizado',
        ]);

        $response->assertRedirectToRoute('login');
    }

    // Un alumno autenticado no puede modificar docentes.
    public function test_alumno_no_puede_actualizar_docentes(): void
    {
        // Usuario de prueba creado únicamente en memoria.
        // No se guarda en la base de datos.
        $alumno = new User();

        $alumno->id_usuario = 999999;
        $alumno->id_rol = 3;
        $alumno->estado = 'ACTIVO';

        // Simular una sesión autenticada como alumno.
        $this->actingAs($alumno);

        // Intentar modificar un docente.
        $response = $this->put('/admin/docentes/1', [
            'nombres' => 'Cambio no autorizado',
        ]);

        // El sistema debe rechazar la solicitud.
        $response->assertStatus(403);
    }

    // Un docente no puede modificar registros de otros docentes.
    public function test_docente_no_puede_actualizar_docentes(): void
    {
        $docente = new User();

        $docente->id_usuario = 999998;
        $docente->id_rol = 2;
        $docente->estado = 'ACTIVO';

        $this->actingAs($docente);

        $response = $this->put('/admin/docentes/1', [
            'nombres' => 'Cambio no autorizado',
        ]);

        $response->assertStatus(403);
    }

    // Una cuenta inactiva no puede modificar docentes.
    public function test_administrador_inactivo_no_puede_actualizar_docentes(): void
    {
        $administrador = new User();

        $administrador->id_usuario = 999997;
        $administrador->id_rol = 1;
        $administrador->estado = 'INACTIVO';

        $this->actingAs($administrador);

        $response = $this->put('/admin/docentes/1', [
            'nombres' => 'Cambio no autorizado',
        ]);

        $response->assertStatus(403);
    }

    // Un administrador activo puede acceder al módulo Docentes.
    public function test_administrador_activo_puede_acceder_a_docentes(): void
{
    // Crear únicamente la tabla necesaria en SQLite temporal.
    \Illuminate\Support\Facades\Schema::create('docentes', function ($table) {
        $table->id('id_docente');
        $table->string('numero_empleado')->nullable();
        $table->string('nombres')->nullable();
        $table->string('apellido_paterno')->nullable();
    });

    // Simular un administrador activo.
    $administrador = new User();

    $administrador->id_usuario = 999996;
    $administrador->id_rol = 1;
    $administrador->estado = 'ACTIVO';

    $this->actingAs($administrador);

    // Comprobar acceso al listado.
    $response = $this->get('/admin/docentes');

    $response->assertOk();
}

    // Un alumno no puede registrar nuevos docentes.
    public function test_alumno_no_puede_registrar_docentes(): void
    {
        $alumno = new User();

        $alumno->id_usuario = 999995;
        $alumno->id_rol = 3;
        $alumno->estado = 'ACTIVO';

        $this->actingAs($alumno);

        $response = $this->post('/admin/docentes', [
            'numero_empleado' => 'PRUEBA-SEG',
            'nombres' => 'Prueba',
            'apellido_paterno' => 'Seguridad',
            'curp' => 'ABCD123456HXYZAB01',
        ]);

        $response->assertStatus(403);
    }

}

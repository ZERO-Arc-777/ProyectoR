<?php

namespace Tests\Feature;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    public function test_the_application_returns_a_successful_response(): void
    {
        // Garantizar que no utilizamos la base de datos real.
        $this->assertSame('sqlite', config('database.default'));
        $this->assertSame(
            ':memory:',
            config('database.connections.sqlite.database')
        );

        // Crear únicamente la tabla necesaria para esta prueba.
        Schema::create('roles', function (Blueprint $table) {
            $table->id('id_rol');
            $table->string('nombre_rol', 100);
        });

        // Comprobar que la página principal responde correctamente.
        $response = $this->get('/');

        $response->assertOk();
    }
}

<?php

namespace Tests\Feature;

use Tests\TestCase;

class DatabaseIsolationTest extends TestCase
{
    public function test_usa_sqlite_en_memoria(): void
    {
        $this->assertSame(
            'testing',
            config('app.env')
        );

        $this->assertSame(
            'sqlite',
            config('database.default')
        );

        $this->assertSame(
            ':memory:',
            config('database.connections.sqlite.database')
        );

        $this->assertSame(
            'sqlite',
            \DB::connection()->getDriverName()
        );
    }
}

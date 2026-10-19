<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Rol;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $rolAdmin = Rol::firstOrCreate(
            ['nombre' => 'Administrador'],
            ['descripcion' => 'Administrador del sistema escolar']
        );

        $rolDocente = Rol::firstOrCreate(
            ['nombre' => 'Docente'],
            ['descripcion' => 'Profesor de la institución']
        );

        $rolAlumno = Rol::firstOrCreate(
            ['nombre' => 'Alumno'],
            ['descripcion' => 'Estudiante inscrito']
        );

        User::firstOrCreate(
            ['correo' => 'admin@prepa5.com'],
            [
                'id_rol'     => $rolAdmin->id_rol,
                'nombre'     => 'Administrador General',
                'contrasena' => Hash::make('Admin123'),
                'estado'     => 1
            ]
        );
    }
}

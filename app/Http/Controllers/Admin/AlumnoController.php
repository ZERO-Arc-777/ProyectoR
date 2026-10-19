<?php

namespace App\Http\Controllers\Admin;

use App\Models\Alumno;
use App\Models\User;
use Illuminate\Http\Request;

class AlumnoController
{
    public function index()
    {
        $alumnos = Alumno::all();
        return view('admin.alumnos.index', compact('alumnos'));
    }

    public function create()
    {
        $usuariosDisponibles = User::where('id_rol', 3)
            ->whereNotIn('id_usuario', Alumno::pluck('id_usuario'))
            ->get();

        return view('admin.alumnos.create', compact('usuariosDisponibles'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'id_usuario'       => 'required|exists:usuarios,id_usuario|unique:alumnos,id_usuario',
            'matricula'        => 'required|string|unique:alumnos,matricula',
            'nombre'           => 'required|string|max:100',
            'apellido_paterno' => 'required|string|max:100',
            'apellido_materno' => 'nullable|string|max:100',
            'curp'             => 'required|string|size:18|unique:alumnos,curp',
            'semestre'         => 'required|integer|in:1,3,5',
            'grupo'            => 'required|string|in:A,B'
        ]);

        Alumno::create($request->all());

        return redirect()->route('admin.alumnos')->with('success', 'Expediente de alumno creado correctamente.');
    }
}

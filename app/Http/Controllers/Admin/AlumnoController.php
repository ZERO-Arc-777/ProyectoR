<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Alumno;
use App\Models\User;

class AlumnoController
{
    public function index(Request $request)
    {
        $buscar = $request->get('buscar');

        $alumnos = \App\Models\Alumno::query()
            ->when($buscar, function ($query, $buscar) {
                return $query->where('matricula', 'LIKE', "%{$buscar}%")
                             ->orWhere('nombres', 'LIKE', "%{$buscar}%")
                             ->orWhere('apellido_paterno', 'LIKE', "%{$buscar}%")
                             ->orWhere('curp', 'LIKE', "%{$buscar}%");
            })
            ->orderBy('matricula', 'desc')
            ->paginate(10); 
            
        return view('admin.alumnos.index', compact('alumnos', 'buscar'));
    }

    public function create()
    {
        $usuariosOcupados = \App\Models\Alumno::whereNotNull('id_usuario')->pluck('id_usuario')->toArray();
        
        $usuarios = \App\Models\User::where('id_rol', 3)
            ->whereNotIn('id_usuario', $usuariosOcupados)
            ->get();
        
        return view('admin.alumnos.create', compact('usuarios'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'id_usuario'       => 'required|exists:usuarios,id_usuario|unique:alumnos,id_usuario',
            'matricula'        => 'required|string|unique:alumnos,matricula',
            'nombres'           => 'required|string|max:100',
            'apellido_paterno' => 'required|string|max:100',
            'apellido_materno' => 'nullable|string|max:100',
            'curp'             => 'required|string|size:18|unique:alumnos,curp',
            'semestre'         => 'required|integer|in:1,3,5',
            'grupo'            => 'required|string|in:A,B'
        ]);

        Alumno::create($request->all());

        return redirect()->route('admin.alumnos')->with('success', 'Expediente de alumno creado correctamente.');
    }

    public function edit($id)
    {
        $alumno = \App\Models\Alumno::where('matricula', $id)->firstOrFail(); 
        
        $usuariosOcupados = \App\Models\Alumno::whereNotNull('id_usuario')
            ->where('matricula', '!=', $id)
            ->pluck('id_usuario')->toArray();

        $usuarios = \App\Models\User::where('id_rol', 3)
            ->whereNotIn('id_usuario', $usuariosOcupados)
            ->get();

        return view('admin.alumnos.editorA', compact('alumno', 'usuarios'));
    }

    public function update(Request $request, $id)
    {
        $alumno = \App\Models\Alumno::where('matricula', $id)->firstOrFail();

        $alumno->id_usuario = $request->id_usuario;
        $alumno->curp = $request->curp;
        $alumno->nombres = $request->nombres;
        $alumno->apellido_paterno = $request->apellido_paterno;
        $alumno->apellido_materno = $request->apellido_materno;
        $alumno->semestre = $request->semestre;
        $alumno->grupo = $request->grupo;
        
        $alumno->save();

        return redirect()->route('admin.alumnos')->with('success', 'Expediente de alumno modificado correctamente.');
    }
}

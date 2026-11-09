<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Docente;
use App\Models\User;

class DocenteController
{
    public function index(Request $request)
    {
        $buscar = $request->get('buscar');

        $docentes = Docente::query()
            ->when($buscar, function ($query, $buscar) {
                return $query->where('numero_empleado', 'LIKE', "%{$buscar}%")
                             ->orWhere('nombres', 'LIKE', "%{$buscar}%")
                             ->orWhere('apellido_paterno', 'LIKE', "%{$buscar}%");
            })
            ->orderBy('id_docente', 'desc')
            ->paginate(10); 
            
        return view('admin.docentes.index', compact('docentes', 'buscar'));
    }

    public function create()
    {
        $usuariosOcupados = Docente::whereNotNull('id_usuario')->pluck('id_usuario')->toArray();
        
        $usuarios = User::where('id_rol', 2)
            ->whereNotIn('id_usuario', $usuariosOcupados)
            ->get();
        
        return view('admin.docentes.create', compact('usuarios'));
    }

    public function store(Request $request)
    {
        $docente = new Docente();
        $docente->id_usuario = $request->id_usuario;
        $docente->numero_empleado = $request->numero_empleado;
        $docente->nombres = $request->nombres;
        $docente->apellido_paterno = $request->apellido_paterno;
        $docente->apellido_materno = $request->apellido_materno;
        $docente->curp = $request->curp;
        $docente->cedula_profesional = $request->cedula_profesional;
        $docente->estado = 'ACTIVO';
        $docente->save();

        return redirect()->route('admin.docentes')->with('success', 'Docente registrado correctamente.');
    }

    public function edit($id)
    {
        $docente = Docente::where('id_docente', $id)->firstOrFail(); 
        
        $usuariosOcupados = Docente::whereNotNull('id_usuario')
            ->where('id_docente', '!=', $id)
            ->pluck('id_usuario')->toArray();

        $usuarios = User::where('id_rol', 2)
            ->whereNotIn('id_usuario', $usuariosOcupados)
            ->get();

        return view('admin.docentes.editorD', compact('docente', 'usuarios'));
    }

    public function update(Request $request, $id)
    {
        $docente = Docente::where('id_docente', $id)->firstOrFail();

        $docente->id_usuario = $request->id_usuario;
        $docente->numero_empleado = $request->numero_empleado;
        $docente->nombres = $request->nombres;
        $docente->apellido_paterno = $request->apellido_paterno;
        $docente->apellido_materno = $request->apellido_materno;
        $docente->curp = $request->curp;
        $docente->cedula_profesional = $request->cedula_profesional;
        $docente->estado = $request->estado;
        
        $docente->save();

        return redirect()->route('admin.docentes')->with('success', 'Expediente de docente actualizado correctamente.');
    }
}

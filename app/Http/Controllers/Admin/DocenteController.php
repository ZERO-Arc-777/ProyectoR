<?php

namespace App\Http\Controllers\Admin;

use App\Models\Docente;
use App\Models\User;
use Illuminate\Http\Request;

class DocenteController
{
    public function index()
    {
        $docentes = Docente::all();
        return view('admin.docentes.index', compact('docentes'));
    }

    public function create()
    {
        $usuariosDisponibles = User::where('id_rol', 2)
            ->whereNotIn('id_usuario', Docente::pluck('id_usuario'))
            ->get();

        return view('admin.docentes.create', compact('usuariosDisponibles'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'id_usuario'         => 'required|exists:usuarios,id_usuario|unique:docentes,id_usuario',
            'numero_empleado'    => 'required|string|unique:docentes,numero_empleado',
            'nombres'            => 'required|string|max:100',
            'apellido_paterno'   => 'required|string|max:100',
            'apellido_materno'   => 'nullable|string|max:100',
            'curp'               => 'required|string|size:18|unique:docentes,curp',
            'cedula_profesional' => 'nullable|string|max:50',
        ]);

        Docente::create($request->all());

        return redirect()->route('admin.docentes')->with('success', 'Expediente docente creado correctamente.');
    }
}

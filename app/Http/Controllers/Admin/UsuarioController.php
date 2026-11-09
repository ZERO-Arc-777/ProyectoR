<?php

namespace App\Http\Controllers\Admin;
use App\Models\User;
use App\Models\Rol;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UsuarioController
{
    public function index(Request $request)
    {
        $buscar = $request->get('buscar');

        $usuarios = User::query()
            ->when($buscar, function ($query, $buscar) {
                return $query->where('nombre', 'LIKE', "%{$buscar}%")
                             ->orWhere('correo', 'LIKE', "%{$buscar}%");
            })
            ->orderBy('id_usuario', 'desc')
            ->paginate(10); 
            
        return view('admin.usuarios.index', compact('usuarios', 'buscar'));
    }

    public function create()
    {
        $roles = Rol::all();
        return view('admin.usuarios.creadorU', compact('roles'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nombre'     => 'required|string|max:255',
            'correo'     => 'required|email|unique:usuarios,correo',
            'contrasena' => 'required|min:6',
            'id_rol'     => 'required|exists:roles,id_rol'
        ]);

        User::create([
            'nombre'     => $request->nombre,
            'correo'     => $request->correo,
            'contrasena' => Hash::make($request->contrasena),
            'id_rol'     => $request->id_rol,
            'estado'     => 1
        ]);

        return redirect()->route('admin.usuarios')->with('success', 'Usuario creado correctamente.');
    }

    public function edit($id)
    {
        $usuario = User::findOrFail($id); 
        $roles = \App\Models\Rol::all(); 

        return view('admin.usuarios.editorU', compact('usuario', 'roles'));
    }

    public function update(Request $request, $id)
    {
        $usuario = User::findOrFail($id);

        $usuario->nombre = $request->nombre; 
        $usuario->correo = $request->correo; 
        $usuario->id_rol = $request->id_rol;

        if ($request->filled('password')) {
            $usuario->contrasena = bcrypt($request->password);
        }

        if ($request->has('estado')) {
            $usuario->estado = $request->estado;
        }

        $usuario->save();

        return redirect()->route('admin.usuarios')->with('success', 'Usuario modificado correctamente.');
    }
}

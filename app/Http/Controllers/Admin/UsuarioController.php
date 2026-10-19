<?php

namespace App\Http\Controllers\Admin;
use App\Models\User;
use App\Models\Rol;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UsuarioController
{
    public function index()
    {
        $usuarios = User::all();
        return view('admin.usuarios.index', compact('usuarios'));
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
            'correo'     => 'required|email|unique:usuarios,correo', // Verifica que el correo no se repita
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
}

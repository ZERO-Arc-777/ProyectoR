<?php

namespace App\Http\Controllers;
use App\Models\Rol;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController
{
    public function mostrarRoles()
    {
        $roles = Rol::all();
        return view('auth.roles', compact('roles'));
    }

    public function iniciarSesion(Request $request)
    {
        $credentials = [
            'correo'   => $request->username,
            'password' => $request->password,
        ];

        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();
            $rol = Auth::user()->id_rol;
            if ($rol == 1) {
                return redirect()->route('admin.dashboard');
            } elseif ($rol == 3) {
                return redirect()->route('alumno.dashboard');
            } elseif ($rol == 2) {
                return redirect()->route('docente.dashboard');
            }

            return redirect('/');
        }

        return back()->with('error', 'Credenciales incorrectas');
    }

    public function cerrarSesion(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/');
    }
}

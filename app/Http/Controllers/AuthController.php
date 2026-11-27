<?php

namespace App\Http\Controllers;

use App\Models\Rol;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController
{
    public function mostrarRoles(Request $request)
    {
        if (Auth::check()) {
            return $this->redirigirSegunRol($request);
        }

        $roles = Rol::all();

        return view('auth.roles', compact('roles'));
    }

    public function mostrarLogin(Request $request)
    {
        if (Auth::check()) {
            return $this->redirigirSegunRol($request);
        }

        return view('logeo');
    }

    public function iniciarSesion(Request $request)
    {
        if (Auth::check()) {
            return $this->redirigirSegunRol($request);
        }

        $credentials = [
            'correo'   => $request->username,
            'password' => $request->password,
        ];

        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();

            return $this->redirigirSegunRol($request);
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

    private function redirigirSegunRol(Request $request)
    {
        return match ((int) Auth::user()->id_rol) {
            1 => redirect()->route('admin.dashboard'),
            2 => redirect()->route('docente.dashboard'),
            3 => redirect()->route('alumno.dashboard'),
            default => $this->cerrarSesionInvalida($request),
        };
    }

    private function cerrarSesionInvalida(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')
            ->with('error', 'El rol asignado a esta cuenta no es válido.');
    }
}

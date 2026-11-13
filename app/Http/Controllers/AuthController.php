<?php

namespace App\Http\Controllers;

use App\Models\Rol;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;

class AuthController
{
    public function mostrarRoles()
    {
        $roles = Rol::all();

        return view('auth.roles', compact('roles'));
    }

    public function iniciarSesion(Request $request)
    {
        // Obtener el correo introducido.
        $correo = trim((string) $request->input('username', ''));

        // Identificar los intentos por correo y direccion IP.
        $claveLimite = 'login:' . hash(
            'sha256',
            strtolower($correo) . '|' . $request->ip()
        );

        // Bloquear temporalmente despues de 5 intentos fallidos.
        if (RateLimiter::tooManyAttempts($claveLimite, 5)) {
            $segundos = RateLimiter::availableIn($claveLimite);

            return back()->with(
                'error',
                "Demasiados intentos de inicio de sesión. Intenta nuevamente en {$segundos} segundos."
            );
        }

        // Verificar correo, contrasena y estado activo.
        $credentials = [
            'correo'   => $correo,
            'password' => $request->input('password', ''),
            'estado'   => 'ACTIVO',
        ];

        if (Auth::attempt($credentials)) {

            // Limpiar los intentos fallidos.
            RateLimiter::clear($claveLimite);

            // Regenerar la sesion por seguridad.
            $request->session()->regenerate();

            $rol = Auth::user()->id_rol;

            if ($rol == 1) {
                return redirect()->route('admin.dashboard');
            } elseif ($rol == 3) {
                return redirect()->route('alumno.dashboard');
            } elseif ($rol == 2) {
                return redirect()->route('docente.dashboard');
            }

            // No mantener una sesion con un rol desconocido.
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            abort(403, 'Tu cuenta no tiene un rol autorizado.');
        }

        // Registrar el intento fallido durante 60 segundos.
        RateLimiter::hit($claveLimite, 60);

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

<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    public function handle(Request $request, Closure $next, $roleId): Response
    {
        // Verificar que el usuario esté autenticado.
        if (!Auth::check()) {
            return redirect('/')->with(
                'error',
                'Debes iniciar sesión primero.'
            );
        }

        $usuario = Auth::user();

        // Comprobar que la cuenta esté activa.
        if (strtoupper(trim((string) $usuario->estado)) !== 'ACTIVO') {
            abort(403, 'Acceso denegado. Tu cuenta de usuario está inactiva.');
        }

        // Verificar que el usuario tenga el rol autorizado.
        if ((string) $usuario->id_rol !== (string) $roleId) {
            abort(403, 'Acceso denegado. Tu cuenta no tiene permisos para ver esta sección.');
        }

        return $next($request);
    }
}

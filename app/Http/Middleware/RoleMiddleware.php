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
        // Verificar que el usuario haya iniciado sesión.
        if (!Auth::check()) {
            return redirect('/')->with(
                'error',
                'Debes iniciar sesión primero.'
            );
        }

        $usuario = Auth::user();

        // Impedir el acceso a cuentas inactivas.
        if ($usuario->estado !== 'ACTIVO') {
            abort(403, 'Tu cuenta se encuentra inactiva.');
        }

        // Comprobar que el usuario tenga el rol autorizado.
        if ((int) $usuario->id_rol !== (int) $roleId) {
            abort(
                403,
                'Acceso denegado. Tu cuenta no tiene permisos para ver esta sección.'
            );
        }

        return $next($request);
    }
}

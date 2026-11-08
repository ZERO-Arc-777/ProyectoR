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
        if (!Auth::check()) {
            return redirect('/')->with('error', 'Debes iniciar sesión primero.');
        }

        if (Auth::user()->id_rol != $roleId) {
            abort(403, 'Acceso denegado. Tu cuenta no tiene permisos para ver esta sección.');
        }

        return $next($request);
    }
}

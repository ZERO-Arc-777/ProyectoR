<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class DocenteMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        // Verificar que el usuario haya iniciado sesión
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        // Verificar que el usuario tenga el rol de docente
        if (Auth::user()->id_rol != 2) {
            abort(403, 'No tienes permiso para acceder al panel docente.');
        }

        return $next($request);
    }
}

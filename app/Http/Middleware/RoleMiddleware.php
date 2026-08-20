<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/*
|--------------------------------------------------------------------------
| MIDDLEWARE DE ROL
|--------------------------------------------------------------------------
| Verifica que el usuario autenticado tenga el rol requerido para
| acceder a la ruta solicitada.
|
| Uso en rutas:
|   ->middleware('role:admin')
|   ->middleware('role:cliente')
|
| Los nombres de rol vienen de la tabla 'rol':
|   id_rol = 1 → Administrador
|   id_rol = 2 → Cliente
|
*/

class RoleMiddleware
{
    public function handle(Request $request, Closure $next, string $role): Response
    {
        // Verificar que el usuario esté autenticado
        if (! Auth::check()) {
            return redirect()->route('login');
        }

        $usuario = Auth::user();

        // Verificar rol según el parámetro recibido
        if ($role === 'admin' && ! $usuario->esAdmin()) {
            abort(403, 'Acceso no autorizado.');
        }

        if ($role === 'cliente' && ! $usuario->esCliente()) {
            abort(403, 'Acceso no autorizado.');
        }

        return $next($request);
    }
}

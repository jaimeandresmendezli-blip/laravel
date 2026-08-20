<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

/*
|--------------------------------------------------------------------------
| CONTROLLER DE SESIÓN AUTENTICADA
|--------------------------------------------------------------------------
| Maneja el login y logout del sistema.
| Redirige al panel correspondiente según el rol del usuario (US-0002).
|
*/

class AuthenticatedSessionController extends Controller
{
    /**
     * Muestra el formulario de login.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Procesa el intento de login.
     * Si las credenciales son válidas, redirige según el rol:
     *   - Administrador (id_rol = 1) → /admin/dashboard
     *   - Cliente       (id_rol = 2) → /cliente/dashboard
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();

        $usuario = Auth::user();

        // Redirigir según el rol del usuario autenticado
        if ($usuario->esAdmin()) {
            return redirect()->route('admin.dashboard');
        }

        return redirect()->route('cliente.dashboard');
    }

    /**
     * Cierra la sesión del usuario actual.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}

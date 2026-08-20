<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Usuario;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

/*
|--------------------------------------------------------------------------
| CONTROLLER DE REGISTRO
|--------------------------------------------------------------------------
| Maneja el formulario de registro de nuevos usuarios (US-0001).
| Todo usuario registrado desde el formulario público recibe rol 2 = Cliente.
| El Administrador crea cuentas admin desde el panel (US-0003).
|
*/

class RegisteredUserController extends Controller
{
    /**
     * Muestra el formulario de registro.
     */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Procesa el registro del nuevo usuario.
     * Validaciones según US-0001:
     *   - Correo único y válido.
     *   - Contraseña mínimo 8 caracteres, al menos 1 letra y 1 número.
     *   - Confirmación de contraseña obligatoria.
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'nombre'                => ['required', 'string', 'max:100'],
            'correo'                => ['required', 'string', 'email', 'max:100', 'unique:usuario,correo'],
            'telefono'              => ['nullable', 'string', 'max:20'],
            'password'              => [
                'required',
                'string',
                'min:8',
                'confirmed',
                'regex:/^(?=.*[a-zA-Z])(?=.*\d).+$/', // al menos 1 letra y 1 número
            ],
            'password_confirmation' => ['required'],
        ], [
            'nombre.required'    => 'El nombre es obligatorio.',
            'correo.required'    => 'El correo electrónico es obligatorio.',
            'correo.email'       => 'Ingresa un correo electrónico válido.',
            'correo.unique'      => 'Este correo ya está registrado.',
            'password.required'  => 'La contraseña es obligatoria.',
            'password.min'       => 'La contraseña debe tener mínimo 8 caracteres.',
            'password.confirmed' => 'Las contraseñas no coinciden.',
            'password.regex'     => 'La contraseña debe tener al menos una letra y un número.',
        ]);

        // Crear usuario con rol 2 = Cliente por defecto
        $usuario = Usuario::create([
            'id_rol'        => 2,
            'correo'        => $request->correo,
            'password_hash' => Hash::make($request->password),
            'nombre'        => $request->nombre,
            'telefono'      => $request->telefono,
            'estado'        => 'activo',
        ]);

        event(new Registered($usuario));

        // Redirigir al login para que el usuario ingrese sus credenciales
        return redirect()->route('login');
    }
}

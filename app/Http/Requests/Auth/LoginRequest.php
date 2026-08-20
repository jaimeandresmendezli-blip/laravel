<?php

namespace App\Http\Requests\Auth;

use Illuminate\Auth\Events\Lockout;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

/*
|--------------------------------------------------------------------------
| LOGIN REQUEST
|--------------------------------------------------------------------------
| Maneja la validación y autenticación del formulario de login.
| Adaptado para usar 'correo' en lugar de 'email' (campo real de la BD)
| y 'password_hash' como campo de contraseña en el modelo Usuario.
|
*/

class LoginRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Reglas de validación — usamos 'correo' como campo de login.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'correo'   => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ];
    }

    /**
     * Mensajes personalizados en español.
     */
    public function messages(): array
    {
        return [
            'correo.required'   => 'El correo electrónico es obligatorio.',
            'correo.email'      => 'Ingresa un correo electrónico válido.',
            'password.required' => 'La contraseña es obligatoria.',
        ];
    }

    /**
     * Intenta autenticar con las credenciales recibidas.
     * Laravel usará getAuthPassword() del modelo para comparar el hash.
     *
     * @throws ValidationException
     */
    public function authenticate(): void
    {
        $this->ensureIsNotRateLimited();

        /*
        | Auth::attempt recibe ['correo' => ..., 'password' => ...]
        | Laravel buscará el usuario por 'correo' y comparará
        | el password con getAuthPassword() del modelo.
        */
        if (! Auth::attempt(['correo' => $this->correo, 'password' => $this->password], $this->boolean('remember'))) {
            RateLimiter::hit($this->throttleKey());

            throw ValidationException::withMessages([
                'correo' => 'Las credenciales no coinciden con nuestros registros.',
            ]);
        }

        /*
        | Verificar que la cuenta esté activa (US-0002 escenario 4).
        */
        if (Auth::user()->estado === 'inactivo') {
            Auth::logout();

            throw ValidationException::withMessages([
                'correo' => 'Tu cuenta está inactiva. Contacta al administrador.',
            ]);
        }

        RateLimiter::clear($this->throttleKey());
    }

    /**
     * Verifica el límite de intentos fallidos (US-0002 escenario 3).
     * Máximo 5 intentos, bloqueo por 5 minutos.
     *
     * @throws ValidationException
     */
    public function ensureIsNotRateLimited(): void
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
            return;
        }

        event(new Lockout($this));

        $seconds = RateLimiter::availableIn($this->throttleKey());

        throw ValidationException::withMessages([
            'correo' => 'Demasiados intentos fallidos. Por favor espera ' . ceil($seconds / 60) . ' minuto(s) antes de intentar nuevamente.',
        ]);
    }

    /**
     * Clave única para el rate limiter basada en correo + IP.
     */
    public function throttleKey(): string
    {
        return Str::transliterate(Str::lower($this->string('correo')) . '|' . $this->ip());
    }
}

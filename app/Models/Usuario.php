<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;

/*
|--------------------------------------------------------------------------
| MODELO USUARIO
|--------------------------------------------------------------------------
| Extiende Authenticatable para que Laravel pueda usarlo como modelo
| de autenticación. Mapea la tabla 'usuario' con campos personalizados
| (correo en lugar de email, password_hash en lugar de password).
|
*/

class Usuario extends Authenticatable
{
    protected $table = 'usuario';

    protected $primaryKey = 'id_usuario';

    public $timestamps = false;

    protected $fillable = [
        'id_rol',
        'correo',
        'password_hash',
        'nombre',
        'telefono',
        'estado',
        'fecha_registro',
    ];

    /*
    |--------------------------------------------------------------------------
    | OCULTAR CAMPOS SENSIBLES EN SERIALIZACIÓN
    |--------------------------------------------------------------------------
    */
    protected $hidden = [
        'password_hash',
    ];

    /*
    |--------------------------------------------------------------------------
    | AUTENTICACIÓN PERSONALIZADA
    |--------------------------------------------------------------------------
    | Laravel espera getAuthPassword() para obtener la contraseña del usuario.
    | Como nuestro campo se llama 'password_hash' y no 'password',
    | sobreescribimos estos métodos.
    |
    */

    /**
     * Retorna el campo que Laravel usa como contraseña para autenticar.
     */
    public function getAuthPassword(): string
    {
        return $this->password_hash;
    }

    /**
     * Retorna el nombre del campo que identifica al usuario (para login).
     * Usamos 'correo' en lugar de 'email'.
     */
    public function getAuthIdentifierName(): string
    {
        return 'id_usuario';
    }

    /*
    |--------------------------------------------------------------------------
    | RELACIONES
    |--------------------------------------------------------------------------
    */

    public function rol()
    {
        return $this->belongsTo(Role::class, 'id_rol', 'id_rol');
    }

    public function recuperaciones()
    {
        return $this->hasMany(RecuperacionCuenta::class, 'id_usuario', 'id_usuario');
    }

    public function carritos()
    {
        return $this->hasMany(Carrito::class, 'id_usuario', 'id_usuario');
    }

    public function pedidos()
    {
        return $this->hasMany(Pedido::class, 'id_cliente', 'id_usuario');
    }

    /*
    |--------------------------------------------------------------------------
    | HELPERS DE ROL
    |--------------------------------------------------------------------------
    */

    /**
     * Verifica si el usuario es Administrador (id_rol = 1).
     */
    public function esAdmin(): bool
    {
        return $this->id_rol === 1;
    }

    /**
     * Verifica si el usuario es Cliente (id_rol = 2).
     */
    public function esCliente(): bool
    {
        return $this->id_rol === 2;
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/*
|--------------------------------------------------------------------------
| MODELO RECUPERACION CUENTA
|--------------------------------------------------------------------------
| Representa los códigos de recuperación de contraseña generados
| para los usuarios. Tabla: recuperacion_cuenta.
|
*/

class RecuperacionCuenta extends Model
{
    protected $table = 'recuperacion_cuenta';

    protected $primaryKey = 'id_recuperacion';

    public $timestamps = false;

    protected $fillable = [
        'id_usuario',
        'codigo',
        'fecha_expiracion',
        'usado',
    ];

    /*
    |--------------------------------------------------------------------------
    | RELACIONES
    |--------------------------------------------------------------------------
    */

    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'id_usuario', 'id_usuario');
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pedido extends Model
{
    protected $table = 'pedido';
    protected $primaryKey = 'id_pedido';
    public $timestamps = false;

    protected $fillable = [
        'id_cliente', 'direccion_entrega', 'barrio', 'telefono_entrega',
        'referencia', 'observaciones', 'estado_entrega', 'fecha', 'estado', 'total',
    ];

    protected $casts = [
        'total' => 'decimal:2',
        'fecha' => 'datetime',
    ];

    public function cliente()
    {
        return $this->belongsTo(Usuario::class, 'id_cliente', 'id_usuario');
    }

    public function detalles()
    {
        return $this->hasMany(DetallePedido::class, 'id_pedido', 'id_pedido');
    }

    public function pago()
    {
        return $this->hasOne(Pago::class, 'id_pedido', 'id_pedido');
    }
}

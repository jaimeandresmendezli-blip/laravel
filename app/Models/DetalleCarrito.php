<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DetalleCarrito extends Model
{
    protected $table = 'detalle_carrito';
    protected $primaryKey = 'id_detalle_carrito';
    public $timestamps = false;

    protected $fillable = [
        'id_carrito', 'id_producto', 'cantidad', 'precio_unitario', 'subtotal',
    ];

    protected $casts = [
        'precio_unitario' => 'decimal:2',
        'subtotal'        => 'decimal:2',
        'cantidad'        => 'integer',
    ];

    public function carrito()
    {
        return $this->belongsTo(Carrito::class, 'id_carrito', 'id_carrito');
    }

    public function producto()
    {
        return $this->belongsTo(Producto::class, 'id_producto', 'id_producto');
    }
}

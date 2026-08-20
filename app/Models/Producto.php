<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Producto extends Model
{
    protected $table = 'producto';
    protected $primaryKey = 'id_producto';
    public $timestamps = false;

    protected $fillable = [
        'nombre', 'tipo_huevo', 'presentacion', 'descripcion',
        'precio', 'cantidad', 'imagen', 'estado',
    ];

    protected $casts = [
        'precio'   => 'decimal:2',
        'cantidad' => 'integer',
    ];

    public function movimientos()
    {
        return $this->hasMany(MovimientoInventario::class, 'id_producto', 'id_producto');
    }

    public function detallesCarrito()
    {
        return $this->hasMany(DetalleCarrito::class, 'id_producto', 'id_producto');
    }

    public function detallesPedido()
    {
        return $this->hasMany(DetallePedido::class, 'id_producto', 'id_producto');
    }
}

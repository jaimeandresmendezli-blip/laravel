<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MovimientoInventario extends Model
{
    protected $table = 'movimiento_inventario';
    protected $primaryKey = 'id_movimiento';
    public $timestamps = false;

    protected $fillable = [
        'id_producto', 'tipo_movimiento', 'cantidad', 'motivo', 'fecha',
    ];

    protected $casts = [
        'fecha'    => 'datetime',
        'cantidad' => 'integer',
    ];

    public function producto()
    {
        return $this->belongsTo(Producto::class, 'id_producto', 'id_producto');
    }
}

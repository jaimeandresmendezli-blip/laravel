<?php

namespace App\Http\Controllers;

use App\Models\MovimientoInventario;
use App\Models\Producto;
use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| US-0005 — ACTUALIZAR INVENTARIO (entradas/salidas manuales)
| US-0006 — CONSULTAR HISTORIAL DE MOVIMIENTOS
|--------------------------------------------------------------------------
*/

class InventarioController extends Controller
{
    /** US-0006: Listar historial con filtros opcionales */
    public function index(Request $request)
    {
        $query = MovimientoInventario::with('producto')->orderBy('fecha', 'desc');

        // Filtro por producto
        if ($request->filled('id_producto')) {
            $query->where('id_producto', $request->id_producto);
        }
        // Filtro por tipo de movimiento
        if ($request->filled('tipo_movimiento')) {
            $query->where('tipo_movimiento', $request->tipo_movimiento);
        }
        // Filtro por fechas
        if ($request->filled('fecha_desde')) {
            $query->whereDate('fecha', '>=', $request->fecha_desde);
        }
        if ($request->filled('fecha_hasta')) {
            $query->whereDate('fecha', '<=', $request->fecha_hasta);
        }

        $movimientos = $query->get();
        $productos   = Producto::orderBy('nombre')->get();

        return view('admin.inventario.index', compact('movimientos', 'productos'));
    }

    /** US-0005: Registrar movimiento manual */
    public function store(Request $request)
    {
        $request->validate([
            'id_producto'     => 'required|exists:producto,id_producto',
            'tipo_movimiento' => 'required|in:entrada,salida',
            'cantidad'        => 'required|integer|min:1',
            'motivo'          => 'required|string|max:255',
        ], [
            'cantidad.min' => 'La cantidad debe ser al menos 1.',
            'motivo.required' => 'El motivo es obligatorio para registrar el movimiento.',
        ]);

        $producto = Producto::findOrFail($request->id_producto);

        // US-0005 escenario 4: verificar stock suficiente en salida
        if ($request->tipo_movimiento === 'salida') {
            if ($producto->cantidad < $request->cantidad) {
                return back()->withErrors([
                    'cantidad' => "Stock insuficiente. Disponible: {$producto->cantidad} unidades.",
                ])->withInput();
            }
            // Reducir stock
            $producto->decrement('cantidad', $request->cantidad);
        } else {
            // Incrementar stock en entrada
            $producto->increment('cantidad', $request->cantidad);
        }

        // Registrar movimiento en historial
        MovimientoInventario::create([
            'id_producto'     => $request->id_producto,
            'tipo_movimiento' => $request->tipo_movimiento,
            'cantidad'        => $request->cantidad,
            'motivo'          => $request->motivo,
            'fecha'           => now(),
        ]);

        return redirect()->route('admin.inventario.index')
            ->with('success', 'Movimiento de inventario registrado correctamente.');
    }
}

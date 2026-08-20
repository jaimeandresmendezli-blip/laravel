<?php

namespace App\Http\Controllers;

use App\Models\Carrito;
use App\Models\DetalleCarrito;
use App\Models\Producto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/*
|--------------------------------------------------------------------------
| US-0008 — CARRITO DE COMPRAS (CLIENTE)
|--------------------------------------------------------------------------
*/

class CarritoController extends Controller
{
    /** Obtiene o crea el carrito activo del usuario */
    private function obtenerCarrito()
    {
        return Carrito::firstOrCreate(
            ['id_usuario' => Auth::id(), 'estado' => 'activo'],
            ['fecha_creacion' => now()]
        );
    }

    /** Ver el carrito */
    public function index()
    {
        $carrito  = $this->obtenerCarrito();
        $detalles = $carrito->detalles()->with('producto')->get();
        $total    = $detalles->sum('subtotal');

        return view('cliente.carrito.index', compact('carrito', 'detalles', 'total'));
    }

    /** Agregar producto al carrito (US-0008 escenario 4) */
    public function agregar(Request $request)
    {
        $request->validate([
            'id_producto' => 'required|exists:producto,id_producto',
            'cantidad'    => 'required|integer|min:1',
        ]);

        $producto = Producto::findOrFail($request->id_producto);

        // Verificar que el producto esté activo
        if ($producto->estado !== 'activo') {
            return back()->withErrors(['error' => 'Este producto no está disponible.']);
        }

        // US-0008 escenario 8: verificar stock suficiente
        if ($producto->cantidad < $request->cantidad) {
            return back()->withErrors([
                'error' => "Stock insuficiente. Solo hay {$producto->cantidad} unidades disponibles.",
            ]);
        }

        $carrito  = $this->obtenerCarrito();

        // Verificar si ya existe en el carrito (unique constraint id_carrito + id_producto)
        $detalle = DetalleCarrito::where('id_carrito', $carrito->id_carrito)
            ->where('id_producto', $producto->id_producto)
            ->first();

        if ($detalle) {
            // Ya existe: sumar cantidad
            $nuevaCantidad = $detalle->cantidad + $request->cantidad;

            if ($producto->cantidad < $nuevaCantidad) {
                return back()->withErrors([
                    'error' => "Stock insuficiente. Solo hay {$producto->cantidad} unidades disponibles.",
                ]);
            }

            $detalle->update([
                'cantidad' => $nuevaCantidad,
                'subtotal' => $nuevaCantidad * $producto->precio,
            ]);
        } else {
            DetalleCarrito::create([
                'id_carrito'      => $carrito->id_carrito,
                'id_producto'     => $producto->id_producto,
                'cantidad'        => $request->cantidad,
                'precio_unitario' => $producto->precio,
                'subtotal'        => $request->cantidad * $producto->precio,
            ]);
        }

        return redirect()->route('cliente.carrito.index')
            ->with('success', 'Producto agregado al carrito.');
    }

    /** Actualizar cantidad de un ítem (US-0008 escenario 5) */
    public function actualizar(Request $request, DetalleCarrito $detalle)
    {
        $request->validate([
            'cantidad' => 'required|integer|min:1',
        ]);

        $producto = $detalle->producto;

        // Verificar stock
        if ($producto->cantidad < $request->cantidad) {
            return back()->withErrors([
                'error' => "Stock insuficiente. Solo hay {$producto->cantidad} unidades disponibles.",
            ]);
        }

        $detalle->update([
            'cantidad' => $request->cantidad,
            'subtotal' => $request->cantidad * $detalle->precio_unitario,
        ]);

        return redirect()->route('cliente.carrito.index')
            ->with('success', 'Cantidad actualizada.');
    }

    /** Eliminar un ítem del carrito (US-0008 escenario 6) */
    public function eliminar(DetalleCarrito $detalle)
    {
        $detalle->delete();

        return redirect()->route('cliente.carrito.index')
            ->with('success', 'Producto eliminado del carrito.');
    }

    /** Vaciar todo el carrito (US-0008 escenario 7) */
    public function vaciar()
    {
        $carrito = $this->obtenerCarrito();
        $carrito->detalles()->delete();

        return redirect()->route('cliente.carrito.index')
            ->with('success', 'Carrito vaciado.');
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\Carrito;
use App\Models\DetallePedido;
use App\Models\MovimientoInventario;
use App\Models\Pago;
use App\Models\Pedido;
use App\Models\Producto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/*
|--------------------------------------------------------------------------
| US-0008 — PEDIDOS DEL CLIENTE
|--------------------------------------------------------------------------
*/

class ClientePedidoController extends Controller
{
    /** Mis pedidos con resumen (US-0008 escenario 10) */
    public function index()
    {
        $pedidos           = Pedido::where('id_cliente', Auth::id())->orderBy('fecha', 'desc')->get();
        $totalPedidos      = $pedidos->count();
        $pedidosPendientes = $pedidos->where('estado', 'pendiente')->count();
        $pedidosPagados    = $pedidos->where('estado', 'pagado')->count();

        return view('cliente.pedidos.index', compact('pedidos', 'totalPedidos', 'pedidosPendientes', 'pedidosPagados'));
    }

    /** Formulario confirmación pedido — datos de entrega (US-0008 escenarios 11,12,13) */
    public function create()
    {
        $carrito = Carrito::where('id_usuario', Auth::id())
            ->where('estado', 'activo')
            ->with('detalles.producto')
            ->first();

        if (!$carrito || $carrito->detalles->isEmpty()) {
            return redirect()->route('cliente.carrito.index')
                ->withErrors(['error' => 'Tu carrito está vacío.']);
        }

        $total = $carrito->detalles->sum('subtotal');

        return view('cliente.pedidos.create', compact('carrito', 'total'));
    }

    /** Confirmar y guardar pedido (US-0008 escenario 1) */
    public function store(Request $request)
    {
        $request->validate([
            'direccion_entrega' => 'required|string|max:255',
            'barrio'            => 'required|string|max:100',
            'telefono_entrega'  => 'required|string|max:20',
            'referencia'        => 'nullable|string|max:255',
            'observaciones'     => 'nullable|string',
            'metodo_pago'       => 'required|in:efectivo,transferencia,nequi',
            'referencia_pago'   => 'nullable|string|max:100',
        ], [
            'direccion_entrega.required' => 'La dirección de entrega es obligatoria.',
            'barrio.required'            => 'El barrio es obligatorio.',
            'telefono_entrega.required'  => 'El teléfono de entrega es obligatorio.',
            'metodo_pago.required'       => 'Selecciona un método de pago.',
        ]);

        $carrito = Carrito::where('id_usuario', Auth::id())
            ->where('estado', 'activo')
            ->with('detalles.producto')
            ->first();

        if (!$carrito || $carrito->detalles->isEmpty()) {
            return redirect()->route('cliente.carrito.index')
                ->withErrors(['error' => 'Tu carrito está vacío.']);
        }

        // Verificar stock de todos los productos antes de confirmar (US-0005 escenario 4)
        foreach ($carrito->detalles as $detalle) {
            if ($detalle->producto->cantidad < $detalle->cantidad) {
                return back()->withErrors([
                    'error' => "Stock insuficiente para '{$detalle->producto->nombre}'. Solo hay {$detalle->producto->cantidad} unidades.",
                ]);
            }
        }

        DB::transaction(function () use ($request, $carrito) {
            $total = $carrito->detalles->sum('subtotal');

            // Crear pedido
            $pedido = Pedido::create([
                'id_cliente'        => Auth::id(),
                'direccion_entrega' => $request->direccion_entrega,
                'barrio'            => $request->barrio,
                'telefono_entrega'  => $request->telefono_entrega,
                'referencia'        => $request->referencia,
                'observaciones'     => $request->observaciones,
                'estado_entrega'    => 'pendiente',
                'fecha'             => now(),
                'estado'            => 'pendiente',
                'total'             => $total,
            ]);

            // Crear detalles del pedido y descontar stock
            foreach ($carrito->detalles as $detalle) {
                DetallePedido::create([
                    'id_pedido'   => $pedido->id_pedido,
                    'id_producto' => $detalle->id_producto,
                    'cantidad'    => $detalle->cantidad,
                    'precio'      => $detalle->precio_unitario,
                    'subtotal'    => $detalle->subtotal,
                ]);

                // US-0005 escenario 2: reducir stock automáticamente
                $detalle->producto->decrement('cantidad', $detalle->cantidad);

                // Registrar movimiento de salida en inventario
                MovimientoInventario::create([
                    'id_producto'     => $detalle->id_producto,
                    'tipo_movimiento' => 'salida',
                    'cantidad'        => $detalle->cantidad,
                    'motivo'          => "Venta - Pedido #{$pedido->id_pedido}",
                    'fecha'           => now(),
                ]);
            }

            // Crear registro de pago
            Pago::create([
                'id_pedido'       => $pedido->id_pedido,
                'metodo_pago'     => $request->metodo_pago,
                'monto'           => $total,
                'estado_pago'     => 'pendiente',
                'referencia_pago' => $request->referencia_pago,
                'fecha'           => now(),
            ]);

            // Marcar carrito como comprado
            $carrito->update(['estado' => 'comprado']);
        });

        return redirect()->route('cliente.pedidos.index')
            ->with('success', 'Pedido registrado correctamente.');
    }

    /** Detalle de un pedido */
    public function show(Pedido $pedido)
    {
        // Verificar que el pedido pertenece al cliente
        if ($pedido->id_cliente !== Auth::id()) {
            abort(403);
        }

        $pedido->load(['detalles.producto', 'pago']);

        return view('cliente.pedidos.show', compact('pedido'));
    }

    /** Cancelar pedido (US-0008 escenario 3) */
    public function cancelar(Pedido $pedido)
    {
        if ($pedido->id_cliente !== Auth::id()) {
            abort(403);
        }

        // Solo se puede cancelar si está pendiente (US-0008 escenario 9)
        if ($pedido->estado !== 'pendiente') {
            return back()->withErrors(['error' => 'Solo puedes cancelar pedidos en estado pendiente.']);
        }

        DB::transaction(function () use ($pedido) {
            $pedido->load('detalles.producto');

            // US-0005 escenario 3: devolver stock automáticamente
            foreach ($pedido->detalles as $detalle) {
                $detalle->producto->increment('cantidad', $detalle->cantidad);

                MovimientoInventario::create([
                    'id_producto'     => $detalle->id_producto,
                    'tipo_movimiento' => 'entrada',
                    'cantidad'        => $detalle->cantidad,
                    'motivo'          => "Reintegro por cancelación - Pedido #{$pedido->id_pedido}",
                    'fecha'           => now(),
                ]);
            }

            $pedido->update(['estado' => 'cancelado', 'estado_entrega' => 'cancelado']);
        });

        return redirect()->route('cliente.pedidos.index')
            ->with('success', 'Pedido cancelado. El stock fue devuelto.');
    }
}

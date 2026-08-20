<?php

namespace App\Http\Controllers;

use App\Models\Pedido;
use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| US-0008 escenario 14 — GESTIÓN DE PEDIDOS (ADMINISTRADOR)
| El admin puede ver todos los pedidos y actualizar el estado de entrega.
|--------------------------------------------------------------------------
*/

class AdminPedidoController extends Controller
{
    /** Lista todos los pedidos */
    public function index(Request $request)
    {
        $query = Pedido::with(['cliente', 'pago'])->orderBy('fecha', 'desc');

        if ($request->filled('estados')) {
            $query->where('estado', $request->estado);
        }
        if ($request->filled('estado_entrega')) {
            $query->where('estado_entrega', $request->estado_entrega);
        }

        $pedidos = $query->get();
        return view('admin.pedidos.index', compact('pedidos'));
    }

    /** Detalle de un pedido */
    public function show(Pedido $pedido)
    {
        $pedido->load(['cliente', 'detalles.producto', 'pago']);
        return view('admin.pedidos.show', compact('pedido'));
    }

    /** Actualizar estado de entrega (US-0008 escenario 14) */
    public function actualizarEstadoEntrega(Request $request, Pedido $pedido)
    {
        $request->validate([
            'estado_entrega' => 'required|in:pendiente,en_camino,entregado,cancelado',
        ]);

        $pedido->update(['estado_entrega' => $request->estado_entrega]);

        return redirect()->route('admin.pedidos.show', $pedido->id_pedido)
            ->with('success', 'Estado de entrega actualizado.');
    }
}

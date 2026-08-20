<?php

namespace App\Http\Controllers;

use App\Models\MovimientoInventario;
use App\Models\Pago;
use App\Models\Pedido;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| US-0009 — REPORTES DEL SISTEMA (ADMINISTRADOR)
|--------------------------------------------------------------------------
*/

class ReporteController extends Controller
{
    /** Generar reporte con filtros de fechas */
    public function index(Request $request)
    {
        $request->validate([
            'fecha_desde' => 'nullable|date',
            'fecha_hasta' => 'nullable|date|after_or_equal:fecha_desde',
        ]);

        $desde = $request->fecha_desde;
        $hasta = $request->fecha_hasta;

        // Pedidos en el período
        $queryPedidos = Pedido::with(['cliente', 'detalles.producto', 'pago']);
        if ($desde) $queryPedidos->whereDate('fecha', '>=', $desde);
        if ($hasta) $queryPedidos->whereDate('fecha', '<=', $hasta);
        $pedidos = $queryPedidos->orderBy('fecha', 'desc')->get();

        // Totales
        $totalVentas    = $pedidos->where('estado', 'pagado')->sum('total');
        $totalPedidos   = $pedidos->count();
        $pedidosPagados = $pedidos->where('estado', 'pagado')->count();
        $pedidosPendientes = $pedidos->where('estado', 'pendiente')->count();
        $pedidosCancelados = $pedidos->where('estado', 'cancelado')->count();

        // Movimientos de inventario en el período
        $queryMov = MovimientoInventario::with('producto');
        if ($desde) $queryMov->whereDate('fecha', '>=', $desde);
        if ($hasta) $queryMov->whereDate('fecha', '<=', $hasta);
        $movimientos = $queryMov->orderBy('fecha', 'desc')->get();

        return view('admin.reportes.index', compact(
            'pedidos', 'movimientos', 'totalVentas',
            'totalPedidos', 'pedidosPagados', 'pedidosPendientes',
            'pedidosCancelados', 'desde', 'hasta'
        ));
    }

    /** Exportar reporte en PDF */
    public function exportarPdf(Request $request)
    {
        $desde = $request->fecha_desde;
        $hasta = $request->fecha_hasta;

        $queryPedidos = Pedido::with(['cliente', 'detalles.producto', 'pago']);
        if ($desde) $queryPedidos->whereDate('fecha', '>=', $desde);
        if ($hasta) $queryPedidos->whereDate('fecha', '<=', $hasta);
        $pedidos = $queryPedidos->orderBy('fecha', 'desc')->get();

        $totalVentas    = $pedidos->where('estado', 'pagado')->sum('total');
        $totalPedidos   = $pedidos->count();
        $pedidosPagados = $pedidos->where('estado', 'pagado')->count();

        $queryMov = MovimientoInventario::with('producto');
        if ($desde) $queryMov->whereDate('fecha', '>=', $desde);
        if ($hasta) $queryMov->whereDate('fecha', '<=', $hasta);
        $movimientos = $queryMov->orderBy('fecha', 'desc')->get();

        $pdf = Pdf::loadView('admin.reportes.pdf', compact(
            'pedidos', 'movimientos', 'totalVentas',
            'totalPedidos', 'pedidosPagados', 'desde', 'hasta'
        ))->setPaper('a4', 'portrait');

        return $pdf->download('reporte-egg-express-' . now()->format('Ymd') . '.pdf');
    }
}

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; }
        h1 { text-align: center; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #ccc; padding: 4px 6px; }
        th { background: #f0f0f0; }
        .resumen td { font-weight: bold; }
    </style>
</head>
<body>
<h1>Reporte EGG EXPRESS</h1>
<p style="text-align:center">
    Período: {{ $desde ?? 'Inicio' }} — {{ $hasta ?? 'Hoy' }} |
    Generado: {{ now()->format('d/m/Y H:i') }}
</p>

<h3>Resumen de Ventas</h3>
<table class="resumen">
    <tr><td>Total pedidos</td><td>{{ $totalPedidos }}</td></tr>
    <tr><td>Pedidos pagados</td><td>{{ $pedidosPagados }}</td></tr>
    <tr><td>Total ventas pagadas</td><td>${{ number_format($totalVentas, 2) }}</td></tr>
</table>

<h3>Pedidos</h3>
<table>
    <thead>
        <tr><th>#</th><th>Cliente</th><th>Fecha</th><th>Total</th><th>Estado</th><th>Pago</th></tr>
    </thead>
    <tbody>
        @forelse($pedidos as $p)
        <tr>
            <td>{{ $p->id_pedido }}</td>
            <td>{{ $p->cliente->nombre }}</td>
            <td>{{ $p->fecha->format('d/m/Y') }}</td>
            <td>${{ number_format($p->total, 2) }}</td>
            <td>{{ strtoupper($p->estado) }}</td>
            <td>{{ $p->pago ? strtoupper($p->pago->metodo_pago) : '-' }}</td>
        </tr>
        @empty
        <tr><td colspan="6">Sin pedidos.</td></tr>
        @endforelse
    </tbody>
</table>

<h3>Movimientos de Inventario</h3>
<table>
    <thead>
        <tr><th>#</th><th>Producto</th><th>Tipo</th><th>Cantidad</th><th>Motivo</th><th>Fecha</th></tr>
    </thead>
    <tbody>
        @forelse($movimientos as $m)
        <tr>
            <td>{{ $m->id_movimiento }}</td>
            <td>{{ $m->producto->nombre }}</td>
            <td>{{ strtoupper($m->tipo_movimiento) }}</td>
            <td>{{ $m->cantidad }}</td>
            <td>{{ $m->motivo }}</td>
            <td>{{ $m->fecha->format('d/m/Y') }}</td>
        </tr>
        @empty
        <tr><td colspan="6">Sin movimientos.</td></tr>
        @endforelse
    </tbody>
</table>
</body>
</html>

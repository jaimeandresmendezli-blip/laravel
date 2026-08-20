@extends('layouts.admin')
@section('content')
<div class="space-y-6">

    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-3xl font-extrabold font-heading text-egg-900 tracking-tight">Reportes del Sistema</h1>
            <p class="text-stone-500 text-sm mt-1">Consolida ventas, pagos y movimientos de inventario.</p>
        </div>
        @if($totalPedidos > 0 || request()->hasAny(['fecha_desde','fecha_hasta']))
        <a href="{{ route('admin.reportes.pdf', request()->query()) }}"
           class="inline-flex items-center gap-2 bg-gradient-to-r from-rose-600 to-rose-700 hover:from-rose-500 hover:to-rose-600 text-white font-semibold px-5 py-2.5 rounded-xl shadow-md transition-all active:scale-[0.98]">
            <i class="fas fa-file-pdf"></i> Exportar PDF
        </a>
        @endif
    </div>

    {{-- Filtros --}}
    <div class="bg-white rounded-2xl p-5 border border-egg-200 shadow-sm">
        <form method="GET" action="{{ route('admin.reportes.index') }}" class="flex flex-wrap gap-4 items-end">
            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-stone-500 mb-1">Desde</label>
                <input type="date" name="fecha_desde" value="{{ $desde }}"
                       class="rounded-xl border border-stone-200 px-4 py-2.5 text-sm focus:border-egg-500 outline-none">
            </div>
            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-stone-500 mb-1">Hasta</label>
                <input type="date" name="fecha_hasta" value="{{ $hasta }}"
                       class="rounded-xl border border-stone-200 px-4 py-2.5 text-sm focus:border-egg-500 outline-none">
            </div>
            <button type="submit"
                    class="bg-gradient-to-r from-egg-900 to-egg-800 hover:from-egg-800 hover:to-egg-700 text-egg-400 font-semibold py-2.5 px-6 rounded-xl text-sm shadow-sm transition-all">
                <i class="fas fa-chart-bar mr-1"></i> Generar Reporte
            </button>
            <a href="{{ route('admin.reportes.index') }}"
               class="bg-stone-100 hover:bg-stone-200 text-stone-600 font-semibold py-2.5 px-4 rounded-xl text-sm transition-colors">
                Limpiar
            </a>
        </form>
    </div>

    {{-- KPI Cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
        <div class="bg-white rounded-2xl p-5 border border-egg-200 shadow-sm">
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs font-bold uppercase tracking-wider text-stone-400">Total Pedidos</span>
                <div class="w-10 h-10 rounded-xl bg-egg-100 text-egg-700 flex items-center justify-center">
                    <i class="fas fa-shopping-basket"></i>
                </div>
            </div>
            <h3 class="text-3xl font-extrabold font-heading text-egg-900">{{ $totalPedidos }}</h3>
            <p class="text-xs text-stone-400 mt-1">En el período</p>
        </div>

        <div class="bg-white rounded-2xl p-5 border border-egg-200 shadow-sm">
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs font-bold uppercase tracking-wider text-stone-400">Pedidos Pagados</span>
                <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center">
                    <i class="fas fa-check-circle"></i>
                </div>
            </div>
            <h3 class="text-3xl font-extrabold font-heading text-egg-900">{{ $pedidosPagados }}</h3>
            <p class="text-xs text-emerald-600 mt-1 font-medium">Completados</p>
        </div>

        <div class="bg-white rounded-2xl p-5 border border-egg-200 shadow-sm">
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs font-bold uppercase tracking-wider text-stone-400">Pendientes</span>
                <div class="w-10 h-10 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center">
                    <i class="fas fa-clock"></i>
                </div>
            </div>
            <h3 class="text-3xl font-extrabold font-heading text-egg-900">{{ $pedidosPendientes }}</h3>
            <p class="text-xs text-amber-600 mt-1 font-medium">Por procesar</p>
        </div>

        <div class="bg-white rounded-2xl p-5 border border-egg-200 shadow-sm">
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs font-bold uppercase tracking-wider text-stone-400">Total Ventas</span>
                <div class="w-10 h-10 rounded-xl bg-egg-100 text-egg-700 flex items-center justify-center">
                    <i class="fas fa-dollar-sign"></i>
                </div>
            </div>
            <h3 class="text-2xl font-extrabold font-heading text-egg-900">${{ number_format($totalVentas,2) }}</h3>
            <p class="text-xs text-egg-600 mt-1 font-medium">Solo pagados</p>
        </div>
    </div>

    {{-- Tabla pedidos --}}
    <div class="bg-white rounded-2xl border border-egg-200 shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-stone-200 bg-stone-50/60 flex items-center justify-between">
            <h3 class="font-heading font-bold text-egg-900">
                Pedidos {{ $desde ? "del $desde al $hasta" : '(todos)' }}
            </h3>
            <span class="text-xs text-stone-400">{{ $pedidos->count() }} registros</span>
        </div>
        @if($pedidos->isEmpty())
            <div class="py-12 text-center text-stone-400">
                <i class="fas fa-box-open text-4xl mb-3 text-stone-300 block"></i>
                No hay pedidos en este período.
            </div>
        @else
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm border-collapse">
                <thead>
                    <tr class="bg-stone-50 text-stone-500 uppercase text-[11px] font-bold tracking-wider border-b border-stone-200">
                        <th class="py-3 px-4">#</th>
                        <th class="py-3 px-4">Cliente</th>
                        <th class="py-3 px-4">Fecha</th>
                        <th class="py-3 px-4">Total</th>
                        <th class="py-3 px-4">Estado</th>
                        <th class="py-3 px-4">Método pago</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-stone-100">
                    @foreach($pedidos as $p)
                    <tr class="hover:bg-stone-50/80 transition-colors">
                        <td class="py-3 px-4 font-mono text-egg-700 font-semibold">#{{ $p->id_pedido }}</td>
                        <td class="py-3 px-4 font-medium text-stone-900">{{ $p->cliente->nombre ?? '-' }}</td>
                        <td class="py-3 px-4 text-stone-400 text-xs">{{ $p->fecha->format('d/m/Y') }}</td>
                        <td class="py-3 px-4 font-extrabold text-egg-800 font-heading">${{ number_format($p->total,2) }}</td>
                        <td class="py-3 px-4">
                            @if($p->estado==='pagado')
                                <span class="bg-emerald-100 text-emerald-800 text-xs px-2.5 py-1 rounded-full font-medium">Pagado</span>
                            @elseif($p->estado==='pendiente')
                                <span class="bg-amber-100 text-amber-800 text-xs px-2.5 py-1 rounded-full font-medium">Pendiente</span>
                            @else
                                <span class="bg-stone-100 text-stone-600 text-xs px-2.5 py-1 rounded-full font-medium">Cancelado</span>
                            @endif
                        </td>
                        <td class="py-3 px-4 text-stone-500 capitalize">{{ $p->pago ? $p->pago->metodo_pago : '-' }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif
    </div>

    {{-- Tabla movimientos --}}
    <div class="bg-white rounded-2xl border border-egg-200 shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-stone-200 bg-stone-50/60">
            <h3 class="font-heading font-bold text-egg-900">Movimientos de Inventario</h3>
        </div>
        @if($movimientos->isEmpty())
            <div class="py-10 text-center text-stone-400">No hay movimientos en este período.</div>
        @else
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm border-collapse">
                <thead>
                    <tr class="bg-stone-50 text-stone-500 uppercase text-[11px] font-bold tracking-wider border-b border-stone-200">
                        <th class="py-3 px-4">Producto</th>
                        <th class="py-3 px-4">Tipo</th>
                        <th class="py-3 px-4">Cantidad</th>
                        <th class="py-3 px-4">Motivo</th>
                        <th class="py-3 px-4">Fecha</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-stone-100">
                    @foreach($movimientos as $m)
                    <tr class="hover:bg-stone-50/80 transition-colors">
                        <td class="py-3 px-4 font-medium text-egg-900">{{ $m->producto->nombre }}</td>
                        <td class="py-3 px-4">
                            @if($m->tipo_movimiento==='entrada')
                                <span class="bg-emerald-50 text-emerald-700 text-xs px-2.5 py-1 rounded-full font-semibold border border-emerald-200">↑ Entrada</span>
                            @else
                                <span class="bg-rose-50 text-rose-700 text-xs px-2.5 py-1 rounded-full font-semibold border border-rose-200">↓ Salida</span>
                            @endif
                        </td>
                        <td class="py-3 px-4 font-bold text-stone-800">{{ $m->cantidad }}</td>
                        <td class="py-3 px-4 text-stone-500">{{ $m->motivo }}</td>
                        <td class="py-3 px-4 text-stone-400 text-xs">{{ $m->fecha->format('d/m/Y') }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif
    </div>

</div>
@endsection

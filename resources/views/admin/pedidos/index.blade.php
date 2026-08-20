@extends('layouts.admin')
@section('content')
<div class="space-y-6">
    <div>
        <h1 class="text-3xl font-extrabold font-heading text-egg-900 tracking-tight">Pedidos</h1>
        <p class="text-stone-500 text-sm mt-1">Gestiona y actualiza los pedidos de tus clientes.</p>
    </div>

    {{-- Filtros --}}
    <div class="bg-white rounded-2xl p-5 border border-egg-200 shadow-sm">
        <form method="GET" class="flex flex-wrap gap-3 items-end">
            <div>
                <label class="block text-xs font-semibold text-stone-500 mb-1">Estado pago</label>
                <select name="estado" class="rounded-xl border border-stone-200 px-4 py-2.5 text-sm focus:border-egg-500 outline-none bg-white">
                    <option value="">Todos</option>
                    <option value="pendiente" {{ request('estado')=='pendiente'?'selected':'' }}>Pendiente</option>
                    <option value="pagado" {{ request('estado')=='pagado'?'selected':'' }}>Pagado</option>
                    <option value="cancelado" {{ request('estado')=='cancelado'?'selected':'' }}>Cancelado</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-semibold text-stone-500 mb-1">Estado entrega</label>
                <select name="estado_entrega" class="rounded-xl border border-stone-200 px-4 py-2.5 text-sm focus:border-egg-500 outline-none bg-white">
                    <option value="">Todos</option>
                    <option value="pendiente" {{ request('estado_entrega')=='pendiente'?'selected':'' }}>Pendiente</option>
                    <option value="en_camino" {{ request('estado_entrega')=='en_camino'?'selected':'' }}>En camino</option>
                    <option value="entregado" {{ request('estado_entrega')=='entregado'?'selected':'' }}>Entregado</option>
                    <option value="cancelado" {{ request('estado_entrega')=='cancelado'?'selected':'' }}>Cancelado</option>
                </select>
            </div>
            <button type="submit" class="bg-egg-900 hover:bg-egg-800 text-egg-400 font-semibold py-2.5 px-5 rounded-xl text-sm">
                <i class="fas fa-filter mr-1"></i> Filtrar
            </button>
            <a href="{{ route('admin.pedidos.index') }}" class="bg-stone-100 hover:bg-stone-200 text-stone-600 font-semibold py-2.5 px-4 rounded-xl text-sm">
                Limpiar
            </a>
        </form>
    </div>

    <div class="bg-white rounded-2xl border border-egg-200 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm border-collapse">
                <thead>
                    <tr class="bg-stone-50 text-stone-500 uppercase text-[11px] font-bold tracking-wider border-b border-stone-200">
                        <th class="py-4 px-4">#Pedido</th>
                        <th class="py-4 px-4">Cliente</th>
                        <th class="py-4 px-4">Fecha</th>
                        <th class="py-4 px-4">Total</th>
                        <th class="py-4 px-4">Pago</th>
                        <th class="py-4 px-4">Entrega</th>
                        <th class="py-4 px-4 text-center">Acción</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-stone-100">
                    @forelse($pedidos as $p)
                    <tr class="hover:bg-stone-50/80 transition-colors">
                        <td class="py-3 px-4 font-mono font-semibold text-egg-800">#{{ $p->id_pedido }}</td>
                        <td class="py-3 px-4 font-medium text-stone-900">{{ $p->cliente->nombre ?? '-' }}</td>
                        <td class="py-3 px-4 text-stone-400 text-xs">{{ $p->fecha->format('d/m/Y H:i') }}</td>
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
                        <td class="py-3 px-4">
                            @php
                                $colors = ['pendiente'=>'bg-amber-50 text-amber-700', 'en_camino'=>'bg-blue-50 text-blue-700', 'entregado'=>'bg-emerald-50 text-emerald-700', 'cancelado'=>'bg-stone-100 text-stone-600'];
                                $labels = ['pendiente'=>'Pendiente', 'en_camino'=>'En camino', 'entregado'=>'Entregado', 'cancelado'=>'Cancelado'];
                            @endphp
                            <span class="text-xs px-2.5 py-1 rounded-full font-medium {{ $colors[$p->estado_entrega] ?? 'bg-stone-100 text-stone-600' }}">
                                {{ $labels[$p->estado_entrega] ?? $p->estado_entrega }}
                            </span>
                        </td>
                        <td class="py-3 px-4 text-center">
                            <a href="{{ route('admin.pedidos.show', $p->id_pedido) }}"
                               class="inline-flex items-center gap-1 text-egg-700 hover:text-egg-900 bg-egg-50 hover:bg-egg-100 px-2.5 py-1 rounded-lg text-xs font-semibold transition-colors">
                                <i class="fas fa-eye"></i> Ver
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="7" class="py-10 text-center text-stone-400">No hay pedidos.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

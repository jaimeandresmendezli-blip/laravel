@extends('layouts.cliente')
@section('content')
<div class="space-y-6">
    <div>
        <h1 class="text-3xl font-extrabold font-heading text-egg-900 tracking-tight">Mis Pedidos</h1>
        <p class="text-stone-500 text-sm mt-1">Historial y seguimiento de tus pedidos.</p>
    </div>

    {{-- Resumen --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-5">
        <div class="bg-white rounded-2xl p-5 border border-egg-200 shadow-sm flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-egg-100 text-egg-700 flex items-center justify-center text-xl flex-shrink-0">
                <i class="fas fa-shopping-basket"></i>
            </div>
            <div>
                <p class="text-2xl font-extrabold font-heading text-egg-900">{{ $totalPedidos }}</p>
                <p class="text-xs text-stone-500">Total pedidos</p>
            </div>
        </div>
        <div class="bg-white rounded-2xl p-5 border border-egg-200 shadow-sm flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center text-xl flex-shrink-0">
                <i class="fas fa-clock"></i>
            </div>
            <div>
                <p class="text-2xl font-extrabold font-heading text-egg-900">{{ $pedidosPendientes }}</p>
                <p class="text-xs text-stone-500">Pendientes</p>
            </div>
        </div>
        <div class="bg-white rounded-2xl p-5 border border-egg-200 shadow-sm flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-xl flex-shrink-0">
                <i class="fas fa-check-circle"></i>
            </div>
            <div>
                <p class="text-2xl font-extrabold font-heading text-egg-900">{{ $pedidosPagados }}</p>
                <p class="text-xs text-stone-500">Pagados</p>
            </div>
        </div>
    </div>

    {{-- Lista pedidos --}}
    @if($pedidos->isEmpty())
        <div class="bg-white rounded-2xl border border-egg-200 shadow-sm py-16 text-center">
            <div class="text-6xl mb-3 select-none">📦</div>
            <h3 class="text-xl font-bold font-heading text-egg-900 mb-2">No tienes pedidos aún</h3>
            <a href="{{ route('cliente.catalogo.index') }}"
               class="mt-4 inline-flex items-center gap-2 bg-egg-900 text-egg-400 font-semibold px-5 py-2.5 rounded-xl shadow-sm text-sm">
                <i class="fas fa-egg"></i> Explorar catálogo
            </a>
        </div>
    @else
    <div class="bg-white rounded-2xl border border-egg-200 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm border-collapse">
                <thead>
                    <tr class="bg-stone-50 text-stone-500 uppercase text-[11px] font-bold tracking-wider border-b border-stone-200">
                        <th class="py-4 px-4">#Pedido</th>
                        <th class="py-4 px-4">Fecha</th>
                        <th class="py-4 px-4">Total</th>
                        <th class="py-4 px-4">Estado Pago</th>
                        <th class="py-4 px-4">Estado Entrega</th>
                        <th class="py-4 px-4 text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-stone-100">
                    @foreach($pedidos as $p)
                    <tr class="hover:bg-stone-50/80 transition-colors">
                        <td class="py-3 px-4 font-mono font-semibold text-egg-700">#{{ $p->id_pedido }}</td>
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
                                $ec = ['pendiente'=>'bg-amber-50 text-amber-700','en_camino'=>'bg-blue-50 text-blue-700','entregado'=>'bg-emerald-50 text-emerald-700','cancelado'=>'bg-stone-100 text-stone-500'];
                                $el = ['pendiente'=>'Pendiente','en_camino'=>'🚚 En camino','entregado'=>'✅ Entregado','cancelado'=>'Cancelado'];
                            @endphp
                            <span class="text-xs px-2.5 py-1 rounded-full font-medium {{ $ec[$p->estado_entrega] ?? 'bg-stone-100 text-stone-500' }}">
                                {{ $el[$p->estado_entrega] ?? $p->estado_entrega }}
                            </span>
                        </td>
                        <td class="py-3 px-4 text-center">
                            <div class="flex items-center justify-center gap-1">
                                <a href="{{ route('cliente.pedidos.show', $p->id_pedido) }}"
                                   class="inline-flex items-center gap-1 text-egg-700 hover:text-egg-900 bg-egg-50 hover:bg-egg-100 px-2.5 py-1 rounded-lg text-xs font-semibold transition-colors">
                                    <i class="fas fa-eye"></i> Ver
                                </a>
                                @if($p->estado==='pendiente')
                                <form method="POST" action="{{ route('cliente.pedidos.cancelar', $p->id_pedido) }}" class="inline">
                                    @csrf @method('DELETE')
                                        <button type="submit"
                                            data-swal-confirm="¿Cancelar este pedido?"
                                            class="inline-flex items-center gap-1 text-rose-500 hover:text-rose-700 bg-rose-50 hover:bg-rose-100 px-2.5 py-1 rounded-lg text-xs font-semibold transition-colors">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif
</div>
@endsection

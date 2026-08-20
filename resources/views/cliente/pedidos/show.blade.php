@extends('layouts.cliente')
@section('content')
<div class="space-y-6">
    <div class="flex items-center gap-2 text-sm text-stone-500">
        <a href="{{ route('cliente.pedidos.index') }}" class="hover:text-egg-700">Mis Pedidos</a>
        <i class="fas fa-chevron-right text-[10px]"></i>
        <span class="text-stone-700 font-medium">Pedido #{{ $pedido->id_pedido }}</span>
    </div>

    <div class="flex items-center justify-between">
        <h1 class="text-3xl font-extrabold font-heading text-egg-900 tracking-tight">Pedido #{{ $pedido->id_pedido }}</h1>
        @if($pedido->estado==='pendiente')
        <form method="POST" action="{{ route('cliente.pedidos.cancelar', $pedido->id_pedido) }}">
            @csrf @method('DELETE')
            <button type="submit" onclick="return confirm('¿Estás seguro de cancelar este pedido? El stock será devuelto.')"
                    class="inline-flex items-center gap-2 bg-rose-50 hover:bg-rose-100 text-rose-600 hover:text-rose-700 font-semibold px-4 py-2.5 rounded-xl border border-rose-200 text-sm transition-all">
                <i class="fas fa-times-circle"></i> Cancelar Pedido
            </button>
        </form>
        @endif
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- Info principal --}}
        <div class="lg:col-span-2 space-y-6">

            {{-- Estados --}}
            <div class="bg-white rounded-2xl border border-egg-200 shadow-sm p-6">
                <div class="grid grid-cols-2 gap-4">
                    <div class="text-center p-4 rounded-xl bg-stone-50 border border-stone-200">
                        <p class="text-xs text-stone-400 font-bold uppercase mb-2">Estado del Pago</p>
                        @if($pedido->estado==='pagado')
                            <span class="inline-flex items-center gap-2 bg-emerald-100 text-emerald-800 font-bold px-4 py-2 rounded-full text-sm"><i class="fas fa-check-circle"></i> Pagado</span>
                        @elseif($pedido->estado==='pendiente')
                            <span class="inline-flex items-center gap-2 bg-amber-100 text-amber-800 font-bold px-4 py-2 rounded-full text-sm"><i class="fas fa-clock"></i> Pendiente</span>
                        @else
                            <span class="inline-flex items-center gap-2 bg-stone-100 text-stone-600 font-bold px-4 py-2 rounded-full text-sm"><i class="fas fa-ban"></i> Cancelado</span>
                        @endif
                    </div>
                    <div class="text-center p-4 rounded-xl bg-stone-50 border border-stone-200">
                        <p class="text-xs text-stone-400 font-bold uppercase mb-2">Estado de Entrega</p>
                        @php
                            $colors = ['pendiente'=>'bg-amber-100 text-amber-800','en_camino'=>'bg-blue-100 text-blue-800','entregado'=>'bg-emerald-100 text-emerald-800','cancelado'=>'bg-stone-100 text-stone-600'];
                            $icons  = ['pendiente'=>'fa-clock','en_camino'=>'fa-truck','entregado'=>'fa-check-circle','cancelado'=>'fa-ban'];
                            $labels = ['pendiente'=>'Pendiente','en_camino'=>'En camino','entregado'=>'Entregado','cancelado'=>'Cancelado'];
                        @endphp
                        <span class="inline-flex items-center gap-2 {{ $colors[$pedido->estado_entrega] ?? 'bg-stone-100 text-stone-600' }} font-bold px-4 py-2 rounded-full text-sm">
                            <i class="fas {{ $icons[$pedido->estado_entrega] ?? 'fa-question' }}"></i>
                            {{ $labels[$pedido->estado_entrega] ?? $pedido->estado_entrega }}
                        </span>
                    </div>
                </div>
            </div>

            {{-- Productos --}}
            <div class="bg-white rounded-2xl border border-egg-200 shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-stone-200 bg-stone-50/60">
                    <h3 class="font-heading font-bold text-egg-900">Productos del Pedido</h3>
                </div>
                <div class="divide-y divide-stone-100">
                    @foreach($pedido->detalles as $d)
                    <div class="p-4 flex items-center gap-4">
                        <div class="w-12 h-12 rounded-xl bg-amber-50 flex items-center justify-center flex-shrink-0 border border-stone-100">
                            @if($d->producto->imagen)
                                <img src="{{ asset('storage/'.$d->producto->imagen) }}" class="max-h-full object-contain p-1">
                            @else
                                <span class="text-xl">🥚</span>
                            @endif
                        </div>
                        <div class="flex-1">
                            <p class="font-bold text-egg-900">{{ $d->producto->nombre }}</p>
                            <p class="text-xs text-stone-400">{{ $d->cantidad }} × ${{ number_format($d->precio,2) }}</p>
                        </div>
                        <p class="font-bold text-egg-800">${{ number_format($d->subtotal,2) }}</p>
                    </div>
                    @endforeach
                </div>
                <div class="px-4 py-4 bg-egg-50 border-t border-egg-200 flex justify-between items-center">
                    <span class="font-bold text-egg-900 font-heading">TOTAL</span>
                    <span class="font-extrabold text-egg-800 text-xl font-heading">${{ number_format($pedido->total,2) }}</span>
                </div>
            </div>
        </div>

        {{-- Panel lateral --}}
        <div class="space-y-4">

            {{-- Datos entrega --}}
            <div class="bg-white rounded-2xl border border-egg-200 shadow-sm p-6 space-y-3">
                <h3 class="font-heading font-bold text-egg-900">Datos de Entrega</h3>
                <div class="space-y-2 text-sm">
                    <div class="bg-stone-50 rounded-xl p-3">
                        <span class="text-xs text-stone-400 block">Dirección</span>
                        <strong class="text-stone-800">{{ $pedido->direccion_entrega }}</strong>
                    </div>
                    <div class="bg-stone-50 rounded-xl p-3">
                        <span class="text-xs text-stone-400 block">Barrio</span>
                        <strong class="text-stone-800">{{ $pedido->barrio }}</strong>
                    </div>
                    <div class="bg-stone-50 rounded-xl p-3">
                        <span class="text-xs text-stone-400 block">Teléfono</span>
                        <strong class="text-stone-800">{{ $pedido->telefono_entrega }}</strong>
                    </div>
                    @if($pedido->referencia)
                    <div class="bg-stone-50 rounded-xl p-3">
                        <span class="text-xs text-stone-400 block">Referencia</span>
                        <strong class="text-stone-800">{{ $pedido->referencia }}</strong>
                    </div>
                    @endif
                    @if($pedido->observaciones)
                    <div class="bg-stone-50 rounded-xl p-3">
                        <span class="text-xs text-stone-400 block">Observaciones</span>
                        <strong class="text-stone-800">{{ $pedido->observaciones }}</strong>
                    </div>
                    @endif
                </div>
            </div>

            {{-- Pago --}}
            @if($pedido->pago)
            <div class="bg-white rounded-2xl border border-egg-200 shadow-sm p-6 space-y-3">
                <h3 class="font-heading font-bold text-egg-900">Información de Pago</h3>
                <div class="space-y-2 text-sm">
                    <div class="flex justify-between">
                        <span class="text-stone-500">Método:</span>
                        <span class="font-semibold text-stone-800 capitalize">{{ $pedido->pago->metodo_pago }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-stone-500">Monto:</span>
                        <span class="font-bold text-egg-800">${{ number_format($pedido->pago->monto,2) }}</span>
                    </div>
                    @if($pedido->pago->referencia_pago)
                    <div class="flex justify-between">
                        <span class="text-stone-500">Referencia:</span>
                        <span class="font-mono text-xs text-stone-700">{{ $pedido->pago->referencia_pago }}</span>
                    </div>
                    @endif
                </div>
            </div>
            @endif

            <a href="{{ route('cliente.pedidos.index') }}"
               class="block text-center bg-stone-100 hover:bg-stone-200 text-stone-700 font-semibold py-3 rounded-xl text-sm transition-colors">
                ← Volver a mis pedidos
            </a>
        </div>
    </div>
</div>
@endsection

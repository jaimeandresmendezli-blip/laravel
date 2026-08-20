@extends('layouts.cliente')
@section('content')
<div class="space-y-6">

    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-3xl font-extrabold font-heading text-egg-900 tracking-tight">Mi Carrito</h1>
            <p class="text-stone-500 text-sm mt-1">Revisa tus productos antes de confirmar el pedido.</p>
        </div>
        <a href="{{ route('cliente.catalogo.index') }}"
           class="inline-flex items-center gap-2 bg-white hover:bg-stone-50 text-stone-700 font-semibold px-5 py-2.5 rounded-xl border border-stone-200 shadow-sm transition-all text-sm">
            <i class="fas fa-arrow-left text-stone-400"></i> Seguir comprando
        </a>
    </div>

    @if($detalles->isEmpty())
        <div class="bg-white rounded-2xl border border-egg-200 shadow-sm py-20 text-center">
            <div class="text-7xl mb-4 select-none">🛒</div>
            <h3 class="text-xl font-bold font-heading text-egg-900 mb-2">Tu carrito está vacío</h3>
            <p class="text-stone-500 text-sm mb-6">Agrega productos desde el catálogo para empezar.</p>
            <a href="{{ route('cliente.catalogo.index') }}"
               class="inline-flex items-center gap-2 bg-gradient-to-r from-egg-900 to-egg-800 text-egg-400 font-semibold px-6 py-3 rounded-xl shadow-md transition-all">
                <i class="fas fa-egg"></i> Ver Catálogo
            </a>
        </div>
    @else

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- Tabla carrito --}}
        <div class="lg:col-span-2 bg-white rounded-2xl border border-egg-200 shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-stone-200 bg-stone-50/60 flex items-center justify-between">
                <h3 class="font-heading font-bold text-egg-900">Productos ({{ $detalles->count() }})</h3>
                <form method="POST" action="{{ route('cliente.carrito.vaciar') }}">
                    @csrf @method('DELETE')
                    <button type="submit" onclick="return confirm('¿Vaciar todo el carrito?')"
                            class="text-xs text-rose-500 hover:text-rose-700 font-semibold flex items-center gap-1 hover:bg-rose-50 px-3 py-1.5 rounded-lg transition-colors">
                        <i class="fas fa-trash"></i> Vaciar carrito
                    </button>
                </form>
            </div>

            <div class="divide-y divide-stone-100">
                @foreach($detalles as $d)
                <div class="p-5 flex items-center gap-4">
                    {{-- Imagen --}}
                    <div class="w-14 h-14 rounded-xl bg-amber-50 flex items-center justify-center flex-shrink-0 border border-stone-200">
                        @if($d->producto->imagen)
                            <img src="{{ asset('storage/'.$d->producto->imagen) }}" class="max-h-full max-w-full object-contain p-1">
                        @else
                            <span class="text-2xl">🥚</span>
                        @endif
                    </div>

                    {{-- Info --}}
                    <div class="flex-1 min-w-0">
                        <p class="font-bold text-egg-900 font-heading truncate">{{ $d->producto->nombre }}</p>
                        <p class="text-xs text-stone-400 mt-0.5">${{ number_format($d->precio_unitario,2) }} / unidad</p>
                    </div>

                    {{-- Cantidad --}}
                    <form method="POST" action="{{ route('cliente.carrito.actualizar', $d->id_detalle_carrito) }}" class="flex items-center gap-2">
                        @csrf @method('PATCH')
                        <input type="number" name="cantidad" value="{{ $d->cantidad }}"
                               min="1" max="{{ $d->producto->cantidad }}"
                               class="w-16 text-center rounded-xl border border-stone-200 text-sm font-bold py-1.5 focus:border-egg-500 outline-none"
                               onchange="this.form.submit()">
                    </form>

                    {{-- Subtotal --}}
                    <div class="text-right min-w-[80px]">
                        <p class="font-extrabold text-egg-800 font-heading">${{ number_format($d->subtotal,2) }}</p>
                    </div>

                    {{-- Eliminar --}}
                    <form method="POST" action="{{ route('cliente.carrito.eliminar', $d->id_detalle_carrito) }}">
                        @csrf @method('DELETE')
                        <button type="submit" class="p-2 text-rose-400 hover:text-rose-600 hover:bg-rose-50 rounded-lg transition-colors">
                            <i class="fas fa-times"></i>
                        </button>
                    </form>
                </div>
                @endforeach
            </div>
        </div>

        {{-- Resumen --}}
        <div class="space-y-4">
            <div class="bg-white rounded-2xl border border-egg-200 shadow-sm p-6 space-y-4">
                <h3 class="font-heading font-bold text-egg-900 text-lg">Resumen del Pedido</h3>

                <div class="space-y-3 text-sm">
                    @foreach($detalles as $d)
                    <div class="flex justify-between text-stone-600">
                        <span class="truncate mr-2">{{ $d->producto->nombre }} ×{{ $d->cantidad }}</span>
                        <span class="font-medium flex-shrink-0">${{ number_format($d->subtotal,2) }}</span>
                    </div>
                    @endforeach
                </div>

                <div class="border-t border-stone-200 pt-4 flex justify-between items-center">
                    <span class="font-bold text-egg-900 text-lg font-heading">Total</span>
                    <span class="font-extrabold text-egg-800 text-2xl font-heading">${{ number_format($total,2) }}</span>
                </div>

                <a href="{{ route('cliente.pedidos.create') }}"
                   class="block w-full text-center bg-gradient-to-r from-egg-900 to-egg-800 hover:from-egg-800 hover:to-egg-700 text-egg-400 font-bold py-4 px-5 rounded-xl shadow-lg hover:shadow-xl transition-all active:scale-[0.98] font-heading">
                    <i class="fas fa-check-circle mr-2"></i> Confirmar Pedido
                </a>
            </div>

            <div class="bg-egg-50 border border-egg-200 rounded-2xl p-4 text-xs text-egg-800 space-y-2">
                <p class="font-bold flex items-center gap-2"><i class="fas fa-truck text-egg-600"></i> Entrega a domicilio</p>
                <p class="text-egg-700 leading-relaxed">Al confirmar ingresarás tu dirección de entrega y método de pago.</p>
            </div>
        </div>
    </div>

    @endif
</div>
@endsection

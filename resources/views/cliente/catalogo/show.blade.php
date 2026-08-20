@extends('layouts.cliente')
@section('content')
<div class="space-y-6">
    <div class="flex items-center gap-2 text-sm text-stone-500">
        <a href="{{ route('cliente.catalogo.index') }}" class="hover:text-egg-700">Catálogo</a>
        <i class="fas fa-chevron-right text-[10px]"></i>
        <span class="text-stone-700 font-medium">{{ $producto->nombre }}</span>
    </div>

    <div class="bg-white rounded-2xl border border-egg-200 shadow-sm overflow-hidden">
        <div class="grid grid-cols-1 md:grid-cols-2">
            {{-- Imagen --}}
            <div class="bg-amber-50 flex items-center justify-center p-12 min-h-72">
                @if($producto->imagen)
                    <img src="{{ asset('storage/'.$producto->imagen) }}" alt="{{ $producto->nombre }}"
                         class="max-h-64 max-w-full object-contain drop-shadow-lg">
                @else
                    <div class="text-9xl select-none opacity-50">🥚</div>
                @endif
            </div>

            {{-- Detalle --}}
            <div class="p-8 space-y-6">
                <div>
                    @if($producto->tipo_huevo)
                        <span class="inline-block bg-egg-100 text-egg-800 text-xs font-bold px-3 py-1 rounded-full mb-3">
                            {{ $producto->tipo_huevo }}
                        </span>
                    @endif
                    <h1 class="text-3xl font-extrabold font-heading text-egg-900 leading-tight">{{ $producto->nombre }}</h1>
                    @if($producto->presentacion)
                        <p class="text-stone-400 text-sm mt-1">{{ $producto->presentacion }}</p>
                    @endif
                </div>

                <p class="text-stone-600 leading-relaxed">
                    {{ $producto->descripcion ?? 'Huevo fresco de excelente calidad, ideal para consumo diario.' }}
                </p>

                <div class="flex items-center gap-4 py-4 border-y border-stone-100">
                    <div>
                        <p class="text-xs text-stone-400 font-semibold uppercase">Precio</p>
                        <p class="text-4xl font-extrabold text-egg-800 font-heading">${{ number_format($producto->precio,2) }}</p>
                    </div>
                    <div class="border-l border-stone-200 pl-4">
                        <p class="text-xs text-stone-400 font-semibold uppercase">Disponible</p>
                        @if($producto->cantidad <= 0)
                            <span class="text-rose-600 font-bold text-lg">Agotado</span>
                        @elseif($producto->cantidad <= 5)
                            <span class="text-amber-600 font-bold text-lg">{{ $producto->cantidad }} 🔔</span>
                        @else
                            <span class="text-emerald-600 font-bold text-lg">{{ $producto->cantidad }} uds.</span>
                        @endif
                    </div>
                </div>

                @if($producto->cantidad > 0)
                <form method="POST" action="{{ route('cliente.carrito.agregar') }}" class="flex gap-3 items-end">
                    @csrf
                    <input type="hidden" name="id_producto" value="{{ $producto->id_producto }}">
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-stone-500 mb-2">Cantidad</label>
                        <input type="number" name="cantidad" value="1" min="1" max="{{ $producto->cantidad }}"
                               class="w-20 text-center rounded-xl border border-stone-200 px-3 py-3 text-sm font-bold focus:border-egg-500 outline-none">
                    </div>
                    <button type="submit"
                            class="flex-1 bg-gradient-to-r from-egg-900 to-egg-800 hover:from-egg-800 hover:to-egg-700 text-egg-400 font-bold py-3 px-5 rounded-xl shadow-md transition-all active:scale-[0.98] flex items-center justify-center gap-2">
                        <i class="fas fa-shopping-cart"></i> Agregar al carrito
                    </button>
                </form>
                @else
                <div class="bg-rose-50 border border-rose-200 text-rose-700 rounded-xl p-4 text-sm font-medium text-center">
                    Este producto está agotado actualmente.
                </div>
                @endif

                <a href="{{ route('cliente.catalogo.index') }}"
                   class="block text-center text-sm text-stone-500 hover:text-egg-700 transition-colors font-medium">
                    ← Volver al catálogo
                </a>
            </div>
        </div>
    </div>
</div>
@endsection

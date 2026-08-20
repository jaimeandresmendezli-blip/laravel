@extends('layouts.cliente')
@section('content')
<div class="space-y-6">

    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-3xl font-extrabold font-heading text-egg-900 tracking-tight">Catálogo de Huevos</h1>
            <p class="text-stone-500 text-sm mt-1">Productos frescos disponibles para pedir.</p>
        </div>
        <a href="{{ route('cliente.carrito.index') }}"
           class="inline-flex items-center gap-2 bg-egg-900 hover:bg-egg-800 text-egg-400 font-semibold px-5 py-2.5 rounded-xl shadow-md transition-all">
            <i class="fas fa-shopping-cart"></i> Mi Carrito
        </a>
    </div>

    {{-- Búsqueda --}}
    <div class="bg-white rounded-2xl p-4 border border-egg-200 shadow-sm">
        <form method="GET" action="{{ route('cliente.catalogo.index') }}" class="flex gap-3">
            <div class="flex-1 relative">
                <i class="fas fa-search absolute left-4 top-3 text-stone-400"></i>
                <input type="text" name="buscar" value="{{ request('buscar') }}"
                       class="w-full pl-11 pr-4 py-2.5 rounded-xl border border-stone-200 text-sm focus:border-egg-500 focus:ring-2 focus:ring-egg-400/20 outline-none transition-all"
                       placeholder="Buscar huevos por nombre, tipo, presentación...">
            </div>
            <button type="submit" class="bg-egg-900 hover:bg-egg-800 text-egg-400 font-semibold px-5 py-2.5 rounded-xl text-sm transition-colors">
                Buscar
            </button>
            @if(request('buscar'))
            <a href="{{ route('cliente.catalogo.index') }}" class="bg-stone-100 hover:bg-stone-200 text-stone-600 font-semibold px-4 py-2.5 rounded-xl text-sm transition-colors">
                <i class="fas fa-times"></i>
            </a>
            @endif
        </form>
    </div>

    {{-- Grid de productos --}}
    @if($productos->isEmpty())
        <div class="bg-white rounded-2xl border border-egg-200 shadow-sm py-16 text-center">
            <div class="text-6xl mb-4">🥚</div>
            <h3 class="text-xl font-bold font-heading text-egg-900 mb-2">
                {{ request('buscar') ? 'Sin resultados' : 'Catálogo vacío' }}
            </h3>
            <p class="text-stone-500 text-sm">
                {{ request('buscar') ? 'No se encontraron productos para "'.request('buscar').'".' : 'No hay productos disponibles en este momento.' }}
            </p>
            @if(request('buscar'))
            <a href="{{ route('cliente.catalogo.index') }}" class="mt-4 inline-block text-egg-700 font-semibold text-sm hover:underline">
                Ver todos los productos
            </a>
            @endif
        </div>
    @else
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
        @foreach($productos as $p)
        <div class="bg-white rounded-2xl overflow-hidden shadow-sm hover:shadow-xl transition-all duration-300 border border-stone-100 flex flex-col group hover:-translate-y-1">
            {{-- Imagen --}}
            <div class="h-44 bg-amber-50 relative overflow-hidden flex items-center justify-center p-4">
                @if($p->imagen)
                    <img src="{{ asset('storage/'.$p->imagen) }}" alt="{{ $p->nombre }}"
                         class="max-h-full max-w-full object-contain group-hover:scale-105 transition-transform duration-300">
                @else
                    <div class="text-7xl select-none opacity-60">🥚</div>
                @endif

                {{-- Badges --}}
                @if($p->cantidad <= 0)
                    <span class="absolute top-2 left-2 bg-rose-600 text-white text-xs px-2.5 py-1 rounded-full font-bold shadow">Agotado</span>
                @elseif($p->cantidad <= 5)
                    <span class="absolute top-2 left-2 bg-amber-500 text-white text-xs px-2.5 py-1 rounded-full font-bold shadow">Stock bajo</span>
                @endif

                @if($p->tipo_huevo)
                    <span class="absolute top-2 right-2 bg-egg-900/80 text-egg-300 text-xs px-2 py-0.5 rounded-full font-medium backdrop-blur-sm">
                        {{ $p->tipo_huevo }}
                    </span>
                @endif
            </div>

            {{-- Info --}}
            <div class="p-5 flex-1 flex flex-col justify-between">
                <div>
                    @if($p->presentacion)
                        <p class="text-[10px] font-bold uppercase tracking-wider text-stone-400 mb-1">{{ $p->presentacion }}</p>
                    @endif
                    <h4 class="font-bold text-base text-egg-900 font-heading line-clamp-2 mb-2 group-hover:text-egg-700 transition-colors">
                        {{ $p->nombre }}
                    </h4>
                    <p class="text-stone-500 text-xs line-clamp-2 mb-4 leading-relaxed">
                        {{ $p->descripcion ?? 'Huevo fresco de la mejor calidad.' }}
                    </p>
                </div>

                <div class="border-t border-stone-100 pt-4 space-y-3">
                    <div class="flex justify-between items-center">
                        <span class="text-2xl font-extrabold text-egg-800 font-heading">
                            ${{ number_format($p->precio, 2) }}
                        </span>
                        @if($p->cantidad > 0)
                            <span class="text-xs font-semibold px-2 py-1 rounded-lg bg-emerald-50 text-emerald-700 border border-emerald-200">
                                {{ $p->cantidad }} disp.
                            </span>
                        @else
                            <span class="text-xs font-semibold px-2 py-1 rounded-lg bg-rose-50 text-rose-700 border border-rose-200">
                                Agotado
                            </span>
                        @endif
                    </div>

                    @if($p->cantidad > 0)
                    <form method="POST" action="{{ route('cliente.carrito.agregar') }}" class="flex gap-2">
                        @csrf
                        <input type="hidden" name="id_producto" value="{{ $p->id_producto }}">
                        <input type="number" name="cantidad" value="1" min="1" max="{{ $p->cantidad }}"
                               class="w-16 text-center rounded-xl border border-stone-200 text-sm font-bold focus:border-egg-500 outline-none py-2">
                        <button type="submit"
                                class="flex-1 bg-gradient-to-r from-egg-900 to-egg-800 hover:from-egg-800 hover:to-egg-700 text-egg-400 font-semibold py-2 px-3 rounded-xl text-sm transition-all shadow-sm active:scale-[0.98] flex items-center justify-center gap-1">
                            <i class="fas fa-shopping-cart text-xs"></i> Agregar
                        </button>
                    </form>
                    @else
                    <button disabled class="w-full bg-stone-100 text-stone-400 font-semibold py-2 px-3 rounded-xl text-sm cursor-not-allowed">
                        No disponible
                    </button>
                    @endif

                    <a href="{{ route('cliente.catalogo.show', $p->id_producto) }}"
                       class="block text-center text-xs text-egg-700 hover:text-egg-900 font-semibold transition-colors">
                        Ver detalle →
                    </a>
                </div>
            </div>
        </div>
        @endforeach
    </div>
    @endif
</div>
@endsection

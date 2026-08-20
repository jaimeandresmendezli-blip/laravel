@extends('layouts.cliente')
@section('content')
<div class="space-y-8">

    {{-- Hero banner --}}
    <div class="relative overflow-hidden bg-gradient-to-r from-egg-900 via-egg-800 to-egg-950 rounded-3xl p-8 lg:p-10 text-white shadow-xl">
        <div class="absolute -right-20 -bottom-20 w-80 h-80 bg-egg-400/20 rounded-full blur-3xl"></div>
        <div class="absolute top-4 right-8 text-7xl opacity-20 select-none">🥚</div>
        <div class="relative z-10 flex flex-col lg:flex-row items-center justify-between gap-6">
            <div class="space-y-3 text-center lg:text-left">
                <span class="inline-flex items-center gap-2 text-xs font-semibold px-3 py-1 rounded-full bg-egg-400/20 text-egg-300 border border-egg-400/30">
                    🥚 Bienvenido a EGG EXPRESS
                </span>
                <h1 class="text-3xl lg:text-4xl font-extrabold font-heading">¡Hola, {{ Auth::user()->nombre }}!</h1>
                <p class="text-stone-300 max-w-xl text-sm lg:text-base leading-relaxed">
                    Explora nuestro catálogo de huevos frescos y realiza tu pedido con entrega a domicilio.
                </p>
            </div>
            <a href="{{ route('cliente.catalogo.index') }}"
               class="inline-flex items-center gap-2 bg-egg-400 hover:bg-egg-300 text-egg-900 font-bold px-6 py-3.5 rounded-xl shadow-lg hover:shadow-xl transition-all active:scale-[0.98]">
                <i class="fas fa-egg"></i> Ver Catálogo Completo
            </a>
        </div>
    </div>

    {{-- Cards resumen --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-5">
        <div class="bg-white rounded-2xl p-5 border border-egg-200 shadow-sm flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center text-xl flex-shrink-0">
                <i class="fas fa-clock"></i>
            </div>
            <div>
                <p class="text-2xl font-extrabold font-heading text-egg-900">
                    {{ Auth::user()->pedidos()->where('estado','pendiente')->count() }}
                </p>
                <p class="text-xs text-stone-500 font-medium">Pedidos pendientes</p>
            </div>
        </div>
        <div class="bg-white rounded-2xl p-5 border border-egg-200 shadow-sm flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-xl flex-shrink-0">
                <i class="fas fa-check-circle"></i>
            </div>
            <div>
                <p class="text-2xl font-extrabold font-heading text-egg-900">
                    {{ Auth::user()->pedidos()->where('estado','pagado')->count() }}
                </p>
                <p class="text-xs text-stone-500 font-medium">Pedidos pagados</p>
            </div>
        </div>
        <div class="bg-white rounded-2xl p-5 border border-egg-200 shadow-sm flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-egg-100 text-egg-700 flex items-center justify-center text-xl flex-shrink-0">
                <i class="fas fa-shopping-basket"></i>
            </div>
            <div>
                <p class="text-2xl font-extrabold font-heading text-egg-900">
                    {{ Auth::user()->pedidos()->count() }}
                </p>
                <p class="text-xs text-stone-500 font-medium">Total pedidos</p>
            </div>
        </div>
    </div>

    {{-- Acciones rápidas + últimos pedidos --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

        {{-- Acciones --}}
        <div class="bg-white rounded-2xl p-6 border border-egg-200 shadow-sm space-y-4">
            <h3 class="text-lg font-bold font-heading text-egg-900 flex items-center gap-2">
                <i class="fas fa-bolt text-egg-400"></i> Acciones Rápidas
            </h3>
            <div class="space-y-3">
                <a href="{{ route('cliente.catalogo.index') }}"
                   class="flex items-center justify-between p-4 rounded-xl bg-egg-50 hover:bg-egg-100 border border-egg-200 transition-all group">
                    <div class="flex items-center gap-3">
                        <span class="text-2xl">🥚</span>
                        <div>
                            <p class="font-semibold text-sm text-egg-900">Ver Catálogo</p>
                            <p class="text-xs text-egg-700">Todos los productos disponibles</p>
                        </div>
                    </div>
                    <i class="fas fa-arrow-right text-egg-500 group-hover:translate-x-1 transition-transform"></i>
                </a>
                <a href="{{ route('cliente.carrito.index') }}"
                   class="flex items-center justify-between p-4 rounded-xl bg-amber-50 hover:bg-amber-100 border border-amber-200 transition-all group">
                    <div class="flex items-center gap-3">
                        <i class="fas fa-shopping-cart text-amber-600 text-lg"></i>
                        <div>
                            <p class="font-semibold text-sm text-egg-900">Mi Carrito</p>
                            <p class="text-xs text-amber-700">Ver y gestionar mi carrito</p>
                        </div>
                    </div>
                    <i class="fas fa-arrow-right text-amber-500 group-hover:translate-x-1 transition-transform"></i>
                </a>
                <a href="{{ route('cliente.pedidos.index') }}"
                   class="flex items-center justify-between p-4 rounded-xl bg-stone-50 hover:bg-stone-100 border border-stone-200 transition-all group">
                    <div class="flex items-center gap-3">
                        <i class="fas fa-box text-stone-500 text-lg"></i>
                        <div>
                            <p class="font-semibold text-sm text-stone-800">Mis Pedidos</p>
                            <p class="text-xs text-stone-500">Historial y seguimiento</p>
                        </div>
                    </div>
                    <i class="fas fa-arrow-right text-stone-400 group-hover:translate-x-1 transition-transform"></i>
                </a>
            </div>
        </div>

        {{-- Últimos pedidos --}}
        <div class="lg:col-span-2 bg-white rounded-2xl p-6 border border-egg-200 shadow-sm">
            <div class="flex items-center justify-between mb-5">
                <h3 class="text-lg font-bold font-heading text-egg-900">Mis Últimos Pedidos</h3>
                <a href="{{ route('cliente.pedidos.index') }}"
                   class="text-xs font-semibold text-egg-700 hover:text-egg-900 bg-egg-50 hover:bg-egg-100 px-3 py-1.5 rounded-lg transition-colors">
                    Ver todos
                </a>
            </div>

            @php $pedidos = Auth::user()->pedidos()->with('pago')->latest('fecha')->take(5)->get(); @endphp

            @if($pedidos->isEmpty())
                <div class="py-10 text-center text-stone-400">
                    <i class="fas fa-box-open text-4xl mb-3 text-stone-300 block"></i>
                    <p class="text-sm">Aún no tienes pedidos.</p>
                    <a href="{{ route('cliente.catalogo.index') }}" class="mt-3 inline-block text-egg-700 font-semibold text-sm hover:underline">
                        ¡Haz tu primer pedido!
                    </a>
                </div>
            @else
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm border-collapse">
                    <thead>
                        <tr class="bg-stone-50 text-stone-500 uppercase text-[11px] font-bold tracking-wider border-y border-stone-200">
                            <th class="py-3 px-3">#</th>
                            <th class="py-3 px-3">Fecha</th>
                            <th class="py-3 px-3">Total</th>
                            <th class="py-3 px-3">Estado</th>
                            <th class="py-3 px-3 text-center">Ver</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-stone-100">
                        @foreach($pedidos as $p)
                        <tr class="hover:bg-stone-50/80 transition-colors">
                            <td class="py-3 px-3 font-mono font-semibold text-egg-700">#{{ $p->id_pedido }}</td>
                            <td class="py-3 px-3 text-stone-400 text-xs">{{ $p->fecha->format('d/m/Y') }}</td>
                            <td class="py-3 px-3 font-bold text-egg-800">${{ number_format($p->total,2) }}</td>
                            <td class="py-3 px-3">
                                @if($p->estado==='pagado')
                                    <span class="bg-emerald-100 text-emerald-800 text-xs px-2 py-0.5 rounded-full font-medium">Pagado</span>
                                @elseif($p->estado==='pendiente')
                                    <span class="bg-amber-100 text-amber-800 text-xs px-2 py-0.5 rounded-full font-medium">Pendiente</span>
                                @else
                                    <span class="bg-stone-100 text-stone-500 text-xs px-2 py-0.5 rounded-full font-medium">Cancelado</span>
                                @endif
                            </td>
                            <td class="py-3 px-3 text-center">
                                <a href="{{ route('cliente.pedidos.show', $p->id_pedido) }}"
                                   class="text-egg-700 hover:text-egg-900 bg-egg-50 hover:bg-egg-100 px-2.5 py-1 rounded-lg text-xs font-semibold transition-colors">
                                    <i class="fas fa-eye"></i>
                                </a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection

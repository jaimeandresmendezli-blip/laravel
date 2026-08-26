@extends('layouts.admin')

@section('content')
<div class="space-y-8">

    {{-- Header --}}
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-3xl font-extrabold font-heading text-egg-900 tracking-tight">Dashboard</h1>
            <p class="text-stone-500 text-sm mt-1">Resumen general de EGG EXPRESS.</p>
        </div>
        <a href="{{ route('admin.productos.create') }}"
           class="inline-flex items-center gap-2 bg-gradient-to-r from-egg-900 to-egg-800 hover:from-egg-800 hover:to-egg-700 text-egg-400 font-semibold px-5 py-2.5 rounded-xl shadow-md hover:shadow-lg transition-all active:scale-[0.98]">
            <i class="fas fa-plus"></i> Nuevo Producto
        </a>
    </div>

    {{-- KPI Cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">

        <div class="bg-white rounded-2xl p-6 border border-egg-200 shadow-sm hover:shadow-md transition-all">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold uppercase tracking-wider text-stone-400">Productos Activos</span>
                <div class="w-12 h-12 rounded-xl bg-egg-100 text-egg-700 flex items-center justify-center text-xl">🥚</div>
            </div>
            <div class="mt-4">
                <h3 class="text-3xl font-extrabold font-heading text-egg-900">
                    {{ \App\Models\Producto::where('estado','activo')->count() }}
                </h3>
                <p class="text-xs text-egg-600 font-medium mt-1">En catálogo</p>
            </div>
            <div class="mt-4 pt-3 border-t border-stone-100">
                <a href="{{ route('admin.productos.index') }}" class="text-xs text-egg-700 hover:text-egg-900 font-semibold flex items-center gap-1">
                    Ver catálogo <i class="fas fa-chevron-right text-[10px]"></i>
                </a>
            </div>
        </div>

        <div class="bg-white rounded-2xl p-6 border border-egg-200 shadow-sm hover:shadow-md transition-all">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold uppercase tracking-wider text-stone-400">Pedidos Pendientes</span>
                <div class="w-12 h-12 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center text-xl">
                    <i class="fas fa-clock"></i>
                </div>
            </div>
            <div class="mt-4">
                <h3 class="text-3xl font-extrabold font-heading text-egg-900">
                    {{ \App\Models\Pedido::where('estado','pendiente')->count() }}
                </h3>
                <p class="text-xs text-amber-600 font-medium mt-1">Por procesar</p>
            </div>
            <div class="mt-4 pt-3 border-t border-stone-100">
                <a href="{{ route('admin.pedidos.index') }}" class="text-xs text-egg-700 hover:text-egg-900 font-semibold flex items-center gap-1">
                    Ver pedidos <i class="fas fa-chevron-right text-[10px]"></i>
                </a>
            </div>
        </div>

        <div class="bg-white rounded-2xl p-6 border border-egg-200 shadow-sm hover:shadow-md transition-all">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold uppercase tracking-wider text-stone-400">Pedidos Pagados</span>
                <div class="w-12 h-12 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-xl">
                    <i class="fas fa-check-circle"></i>
                </div>
            </div>
            <div class="mt-4">
                <h3 class="text-3xl font-extrabold font-heading text-egg-900">
                    {{ \App\Models\Pedido::where('estado','pagado')->count() }}
                </h3>
                <p class="text-xs text-emerald-600 font-medium mt-1">Completados</p>
            </div>
            <div class="mt-4 pt-3 border-t border-stone-100">
                <a href="{{ route('admin.pedidos.index', ['estado'=>'pagado']) }}" class="text-xs text-egg-700 hover:text-egg-900 font-semibold flex items-center gap-1">
                    Ver pagados <i class="fas fa-chevron-right text-[10px]"></i>
                </a>
            </div>
        </div>

        <div class="bg-white rounded-2xl p-6 border border-egg-200 shadow-sm hover:shadow-md transition-all">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold uppercase tracking-wider text-stone-400">Clientes</span>
                <div class="w-12 h-12 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center text-xl">
                    <i class="fas fa-users"></i>
                </div>
            </div>
            <div class="mt-4">
                <h3 class="text-3xl font-extrabold font-heading text-egg-900">
                    {{ \App\Models\Usuario::where('id_rol',2)->count() }}
                </h3>
                <p class="text-xs text-blue-600 font-medium mt-1">Registrados</p>
            </div>
            <div class="mt-4 pt-3 border-t border-stone-100">
                <a href="{{ route('admin.usuarios.index') }}" class="text-xs text-egg-700 hover:text-egg-900 font-semibold flex items-center gap-1">
                    Ver usuarios <i class="fas fa-chevron-right text-[10px]"></i>
                </a>
            </div>
        </div>
    </div>

    {{-- Acciones rápidas + Últimos pedidos --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

        {{-- Acciones rápidas --}}
        <div class="bg-white rounded-2xl p-6 border border-egg-200 shadow-sm space-y-4">
            <h3 class="text-lg font-bold font-heading text-egg-900 flex items-center gap-2">
                <i class="fas fa-bolt text-egg-400"></i> Acciones Rápidas
            </h3>
            <div class="space-y-3">
                <a href="{{ route('admin.productos.create') }}"
                   class="flex items-center justify-between p-4 rounded-xl bg-egg-50 hover:bg-egg-100 text-egg-900 border border-egg-200 transition-all group">
                    <div class="flex items-center gap-3">
                        <i class="fas fa-plus-circle text-egg-600 text-lg"></i>
                        <div>
                            <p class="font-semibold text-sm">Nuevo Producto</p>
                            <p class="text-xs text-egg-700">Agregar al catálogo</p>
                        </div>
                    </div>
                    <i class="fas fa-arrow-right text-egg-500 group-hover:translate-x-1 transition-transform"></i>
                </a>



                <a href="{{ route('admin.usuarios.create') }}"
                   class="flex items-center justify-between p-4 rounded-xl bg-stone-50 hover:bg-stone-100 text-stone-800 border border-stone-200 transition-all group">
                    <div class="flex items-center gap-3">
                        <i class="fas fa-user-plus text-stone-600 text-lg"></i>
                        <div>
                            <p class="font-semibold text-sm">Crear Usuario</p>
                            <p class="text-xs text-stone-500">Admin o Cliente</p>
                        </div>
                    </div>
                    <i class="fas fa-arrow-right text-stone-500 group-hover:translate-x-1 transition-transform"></i>
                </a>

                <a href="{{ route('admin.reportes.index') }}"
                   class="flex items-center justify-between p-4 rounded-xl bg-stone-50 hover:bg-stone-100 text-stone-800 border border-stone-200 transition-all group">
                    <div class="flex items-center gap-3">
                        <i class="fas fa-chart-line text-stone-600 text-lg"></i>
                        <div>
                            <p class="font-semibold text-sm">Ver Reportes</p>
                            <p class="text-xs text-stone-500">Ventas e inventario</p>
                        </div>
                    </div>
                    <i class="fas fa-arrow-right text-stone-500 group-hover:translate-x-1 transition-transform"></i>
                </a>
            </div>
        </div>

        {{-- Últimos pedidos --}}
        <div class="lg:col-span-2 bg-white rounded-2xl p-6 border border-egg-200 shadow-sm">
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h3 class="text-lg font-bold font-heading text-egg-900">Últimos Pedidos</h3>
                    <p class="text-xs text-stone-500">Pedidos recientes registrados</p>
                </div>
                <a href="{{ route('admin.pedidos.index') }}"
                   class="text-xs font-semibold text-egg-700 hover:text-egg-900 bg-egg-50 hover:bg-egg-100 px-3 py-1.5 rounded-lg transition-colors">
                    Ver todos
                </a>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm border-collapse">
                    <thead>
                        <tr class="bg-stone-50 text-stone-500 uppercase text-[11px] font-bold tracking-wider border-y border-stone-200">
                            <th class="py-3 px-4">#Pedido</th>
                            <th class="py-3 px-4">Cliente</th>
                            <th class="py-3 px-4">Total</th>
                            <th class="py-3 px-4">Estado</th>
                            <th class="py-3 px-4">Fecha</th>
                            <th class="py-3 px-4 text-center">Acción</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-stone-100">
                        @forelse(\App\Models\Pedido::with('cliente')->latest('fecha')->take(6)->get() as $p)
                        <tr class="hover:bg-stone-50/80 transition-colors">
                            <td class="py-3 px-4 font-mono font-semibold text-egg-800">#{{ $p->id_pedido }}</td>
                            <td class="py-3 px-4 font-medium text-stone-900">{{ $p->cliente->nombre ?? '-' }}</td>
                            <td class="py-3 px-4 font-extrabold text-egg-800 font-heading">${{ number_format($p->total, 2) }}</td>
                            <td class="py-3 px-4">
                                @if($p->estado === 'pagado')
                                    <span class="bg-emerald-100 text-emerald-800 text-xs px-2.5 py-1 rounded-full font-medium">Pagado</span>
                                @elseif($p->estado === 'pendiente')
                                    <span class="bg-amber-100 text-amber-800 text-xs px-2.5 py-1 rounded-full font-medium">Pendiente</span>
                                @else
                                    <span class="bg-stone-100 text-stone-600 text-xs px-2.5 py-1 rounded-full font-medium">Cancelado</span>
                                @endif
                            </td>
                            <td class="py-3 px-4 text-stone-400 text-xs">{{ $p->fecha->format('d/m/Y H:i') }}</td>
                            <td class="py-3 px-4 text-center">
                                <a href="{{ route('admin.pedidos.show', $p->id_pedido) }}"
                                   class="inline-flex items-center gap-1 text-egg-700 hover:text-egg-900 bg-egg-50 hover:bg-egg-100 px-2.5 py-1 rounded-lg text-xs font-semibold transition-colors">
                                    <i class="fas fa-eye"></i> Ver
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="6" class="py-8 text-center text-stone-400">No hay pedidos aún.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>
@endsection

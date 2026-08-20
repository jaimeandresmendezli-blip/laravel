@extends('layouts.admin')
@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-3xl font-extrabold font-heading text-egg-900 tracking-tight">Historial de Inventario</h1>
            <p class="text-stone-500 text-sm mt-1">Entradas y salidas de productos.</p>
        </div>
        <a href="{{ route('admin.inventario.create') }}"
           class="inline-flex items-center gap-2 bg-gradient-to-r from-egg-900 to-egg-800 hover:from-egg-800 hover:to-egg-700 text-egg-400 font-semibold px-5 py-2.5 rounded-xl shadow-md transition-all active:scale-[0.98]">
            <i class="fas fa-plus"></i> Registrar Movimiento
        </a>
    </div>

    {{-- Filtros --}}
    <div class="bg-white rounded-2xl p-5 border border-egg-200 shadow-sm">
        <form method="GET" action="{{ route('admin.inventario.index') }}" class="grid grid-cols-1 md:grid-cols-5 gap-4">
            <div class="md:col-span-2">
                <select name="id_producto" class="w-full rounded-xl border border-stone-200 px-4 py-2.5 text-sm focus:border-egg-500 focus:ring-2 focus:ring-egg-400/20 outline-none bg-white">
                    <option value="">Todos los productos</option>
                    @foreach($productos as $p)
                        <option value="{{ $p->id_producto }}" {{ request('id_producto')==$p->id_producto?'selected':'' }}>{{ $p->nombre }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <select name="tipo_movimiento" class="w-full rounded-xl border border-stone-200 px-4 py-2.5 text-sm focus:border-egg-500 outline-none bg-white">
                    <option value="">Todos los tipos</option>
                    <option value="entrada" {{ request('tipo_movimiento')=='entrada'?'selected':'' }}>Entrada</option>
                    <option value="salida" {{ request('tipo_movimiento')=='salida'?'selected':'' }}>Salida</option>
                </select>
            </div>
            <div>
                <input type="date" name="fecha_desde" value="{{ request('fecha_desde') }}"
                       class="w-full rounded-xl border border-stone-200 px-4 py-2.5 text-sm focus:border-egg-500 outline-none">
            </div>
            <div class="flex gap-2">
                <input type="date" name="fecha_hasta" value="{{ request('fecha_hasta') }}"
                       class="flex-1 rounded-xl border border-stone-200 px-4 py-2.5 text-sm focus:border-egg-500 outline-none">
                <button type="submit" class="bg-egg-900 hover:bg-egg-800 text-egg-400 font-semibold py-2.5 px-4 rounded-xl text-sm transition-colors">
                    <i class="fas fa-filter"></i>
                </button>
                <a href="{{ route('admin.inventario.index') }}" class="bg-stone-100 hover:bg-stone-200 text-stone-600 font-semibold py-2.5 px-4 rounded-xl text-sm transition-colors">
                    <i class="fas fa-times"></i>
                </a>
            </div>
        </form>
    </div>

    <div class="bg-white rounded-2xl border border-egg-200 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm border-collapse">
                <thead>
                    <tr class="bg-stone-50 text-stone-500 uppercase text-[11px] font-bold tracking-wider border-b border-stone-200">
                        <th class="py-4 px-4">#</th>
                        <th class="py-4 px-4">Producto</th>
                        <th class="py-4 px-4">Tipo</th>
                        <th class="py-4 px-4">Cantidad</th>
                        <th class="py-4 px-4">Motivo</th>
                        <th class="py-4 px-4">Fecha</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-stone-100">
                    @forelse($movimientos as $m)
                    <tr class="hover:bg-stone-50/80 transition-colors">
                        <td class="py-3 px-4 font-mono text-stone-400 text-xs">{{ $m->id_movimiento }}</td>
                        <td class="py-3 px-4 font-bold text-egg-900">{{ $m->producto->nombre }}</td>
                        <td class="py-3 px-4">
                            @if($m->tipo_movimiento === 'entrada')
                                <span class="inline-flex items-center gap-1 bg-emerald-50 text-emerald-700 text-xs px-2.5 py-1 rounded-full font-semibold border border-emerald-200">
                                    <i class="fas fa-arrow-up text-[10px]"></i> Entrada
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1 bg-rose-50 text-rose-700 text-xs px-2.5 py-1 rounded-full font-semibold border border-rose-200">
                                    <i class="fas fa-arrow-down text-[10px]"></i> Salida
                                </span>
                            @endif
                        </td>
                        <td class="py-3 px-4 font-bold text-stone-800">{{ $m->cantidad }}</td>
                        <td class="py-3 px-4 text-stone-500">{{ $m->motivo }}</td>
                        <td class="py-3 px-4 text-stone-400 text-xs">{{ $m->fecha->format('d/m/Y H:i') }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="6" class="py-10 text-center text-stone-400">No hay movimientos registrados.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

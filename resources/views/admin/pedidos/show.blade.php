@extends('layouts.admin')
@section('content')
<div class="space-y-6">
    <div class="flex items-center gap-2 text-sm text-stone-500">
        <a href="{{ route('admin.pedidos.index') }}" class="hover:text-egg-700">Pedidos</a>
        <i class="fas fa-chevron-right text-[10px]"></i>
        <span class="text-stone-700 font-medium">Pedido #{{ $pedido->id_pedido }}</span>
    </div>
    <h1 class="text-3xl font-extrabold font-heading text-egg-900 tracking-tight">Pedido #{{ $pedido->id_pedido }}</h1>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- Detalle principal --}}
        <div class="lg:col-span-2 space-y-6">

            {{-- Datos cliente y entrega --}}
            <div class="bg-white rounded-2xl border border-egg-200 shadow-sm p-6 space-y-4">
                <h3 class="font-heading font-bold text-egg-900 text-lg flex items-center gap-2">
                    <i class="fas fa-user text-egg-500"></i> Datos del Cliente
                </h3>
                <div class="grid grid-cols-2 gap-4 text-sm">
                    <div class="bg-stone-50 rounded-xl p-3">
                        <span class="text-xs text-stone-400 block font-semibold">Cliente</span>
                        <strong class="text-stone-800">{{ $pedido->cliente->nombre ?? '-' }}</strong>
                    </div>
                    <div class="bg-stone-50 rounded-xl p-3">
                        <span class="text-xs text-stone-400 block font-semibold">Fecha</span>
                        <strong class="text-stone-800">{{ $pedido->fecha->format('d/m/Y H:i') }}</strong>
                    </div>
                    <div class="bg-stone-50 rounded-xl p-3">
                        <span class="text-xs text-stone-400 block font-semibold">Dirección</span>
                        <strong class="text-stone-800">{{ $pedido->direccion_entrega }}</strong>
                    </div>
                    <div class="bg-stone-50 rounded-xl p-3">
                        <span class="text-xs text-stone-400 block font-semibold">Barrio</span>
                        <strong class="text-stone-800">{{ $pedido->barrio }}</strong>
                    </div>
                    <div class="bg-stone-50 rounded-xl p-3">
                        <span class="text-xs text-stone-400 block font-semibold">Teléfono</span>
                        <strong class="text-stone-800">{{ $pedido->telefono_entrega }}</strong>
                    </div>
                    <div class="bg-stone-50 rounded-xl p-3">
                        <span class="text-xs text-stone-400 block font-semibold">Referencia</span>
                        <strong class="text-stone-800">{{ $pedido->referencia ?? '-' }}</strong>
                    </div>
                    @if($pedido->observaciones)
                    <div class="bg-stone-50 rounded-xl p-3 col-span-2">
                        <span class="text-xs text-stone-400 block font-semibold">Observaciones</span>
                        <strong class="text-stone-800">{{ $pedido->observaciones }}</strong>
                    </div>
                    @endif
                </div>
            </div>

            {{-- Productos --}}
            <div class="bg-white rounded-2xl border border-egg-200 shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-stone-200 bg-stone-50/60">
                    <h3 class="font-heading font-bold text-egg-900">Productos del Pedido</h3>
                </div>
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-stone-50 text-stone-500 uppercase text-[11px] font-bold tracking-wider border-b border-stone-200">
                            <th class="py-3 px-4">Producto</th>
                            <th class="py-3 px-4">Cant.</th>
                            <th class="py-3 px-4">Precio</th>
                            <th class="py-3 px-4">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-stone-100">
                        @foreach($pedido->detalles as $d)
                        <tr class="hover:bg-stone-50/80">
                            <td class="py-3 px-4 font-medium text-egg-900">{{ $d->producto->nombre }}</td>
                            <td class="py-3 px-4 text-stone-600">{{ $d->cantidad }}</td>
                            <td class="py-3 px-4 text-stone-600">${{ number_format($d->precio,2) }}</td>
                            <td class="py-3 px-4 font-bold text-egg-800">${{ number_format($d->subtotal,2) }}</td>
                        </tr>
                        @endforeach
                        <tr class="bg-egg-50">
                            <td colspan="3" class="py-3 px-4 text-right font-bold text-egg-900">TOTAL</td>
                            <td class="py-3 px-4 font-extrabold text-egg-800 text-lg font-heading">${{ number_format($pedido->total,2) }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Panel lateral --}}
        <div class="space-y-6">

            {{-- Estado y actualizar --}}
            <div class="bg-white rounded-2xl border border-egg-200 shadow-sm p-6 space-y-4">
                <h3 class="font-heading font-bold text-egg-900">Estado del Pedido</h3>

                <div class="space-y-3 text-sm">
                    <div class="flex justify-between items-center">
                        <span class="text-stone-500">Pago:</span>
                        @if($pedido->estado==='pagado')
                            <span class="bg-emerald-100 text-emerald-800 text-xs px-3 py-1 rounded-full font-medium">Pagado</span>
                        @elseif($pedido->estado==='pendiente')
                            <span class="bg-amber-100 text-amber-800 text-xs px-3 py-1 rounded-full font-medium">Pendiente</span>
                        @else
                            <span class="bg-stone-100 text-stone-600 text-xs px-3 py-1 rounded-full font-medium">Cancelado</span>
                        @endif
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-stone-500">Entrega:</span>
                        <span class="bg-blue-50 text-blue-700 text-xs px-3 py-1 rounded-full font-medium capitalize">{{ str_replace('_',' ', $pedido->estado_entrega) }}</span>
                    </div>
                </div>

                <form method="POST" action="{{ route('admin.pedidos.estado-entrega', $pedido->id_pedido) }}" class="space-y-3 border-t border-stone-200 pt-4">
                    @csrf @method('PATCH')
                    <label class="block text-xs font-bold uppercase tracking-wider text-stone-500">Actualizar entrega</label>
                    <select name="estado_entrega" class="w-full rounded-xl border border-stone-200 px-4 py-2.5 text-sm focus:border-egg-500 outline-none bg-white">
                        <option value="pendiente" {{ $pedido->estado_entrega=='pendiente'?'selected':'' }}>Pendiente</option>
                        <option value="en_camino" {{ $pedido->estado_entrega=='en_camino'?'selected':'' }}>En camino 🚚</option>
                        <option value="entregado" {{ $pedido->estado_entrega=='entregado'?'selected':'' }}>Entregado ✅</option>
                        <option value="cancelado" {{ $pedido->estado_entrega=='cancelado'?'selected':'' }}>Cancelado</option>
                    </select>
                    <button type="submit" class="w-full bg-gradient-to-r from-egg-900 to-egg-800 hover:from-egg-800 hover:to-egg-700 text-egg-400 font-semibold py-2.5 rounded-xl shadow-sm text-sm transition-all">
                        Actualizar Estado
                    </button>
                </form>
            </div>

            {{-- Pago --}}
            <div class="bg-white rounded-2xl border border-egg-200 shadow-sm p-6 space-y-3">
                <h3 class="font-heading font-bold text-egg-900">Información de Pago</h3>
                @if($pedido->pago)
                <div class="space-y-2 text-sm">
                    <div class="flex justify-between">
                        <span class="text-stone-500">Método:</span>
                        <span class="font-semibold text-stone-800 capitalize">{{ $pedido->pago->metodo_pago }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-stone-500">Monto:</span>
                        <span class="font-bold text-egg-800">${{ number_format($pedido->pago->monto,2) }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-stone-500">Estado:</span>
                        <span class="font-semibold text-stone-800 capitalize">{{ $pedido->pago->estado_pago }}</span>
                    </div>
                    @if($pedido->pago->referencia_pago)
                    <div class="flex justify-between">
                        <span class="text-stone-500">Referencia:</span>
                        <span class="font-mono text-xs text-stone-700">{{ $pedido->pago->referencia_pago }}</span>
                    </div>
                    @endif
                </div>
                @else
                <p class="text-stone-400 text-sm">Sin registro de pago.</p>
                @endif
            </div>

        </div>
    </div>
</div>
@endsection

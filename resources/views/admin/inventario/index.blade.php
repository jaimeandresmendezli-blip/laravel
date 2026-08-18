@extends('layouts.admin')
@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-3xl font-extrabold font-heading text-egg-900 tracking-tight">Historial de Inventario</h1>
            <p class="text-stone-500 text-sm mt-1">Entradas y salidas de productos.</p>
        </div>
        <button type="button" onclick="abrirMovimientoModal()"
                class="inline-flex items-center gap-2 bg-gradient-to-r from-egg-900 to-egg-800 hover:from-egg-800 hover:to-egg-700 text-egg-400 font-semibold px-5 py-2.5 rounded-xl shadow-md transition-all active:scale-[0.98]">
            <i class="fas fa-exchange-alt"></i> Registrar Movimiento
        </button>
    </div>

    <div id="movimiento-modal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/40 backdrop-blur-sm p-4">
        <div class="bg-white rounded-2xl shadow-xl w-full max-w-2xl max-h-[90vh] overflow-y-auto">
            <div class="px-6 py-4 border-b border-stone-200 bg-stone-50/60 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-xl bg-amber-100 text-amber-700 flex items-center justify-center">
                        <i class="fas fa-exchange-alt"></i>
                    </div>
                    <div>
                        <h3 class="font-heading font-bold text-stone-800">Entrada o Salida Manual</h3>
                        <p class="text-xs text-stone-500">Se actualizará el stock automáticamente</p>
                    </div>
                </div>
                <button type="button" onclick="cerrarMovimientoModal()" class="text-stone-400 hover:text-stone-700 text-xl" aria-label="Cerrar">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            @if($errors->any())
                <div class="mx-6 mt-4 bg-rose-50 border border-rose-200 text-rose-800 p-4 rounded-xl">
                    <ul class="text-xs space-y-1 list-disc list-inside">
                        @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('admin.inventario.store') }}" class="p-6 space-y-5">
                @csrf
                <div>
                    <label class="block text-sm font-semibold text-stone-700 mb-2">Producto *</label>
                    <select name="id_producto" required class="w-full rounded-xl border border-stone-200 px-4 py-2.5 text-sm focus:border-egg-500 focus:ring-2 focus:ring-egg-400/20 outline-none bg-white">
                        <option value="">-- Seleccionar producto --</option>
                        @foreach($productos->where('estado', 'activo') as $p)
                            <option value="{{ $p->id_producto }}" {{ old('id_producto') == $p->id_producto ? 'selected' : '' }}>
                                {{ $p->nombre }} (Stock actual: {{ $p->cantidad }})
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-stone-700 mb-2">Tipo de movimiento *</label>
                    <div class="grid grid-cols-2 gap-3">
                        <label class="flex items-center gap-3 p-4 rounded-xl border-2 cursor-pointer border-stone-200 hover:border-emerald-300">
                            <input type="radio" name="tipo_movimiento" value="entrada" {{ old('tipo_movimiento', 'entrada') == 'entrada' ? 'checked' : '' }} class="text-emerald-600">
                            <div><p class="font-semibold text-sm text-emerald-700">Entrada</p><p class="text-xs text-stone-500">Aumenta el stock</p></div>
                        </label>
                        <label class="flex items-center gap-3 p-4 rounded-xl border-2 cursor-pointer border-stone-200 hover:border-rose-300">
                            <input type="radio" name="tipo_movimiento" value="salida" {{ old('tipo_movimiento') == 'salida' ? 'checked' : '' }} class="text-rose-600">
                            <div><p class="font-semibold text-sm text-rose-700">Salida</p><p class="text-xs text-stone-500">Reduce el stock</p></div>
                        </label>
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-stone-700 mb-2">Cantidad *</label>
                    <input type="number" name="cantidad" value="{{ old('cantidad') }}" min="1" required class="w-full rounded-xl border border-stone-200 px-4 py-2.5 text-sm font-semibold focus:border-egg-500 focus:ring-2 focus:ring-egg-400/20 outline-none">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-stone-700 mb-2">Motivo *</label>
                    <input type="text" name="motivo" value="{{ old('motivo') }}" maxlength="255" required placeholder="Ej: Compra a proveedor, Merma, Ajuste..." class="w-full rounded-xl border border-stone-200 px-4 py-2.5 text-sm focus:border-egg-500 focus:ring-2 focus:ring-egg-400/20 outline-none">
                </div>
                <div class="border-t border-stone-200 pt-4 flex items-center justify-end gap-3">
                    <button type="button" onclick="cerrarMovimientoModal()" class="px-5 py-2.5 text-sm font-semibold text-stone-700 bg-white border border-stone-200 hover:bg-stone-100 rounded-xl transition-all">Cancelar</button>
                    <button type="submit" class="px-6 py-2.5 text-sm font-semibold text-egg-400 bg-gradient-to-r from-egg-900 to-egg-800 hover:from-egg-800 hover:to-egg-700 rounded-xl shadow-md flex items-center gap-2"><i class="fas fa-save"></i> Registrar</button>
                </div>
            </form>
        </div>
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
@push('scripts')
<script>
function abrirMovimientoModal() {
    const modal = document.getElementById('movimiento-modal');
    modal.classList.remove('hidden');
    modal.classList.add('flex');
}
function cerrarMovimientoModal() {
    const modal = document.getElementById('movimiento-modal');
    modal.classList.add('hidden');
    modal.classList.remove('flex');
}
document.getElementById('movimiento-modal').addEventListener('click', function (event) {
    if (event.target === this) cerrarMovimientoModal();
});
@if($errors->any())
abrirMovimientoModal();
@endif
</script>
@endpush
@endsection

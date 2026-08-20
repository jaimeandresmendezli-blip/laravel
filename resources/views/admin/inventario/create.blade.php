@extends('layouts.admin')
@section('content')
<div class="space-y-6">
    <div class="flex items-center gap-2 text-sm text-stone-500">
        <a href="{{ route('admin.inventario.index') }}" class="hover:text-egg-700">Inventario</a>
        <i class="fas fa-chevron-right text-[10px]"></i>
        <span class="text-stone-700 font-medium">Registrar Movimiento</span>
    </div>
    <h1 class="text-3xl font-extrabold font-heading text-egg-900 tracking-tight">Registrar Movimiento de Inventario</h1>

    <div class="bg-white rounded-2xl shadow-sm border border-egg-200 overflow-hidden max-w-2xl">
        <div class="px-6 py-4 border-b border-stone-200 bg-stone-50/60 flex items-center gap-3">
            <div class="w-9 h-9 rounded-xl bg-amber-100 text-amber-700 flex items-center justify-center">
                <i class="fas fa-exchange-alt"></i>
            </div>
            <div>
                <h3 class="font-heading font-bold text-stone-800">Entrada o Salida Manual</h3>
                <p class="text-xs text-stone-500">Se actualizará el stock automáticamente</p>
            </div>
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
                    @foreach($productos as $p)
                        <option value="{{ $p->id_producto }}" {{ old('id_producto')==$p->id_producto?'selected':'' }}>
                            {{ $p->nombre }} (Stock actual: {{ $p->cantidad }})
                        </option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-semibold text-stone-700 mb-2">Tipo de movimiento *</label>
                <div class="grid grid-cols-2 gap-3">
                    <label class="flex items-center gap-3 p-4 rounded-xl border-2 cursor-pointer transition-all {{ old('tipo_movimiento')=='entrada' ? 'border-emerald-500 bg-emerald-50' : 'border-stone-200 hover:border-emerald-300' }}">
                        <input type="radio" name="tipo_movimiento" value="entrada" {{ old('tipo_movimiento','entrada')=='entrada'?'checked':'' }} class="text-emerald-600">
                        <div>
                            <p class="font-semibold text-sm text-emerald-700">📦 Entrada</p>
                            <p class="text-xs text-stone-500">Aumenta el stock</p>
                        </div>
                    </label>
                    <label class="flex items-center gap-3 p-4 rounded-xl border-2 cursor-pointer transition-all {{ old('tipo_movimiento')=='salida' ? 'border-rose-500 bg-rose-50' : 'border-stone-200 hover:border-rose-300' }}">
                        <input type="radio" name="tipo_movimiento" value="salida" {{ old('tipo_movimiento')=='salida'?'checked':'' }} class="text-rose-600">
                        <div>
                            <p class="font-semibold text-sm text-rose-700">📤 Salida</p>
                            <p class="text-xs text-stone-500">Reduce el stock</p>
                        </div>
                    </label>
                </div>
            </div>
            <div>
                <label class="block text-sm font-semibold text-stone-700 mb-2">Cantidad *</label>
                <input type="number" name="cantidad" value="{{ old('cantidad') }}" min="1" required
                       class="w-full rounded-xl border border-stone-200 px-4 py-2.5 text-sm font-semibold focus:border-egg-500 focus:ring-2 focus:ring-egg-400/20 outline-none transition-all">
            </div>
            <div>
                <label class="block text-sm font-semibold text-stone-700 mb-2">Motivo *</label>
                <input type="text" name="motivo" value="{{ old('motivo') }}" maxlength="255" required
                       class="w-full rounded-xl border border-stone-200 px-4 py-2.5 text-sm focus:border-egg-500 focus:ring-2 focus:ring-egg-400/20 outline-none transition-all"
                       placeholder="Ej: Compra a proveedor, Merma, Ajuste...">
            </div>

            <div class="border-t border-stone-200 pt-4 flex items-center justify-end gap-3">
                <a href="{{ route('admin.inventario.index') }}"
                   class="px-5 py-2.5 text-sm font-semibold text-stone-700 bg-white border border-stone-200 hover:bg-stone-100 rounded-xl transition-all">
                    Cancelar
                </a>
                <button type="submit"
                        class="px-6 py-2.5 text-sm font-semibold text-egg-400 bg-gradient-to-r from-egg-900 to-egg-800 hover:from-egg-800 hover:to-egg-700 rounded-xl shadow-md flex items-center gap-2">
                    <i class="fas fa-save"></i> Registrar
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

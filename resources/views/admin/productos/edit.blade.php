@extends('layouts.admin')
@section('content')
<div class="space-y-6">
    <div class="flex items-center gap-2 text-sm text-stone-500">
        <a href="{{ route('admin.productos.index') }}" class="hover:text-egg-700">Productos</a>
        <i class="fas fa-chevron-right text-[10px]"></i>
        <span class="text-stone-700 font-medium">Editar</span>
    </div>
    <h1 class="text-3xl font-extrabold font-heading text-egg-900 tracking-tight">Editar Producto</h1>

    <div class="bg-white rounded-2xl shadow-sm border border-egg-200 overflow-hidden">
        <div class="px-6 py-4 border-b border-stone-200 bg-stone-50/60 flex items-center gap-3">
            <div class="w-9 h-9 rounded-xl bg-egg-100 text-egg-700 flex items-center justify-center text-xl">🥚</div>
            <h3 class="font-heading font-bold text-stone-800">{{ $producto->nombre }}</h3>
        </div>

        @if($errors->any())
        <div class="mx-6 mt-4 bg-rose-50 border border-rose-200 text-rose-800 p-4 rounded-xl">
            <ul class="text-xs space-y-1 list-disc list-inside">
                @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
            </ul>
        </div>
        @endif

        <form action="{{ route('admin.productos.update', $producto->id_producto) }}" method="POST" enctype="multipart/form-data" class="p-6 lg:p-8 space-y-6">
            @csrf @method('PUT')
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="md:col-span-2">
                    <label class="block text-sm font-semibold text-stone-700 mb-2">Nombre *</label>
                    <input type="text" name="nombre" value="{{ old('nombre', $producto->nombre) }}" required
                           class="w-full rounded-xl border border-stone-200 px-4 py-2.5 text-sm focus:border-egg-500 focus:ring-2 focus:ring-egg-400/20 outline-none transition-all">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-stone-700 mb-2">Tipo de huevo</label>
                    <input type="text" name="tipo_huevo" value="{{ old('tipo_huevo', $producto->tipo_huevo) }}"
                           class="w-full rounded-xl border border-stone-200 px-4 py-2.5 text-sm focus:border-egg-500 focus:ring-2 focus:ring-egg-400/20 outline-none transition-all">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-stone-700 mb-2">Presentación</label>
                    <input type="text" name="presentacion" value="{{ old('presentacion', $producto->presentacion) }}"
                           class="w-full rounded-xl border border-stone-200 px-4 py-2.5 text-sm focus:border-egg-500 focus:ring-2 focus:ring-egg-400/20 outline-none transition-all">
                </div>
                <div class="md:col-span-2">
                    <label class="block text-sm font-semibold text-stone-700 mb-2">Descripción</label>
                    <textarea name="descripcion" rows="2" class="w-full rounded-xl border border-stone-200 px-4 py-2.5 text-sm focus:border-egg-500 focus:ring-2 focus:ring-egg-400/20 outline-none transition-all">{{ old('descripcion', $producto->descripcion) }}</textarea>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-stone-700 mb-2">Precio ($) *</label>
                    <div class="relative">
                        <span class="absolute left-4 top-2.5 text-stone-400 font-bold text-sm">$</span>
                        <input type="number" name="precio" step="0.01" value="{{ old('precio', $producto->precio) }}" required
                               class="w-full rounded-xl border border-stone-200 pl-8 pr-4 py-2.5 text-sm font-bold text-egg-800 focus:border-egg-500 focus:ring-2 focus:ring-egg-400/20 outline-none transition-all">
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-stone-700 mb-2">Estado</label>
                    <select name="estado" class="w-full rounded-xl border border-stone-200 px-4 py-2.5 text-sm focus:border-egg-500 focus:ring-2 focus:ring-egg-400/20 outline-none bg-white">
                        <option value="activo" {{ old('estado', $producto->estado)=='activo'?'selected':'' }}>Activo</option>
                        <option value="inactivo" {{ old('estado', $producto->estado)=='inactivo'?'selected':'' }}>Inactivo</option>
                    </select>
                </div>
                <div class="md:col-span-2">
                    @if($producto->imagen)
                        <p class="text-xs text-stone-500 mb-2">Imagen actual:</p>
                        <img src="{{ asset('storage/'.$producto->imagen) }}" class="w-20 h-20 object-cover rounded-xl border border-stone-200 mb-3">
                    @endif
                    <label class="block text-sm font-semibold text-stone-700 mb-2">Nueva imagen (opcional)</label>
                    <input type="file" name="imagen" accept="image/*"
                           class="w-full rounded-xl border border-stone-200 px-3 py-2 text-sm text-stone-600 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-egg-50 file:text-egg-700 hover:file:bg-egg-100 cursor-pointer">
                </div>
            </div>

            <div class="border-t border-stone-200 pt-4 flex items-center justify-end gap-3">
                <a href="{{ route('admin.productos.index') }}"
                   class="px-5 py-2.5 text-sm font-semibold text-stone-700 bg-white border border-stone-200 hover:bg-stone-100 rounded-xl transition-all">
                    Cancelar
                </a>
                <button type="submit"
                        class="px-6 py-2.5 text-sm font-semibold text-egg-400 bg-gradient-to-r from-egg-900 to-egg-800 hover:from-egg-800 hover:to-egg-700 rounded-xl shadow-md flex items-center gap-2">
                    <i class="fas fa-save"></i> Actualizar Producto
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

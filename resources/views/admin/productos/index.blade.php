@extends('layouts.admin')
@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-3xl font-extrabold font-heading text-egg-900 tracking-tight">Catálogo de Productos</h1>
            <p class="text-stone-500 text-sm mt-1">Gestión de huevos y presentaciones disponibles.</p>
        </div>
        <a href="{{ route('admin.productos.create') }}"
           class="inline-flex items-center gap-2 bg-gradient-to-r from-egg-900 to-egg-800 hover:from-egg-800 hover:to-egg-700 text-egg-400 font-semibold px-5 py-2.5 rounded-xl shadow-md transition-all active:scale-[0.98]">
            <i class="fas fa-plus"></i> Agregar Producto
        </a>
    </div>

    <div class="bg-white rounded-2xl border border-egg-200 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm border-collapse">
                <thead>
                    <tr class="bg-stone-50 text-stone-500 uppercase text-[11px] font-bold tracking-wider border-b border-stone-200">
                        <th class="py-4 px-4 text-center">Imagen</th>
                        <th class="py-4 px-4">Nombre</th>
                        <th class="py-4 px-4">Tipo</th>
                        <th class="py-4 px-4">Presentación</th>
                        <th class="py-4 px-4">Precio</th>
                        <th class="py-4 px-4">Stock</th>
                        <th class="py-4 px-4">Estado</th>
                        <th class="py-4 px-4 text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-stone-100">
                    @forelse($productos as $p)
                    <tr class="hover:bg-stone-50/80 transition-colors">
                        <td class="py-3 px-4 text-center">
                            @if($p->imagen)
                                <img src="{{ asset('storage/'.$p->imagen) }}" class="w-12 h-12 object-cover rounded-xl border border-stone-200 inline-block">
                            @else
                                <div class="w-12 h-12 rounded-xl bg-egg-50 text-egg-400 flex items-center justify-center inline-flex text-2xl">🥚</div>
                            @endif
                        </td>
                        <td class="py-3 px-4 font-bold text-egg-900 font-heading">{{ $p->nombre }}</td>
                        <td class="py-3 px-4 text-stone-600">{{ $p->tipo_huevo ?? '-' }}</td>
                        <td class="py-3 px-4 text-stone-600">{{ $p->presentacion ?? '-' }}</td>
                        <td class="py-3 px-4 font-extrabold text-egg-800 font-heading">${{ number_format($p->precio, 2) }}</td>
                        <td class="py-3 px-4">
                            @if($p->cantidad <= 0)
                                <span class="bg-rose-50 text-rose-700 text-xs px-2.5 py-1 rounded-full font-semibold border border-rose-200">Agotado (0)</span>
                            @elseif($p->cantidad <= 5)
                                <span class="bg-amber-50 text-amber-700 text-xs px-2.5 py-1 rounded-full font-semibold border border-amber-200">{{ $p->cantidad }} 🔔</span>
                            @else
                                <span class="bg-emerald-50 text-emerald-700 text-xs px-2.5 py-1 rounded-full font-semibold border border-emerald-200">{{ $p->cantidad }}</span>
                            @endif
                        </td>
                        <td class="py-3 px-4">
                            @if($p->estado === 'activo')
                                <span class="bg-emerald-100 text-emerald-800 text-xs px-2.5 py-1 rounded-full font-medium">Activo</span>
                            @else
                                <span class="bg-stone-100 text-stone-600 text-xs px-2.5 py-1 rounded-full font-medium">Inactivo</span>
                            @endif
                        </td>
                        <td class="py-3 px-4 text-center">
                            <div class="flex items-center justify-center gap-1">
                                <a href="{{ route('admin.productos.edit', $p->id_producto) }}"
                                   class="p-2 text-amber-600 hover:bg-amber-50 rounded-lg transition-colors" title="Editar">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <form method="POST" action="{{ route('admin.productos.toggle', $p->id_producto) }}" class="inline">
                                    @csrf @method('PATCH')
                                    <button type="submit"
                                            class="p-2 {{ $p->estado==='activo' ? 'text-rose-500 hover:bg-rose-50' : 'text-emerald-600 hover:bg-emerald-50' }} rounded-lg transition-colors"
                                            title="{{ $p->estado==='activo' ? 'Desactivar' : 'Activar' }}">
                                        <i class="fas {{ $p->estado==='activo' ? 'fa-eye-slash' : 'fa-eye' }}"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="8" class="py-10 text-center text-stone-400">No hay productos registrados.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

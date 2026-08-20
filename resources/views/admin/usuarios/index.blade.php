@extends('layouts.admin')
@section('content')
<div class="space-y-6">

    {{-- Modal de confirmación de eliminación --}}
    <div id="modal-eliminar" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/40 backdrop-blur-sm">
        <div class="bg-white rounded-2xl shadow-xl p-6 w-full max-w-sm mx-4 text-center">
            <div class="w-14 h-14 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-4">
                <i class="fas fa-trash-alt text-red-600 text-xl"></i>
            </div>
            <h3 class="text-lg font-bold text-egg-900 mb-1">¿Eliminar usuario?</h3>
            <p class="text-stone-500 text-sm mb-6">Esta acción es permanente y no se puede deshacer.</p>
            <div class="flex gap-3 justify-center">
                <button onclick="cerrarModal()" class="px-5 py-2 rounded-xl border border-stone-200 text-stone-600 hover:bg-stone-50 transition-colors font-medium">
                    Cancelar
                </button>
                <form id="form-eliminar" method="POST">
                    @csrf @method('DELETE')
                    <button type="submit" class="px-5 py-2 rounded-xl bg-red-600 hover:bg-red-700 text-white font-semibold transition-colors">
                        Sí, eliminar
                    </button>
                </form>
            </div>
        </div>
    </div>
    @if(session('error'))
    <div class="flex items-center gap-3 bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-xl text-sm">
        <i class="fas fa-exclamation-circle text-red-500"></i>
        {{ session('error') }}
    </div>
    @endif

    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-3xl font-extrabold font-heading text-egg-900 tracking-tight">Usuarios del Sistema</h1>
            <p class="text-stone-500 text-sm mt-1">Gestión de administradores y clientes.</p>
        </div>
        <a href="{{ route('admin.usuarios.create') }}"
           class="inline-flex items-center gap-2 bg-gradient-to-r from-egg-900 to-egg-800 hover:from-egg-800 hover:to-egg-700 text-egg-400 font-semibold px-5 py-2.5 rounded-xl shadow-md transition-all active:scale-[0.98]">
            <i class="fas fa-plus"></i> Crear Usuario
        </a>
    </div>

    <div class="bg-white rounded-2xl border border-egg-200 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm border-collapse">
                <thead>
                    <tr class="bg-stone-50 text-stone-500 uppercase text-[11px] font-bold tracking-wider border-b border-stone-200">
                        <th class="py-4 px-4">Nombre</th>
                        <th class="py-4 px-4">Correo</th>
                        <th class="py-4 px-4">Rol</th>
                        <th class="py-4 px-4">Teléfono</th>
                        <th class="py-4 px-4">Estado</th>
                        <th class="py-4 px-4">Registro</th>
                        <th class="py-4 px-4 text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-stone-100">
                    @forelse($usuarios as $u)
                    <tr class="hover:bg-stone-50/80 transition-colors">
                        <td class="py-3 px-4 font-bold text-egg-900 font-heading">
                            <div class="flex items-center gap-3">
                                <div class="w-9 h-9 rounded-xl bg-egg-100 text-egg-700 flex items-center justify-center font-bold text-sm flex-shrink-0">
                                    {{ strtoupper(substr($u->nombre ?? 'U', 0, 1)) }}
                                </div>
                                {{ $u->nombre }}
                            </div>
                        </td>
                        <td class="py-3 px-4 text-stone-600">{{ $u->correo }}</td>
                        <td class="py-3 px-4">
                            @if($u->id_rol == 1)
                                <span class="bg-egg-100 text-egg-800 text-xs px-2.5 py-1 rounded-full font-semibold">Admin</span>
                            @else
                                <span class="bg-blue-50 text-blue-700 text-xs px-2.5 py-1 rounded-full font-semibold">Cliente</span>
                            @endif
                        </td>
                        <td class="py-3 px-4 text-stone-500">{{ $u->telefono ?? '-' }}</td>
                        <td class="py-3 px-4">
                            @if($u->estado === 'activo')
                                <span class="bg-emerald-100 text-emerald-800 text-xs px-2.5 py-1 rounded-full font-medium">Activo</span>
                            @else
                                <span class="bg-stone-100 text-stone-600 text-xs px-2.5 py-1 rounded-full font-medium">Inactivo</span>
                            @endif
                        </td>
                        <td class="py-3 px-4 text-stone-400 text-xs">{{ $u->fecha_registro }}</td>
                        <td class="py-3 px-4 text-center">
                            <div class="flex items-center justify-center gap-1">
                                {{-- Editar: amarillo --}}
                                <a href="{{ route('admin.usuarios.edit', $u->id_usuario) }}"
                                   class="p-2 rounded-lg transition-all text-amber-500 bg-amber-50 hover:bg-amber-100 hover:text-amber-700 border border-amber-100"
                                   title="Editar">
                                    <i class="fas fa-edit text-xs"></i>
                                </a>
                                {{-- Activar / Desactivar: verde o naranja --}}
                                <form method="POST" action="{{ route('admin.usuarios.toggle', $u->id_usuario) }}" class="inline">
                                    @csrf @method('PATCH')
                                    <button type="submit"
                                            class="p-2 rounded-lg transition-all border
                                            {{ $u->estado === 'activo'
                                                ? 'text-orange-500 bg-orange-50 hover:bg-orange-100 hover:text-orange-700 border-orange-100'
                                                : 'text-emerald-600 bg-emerald-50 hover:bg-emerald-100 hover:text-emerald-700 border-emerald-100' }}"
                                            title="{{ $u->estado === 'activo' ? 'Desactivar' : 'Activar' }}">
                                        <i class="fas {{ $u->estado === 'activo' ? 'fa-ban' : 'fa-check-circle' }} text-xs"></i>
                                    </button>
                                </form>
                                {{-- Eliminar: rojo (para todos) --}}
                                <button onclick="abrirModal('{{ route('admin.usuarios.destroy', $u->id_usuario) }}')"
                                        class="p-2 rounded-lg transition-all text-red-500 bg-red-50 hover:bg-red-100 hover:text-red-700 border border-red-100"
                                        title="Eliminar">
                                    <i class="fas fa-trash-alt text-xs"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="7" class="py-10 text-center text-stone-400">No hay usuarios registrados.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

@push('scripts')
<script>
function abrirModal(url) {
    document.getElementById('form-eliminar').action = url;
    const modal = document.getElementById('modal-eliminar');
    modal.classList.remove('hidden');
    modal.classList.add('flex');
}
function cerrarModal() {
    const modal = document.getElementById('modal-eliminar');
    modal.classList.add('hidden');
    modal.classList.remove('flex');
}
document.getElementById('modal-eliminar').addEventListener('click', function(e) {
    if (e.target === this) cerrarModal();
});
</script>
@endpush
@endsection

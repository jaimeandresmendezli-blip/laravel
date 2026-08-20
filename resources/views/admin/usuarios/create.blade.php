@extends('layouts.admin')
@section('content')
<div class="space-y-6">
    <div class="flex items-center gap-2 text-sm text-stone-500 mb-1">
        <a href="{{ route('admin.usuarios.index') }}" class="hover:text-egg-700 transition-colors">Usuarios</a>
        <i class="fas fa-chevron-right text-[10px] text-stone-400"></i>
        <span class="text-stone-700 font-medium">Crear Usuario</span>
    </div>
    <h1 class="text-3xl font-extrabold font-heading text-egg-900 tracking-tight">Crear Nuevo Usuario</h1>

    <div class="bg-white rounded-2xl shadow-sm border border-egg-200 overflow-hidden">
        <div class="px-6 py-4 border-b border-stone-200 bg-stone-50/60 flex items-center gap-3">
            <div class="w-9 h-9 rounded-xl bg-egg-100 text-egg-700 flex items-center justify-center">
                <i class="fas fa-user-plus"></i>
            </div>
            <div>
                <h3 class="font-heading font-bold text-stone-800 text-base">Datos del Usuario</h3>
                <p class="text-xs text-stone-500">Complete los campos obligatorios (*)</p>
            </div>
        </div>

        @if($errors->any())
        <div class="mx-6 mt-4 bg-rose-50 border border-rose-200 text-rose-800 p-4 rounded-xl">
            <ul class="text-xs space-y-1 list-disc list-inside">
                @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
            </ul>
        </div>
        @endif

        <form method="POST" action="{{ route('admin.usuarios.store') }}" class="p-6 lg:p-8 space-y-6">
            @csrf
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-semibold text-stone-700 mb-2">Nombre completo *</label>
                    <input type="text" name="nombre" value="{{ old('nombre') }}" required
                           class="w-full rounded-xl border border-stone-200 px-4 py-2.5 text-sm focus:border-egg-500 focus:ring-2 focus:ring-egg-400/20 outline-none transition-all">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-stone-700 mb-2">Correo electrónico *</label>
                    <input type="email" name="correo" value="{{ old('correo') }}" required
                           class="w-full rounded-xl border border-stone-200 px-4 py-2.5 text-sm focus:border-egg-500 focus:ring-2 focus:ring-egg-400/20 outline-none transition-all">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-stone-700 mb-2">Teléfono</label>
                    <input type="text" name="telefono" value="{{ old('telefono') }}"
                           class="w-full rounded-xl border border-stone-200 px-4 py-2.5 text-sm focus:border-egg-500 focus:ring-2 focus:ring-egg-400/20 outline-none transition-all">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-stone-700 mb-2">Rol *</label>
                    <select name="id_rol" required class="w-full rounded-xl border border-stone-200 px-4 py-2.5 text-sm focus:border-egg-500 focus:ring-2 focus:ring-egg-400/20 outline-none transition-all bg-white">
                        @foreach($roles as $rol)
                            <option value="{{ $rol->id_rol }}" {{ old('id_rol') == $rol->id_rol ? 'selected' : '' }}>{{ $rol->nombre }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-stone-700 mb-2">Contraseña *</label>
                    <input type="password" name="password" required
                           class="w-full rounded-xl border border-stone-200 px-4 py-2.5 text-sm focus:border-egg-500 focus:ring-2 focus:ring-egg-400/20 outline-none transition-all"
                           placeholder="Mín. 8 caracteres, letra y número">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-stone-700 mb-2">Confirmar contraseña *</label>
                    <input type="password" name="password_confirmation" required
                           class="w-full rounded-xl border border-stone-200 px-4 py-2.5 text-sm focus:border-egg-500 focus:ring-2 focus:ring-egg-400/20 outline-none transition-all">
                </div>
            </div>

            <div class="px-0 py-4 border-t border-stone-200 flex items-center justify-end gap-3 mt-4">
                <a href="{{ route('admin.usuarios.index') }}"
                   class="px-5 py-2.5 text-sm font-semibold text-stone-700 bg-white border border-stone-200 hover:bg-stone-100 rounded-xl transition-all">
                    Cancelar
                </a>
                <button type="submit"
                        class="px-6 py-2.5 text-sm font-semibold text-egg-400 bg-gradient-to-r from-egg-900 to-egg-800 hover:from-egg-800 hover:to-egg-700 rounded-xl transition-all shadow-md flex items-center gap-2">
                    <i class="fas fa-save"></i> Guardar Usuario
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

<x-guest-layout>
    <div class="space-y-6">
        <div class="text-center space-y-2">
            <h2 class="text-3xl font-extrabold font-heading text-egg-900 tracking-tight">Crear cuenta</h2>
            <p class="text-stone-500 text-sm">Regístrate para empezar a pedir</p>
        </div>

        <form method="POST" action="{{ route('register') }}" class="space-y-4">
            @csrf

            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-stone-600 mb-1">Nombre completo *</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-stone-400">
                        <i class="fas fa-user"></i>
                    </div>
                    <input type="text" name="nombre" value="{{ old('nombre') }}" required
                           class="w-full pl-10 pr-4 py-3 bg-stone-50 border border-stone-200 rounded-xl text-stone-800 text-sm focus:bg-white focus:border-egg-500 focus:ring-4 focus:ring-egg-400/20 transition-all outline-none"
                           placeholder="Tu nombre">
                </div>
                <x-input-error :messages="$errors->get('nombre')" class="mt-1 text-xs text-rose-600" />
            </div>

            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-stone-600 mb-1">Correo electrónico *</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-stone-400">
                        <i class="fas fa-envelope"></i>
                    </div>
                    <input type="email" name="correo" value="{{ old('correo') }}" required
                           class="w-full pl-10 pr-4 py-3 bg-stone-50 border border-stone-200 rounded-xl text-stone-800 text-sm focus:bg-white focus:border-egg-500 focus:ring-4 focus:ring-egg-400/20 transition-all outline-none"
                           placeholder="tu@correo.com">
                </div>
                <x-input-error :messages="$errors->get('correo')" class="mt-1 text-xs text-rose-600" />
            </div>

            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-stone-600 mb-1">Teléfono (opcional)</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-stone-400">
                        <i class="fas fa-phone"></i>
                    </div>
                    <input type="text" name="telefono" value="{{ old('telefono') }}"
                           class="w-full pl-10 pr-4 py-3 bg-stone-50 border border-stone-200 rounded-xl text-stone-800 text-sm focus:bg-white focus:border-egg-500 focus:ring-4 focus:ring-egg-400/20 transition-all outline-none"
                           placeholder="300 000 0000">
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-stone-600 mb-1">Contraseña *</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-stone-400">
                        <i class="fas fa-lock"></i>
                    </div>
                    <input type="password" name="password" required
                           class="w-full pl-10 pr-4 py-3 bg-stone-50 border border-stone-200 rounded-xl text-stone-800 text-sm focus:bg-white focus:border-egg-500 focus:ring-4 focus:ring-egg-400/20 transition-all outline-none"
                           placeholder="Mín. 8 caracteres, letra y número">
                </div>
                <x-input-error :messages="$errors->get('password')" class="mt-1 text-xs text-rose-600" />
            </div>

            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-stone-600 mb-1">Confirmar contraseña *</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-stone-400">
                        <i class="fas fa-lock"></i>
                    </div>
                    <input type="password" name="password_confirmation" required
                           class="w-full pl-10 pr-4 py-3 bg-stone-50 border border-stone-200 rounded-xl text-stone-800 text-sm focus:bg-white focus:border-egg-500 focus:ring-4 focus:ring-egg-400/20 transition-all outline-none"
                           placeholder="Repite la contraseña">
                </div>
            </div>

            <div class="pt-2">
                <button type="submit"
                        class="w-full bg-gradient-to-r from-egg-900 to-egg-800 hover:from-egg-800 hover:to-egg-700 text-egg-400 font-bold py-3.5 px-6 rounded-xl shadow-lg transition-all active:scale-[0.99] flex items-center justify-center gap-2 font-heading text-base">
                    <i class="fas fa-user-plus"></i> CREAR CUENTA
                </button>
            </div>
        </form>

        <div class="text-center pt-4 border-t border-stone-100">
            <p class="text-xs text-stone-500">
                ¿Ya tienes cuenta?
                <a href="{{ route('login') }}" class="font-bold text-egg-700 hover:text-egg-900 ml-1">Inicia sesión</a>
            </p>
        </div>
    </div>
</x-guest-layout>

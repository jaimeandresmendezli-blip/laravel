<x-guest-layout>
    <div class="space-y-6">
        <div class="text-center space-y-2">
            <h2 class="text-3xl font-extrabold font-heading text-egg-900 tracking-tight">Bienvenido de nuevo</h2>
            <p class="text-stone-500 text-sm">Ingresa tus credenciales para acceder</p>
        </div>

        <x-auth-session-status class="mb-4" :status="session('status')" />

        <form method="POST" action="{{ route('login') }}" class="space-y-5">
            @csrf

            <div>
                <label for="correo" class="block text-xs font-bold uppercase tracking-wider text-stone-600 mb-1">
                    Correo Electrónico *
                </label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-stone-400">
                        <i class="fas fa-envelope"></i>
                    </div>
                    <input id="correo" type="email" name="correo" value="{{ old('correo') }}" required autofocus
                           class="w-full pl-10 pr-4 py-3 bg-stone-50 border border-stone-200 rounded-xl text-stone-800 text-sm focus:bg-white focus:border-egg-500 focus:ring-4 focus:ring-egg-400/20 transition-all font-medium outline-none"
                           placeholder="tu@correo.com">
                </div>
                <x-input-error :messages="$errors->get('correo')" class="mt-2 text-xs text-rose-600 font-medium" />
            </div>

            <div>
                <div class="flex justify-between items-center mb-1">
                    <label for="password" class="block text-xs font-bold uppercase tracking-wider text-stone-600">
                        Contraseña *
                    </label>
                </div>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-stone-400">
                        <i class="fas fa-lock"></i>
                    </div>
                    <input id="password" type="password" name="password" required
                           class="w-full pl-10 pr-4 py-3 bg-stone-50 border border-stone-200 rounded-xl text-stone-800 text-sm focus:bg-white focus:border-egg-500 focus:ring-4 focus:ring-egg-400/20 transition-all font-medium outline-none"
                           placeholder="••••••••">
                </div>
                <x-input-error :messages="$errors->get('password')" class="mt-2 text-xs text-rose-600 font-medium" />
            </div>

            <div class="flex items-center gap-2 pt-1">
                <input id="remember_me" type="checkbox" class="w-4 h-4 rounded border-stone-300 text-egg-500 focus:ring-egg-400" name="remember">
                <label for="remember_me" class="text-xs font-medium text-stone-600 cursor-pointer">Recordar sesión</label>
            </div>

            <div class="pt-2">
                <button type="submit"
                        class="w-full bg-gradient-to-r from-egg-900 to-egg-800 hover:from-egg-800 hover:to-egg-700 text-egg-400 font-bold py-3.5 px-6 rounded-xl shadow-lg hover:shadow-xl transition-all active:scale-[0.99] flex items-center justify-center gap-2 font-heading text-base">
                    <i class="fas fa-sign-in-alt"></i> INICIAR SESIÓN
                </button>
            </div>
        </form>

        <div class="text-center pt-4 border-t border-stone-100">
            <p class="text-xs text-stone-500">
                ¿No tienes cuenta?
                <a href="{{ route('register') }}" class="font-bold text-egg-700 hover:text-egg-900 ml-1">
                    Regístrate gratis
                </a>
            </p>
        </div>
    </div>
</x-guest-layout>

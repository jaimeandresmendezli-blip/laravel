<!DOCTYPE html>
<html lang="es" class="h-full bg-amber-50">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>EGG EXPRESS — Portal Cliente</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Outfit:wght@500;600;700;800&display=swap" rel="stylesheet">

    <script src="https://kit.fontawesome.com/dcb1bbced2.js" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                        heading: ['Outfit', 'sans-serif'],
                    },
                    colors: {
                        egg: {
                            50:  '#fffdf0',
                            100: '#fef9d0',
                            200: '#fdf0a0',
                            300: '#fce46a',
                            400: '#F5C842',
                            500: '#e6b020',
                            600: '#c8900a',
                            700: '#a06d05',
                            800: '#7A5B00',
                            900: '#4A3A00',
                            950: '#2e2400',
                        },
                        cream: '#F5E6C8',
                    }
                }
            }
        }
    </script>
    <style>
        body { font-family: 'Inter', sans-serif; }
        h1, h2, h3, h4, h5, h6 { font-family: 'Outfit', sans-serif; }
        [x-cloak] { display: none !important; }
    </style>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    @yield('css')
</head>

<body class="h-full antialiased text-stone-800 bg-amber-50" x-data="{ sidebarOpen: false }">

    <div class="min-h-screen flex flex-col lg:flex-row">

        {{-- ===================== SIDEBAR CLIENTE ===================== --}}
        <aside class="w-full lg:w-72 bg-egg-900 text-egg-200 flex-shrink-0 flex flex-col justify-between border-r border-egg-950">
            <div>
                <div class="h-20 px-6 flex items-center justify-between bg-egg-950/70 border-b border-egg-950">
                    <a href="{{ route('cliente.dashboard') }}" class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-egg-400 to-egg-300 flex items-center justify-center text-egg-900 shadow-lg text-xl">
                            🛒
                        </div>
                        <div>
                            <span class="font-heading font-extrabold text-xl text-white tracking-tight">EGG<span class="text-egg-400">EXPRESS</span></span>
                            <span class="block text-[10px] text-egg-400 font-semibold tracking-widest uppercase">Portal Cliente</span>
                        </div>
                    </a>
                    <button @click="sidebarOpen = !sidebarOpen" class="lg:hidden text-egg-400 hover:text-white">
                        <i class="fas fa-bars text-xl"></i>
                    </button>
                </div>

                <div class="px-4 py-6 space-y-6" :class="{ 'block': sidebarOpen, 'hidden lg:block': !sidebarOpen }">

                    <div>
                        <a href="{{ route('cliente.dashboard') }}"
                           class="flex items-center gap-3 px-4 py-3 rounded-xl font-medium transition-all duration-150
                           {{ request()->routeIs('cliente.dashboard') ? 'bg-egg-400 text-egg-900 shadow-md font-bold' : 'text-egg-300 hover:text-white hover:bg-egg-800/60' }}">
                            <i class="fas fa-home text-lg w-5 text-center"></i>
                            <span>Inicio</span>
                        </a>
                    </div>

                    <div class="space-y-1">
                        <p class="px-4 text-[11px] font-bold text-egg-600 uppercase tracking-wider">Compras</p>

                        <a href="{{ route('cliente.catalogo.index') }}"
                           class="flex items-center gap-3 px-4 py-2.5 rounded-xl text-sm font-medium transition-all duration-150
                           {{ request()->routeIs('cliente.catalogo.*') ? 'bg-egg-800 text-egg-400 border-l-4 border-egg-400' : 'text-egg-300 hover:text-white hover:bg-egg-800/40' }}">
                            <i class="fas fa-egg text-egg-400 w-5 text-center"></i>
                            <span>Catálogo de Huevos</span>
                        </a>

                        <a href="{{ route('cliente.carrito.index') }}"
                           class="flex items-center gap-3 px-4 py-2.5 rounded-xl text-sm font-medium transition-all duration-150
                           {{ request()->routeIs('cliente.carrito.*') ? 'bg-egg-800 text-egg-400 border-l-4 border-egg-400' : 'text-egg-300 hover:text-white hover:bg-egg-800/40' }}">
                            <i class="fas fa-shopping-cart w-5 text-center"></i>
                            <span>Mi Carrito</span>
                        </a>

                        <a href="{{ route('cliente.pedidos.index') }}"
                           class="flex items-center gap-3 px-4 py-2.5 rounded-xl text-sm font-medium transition-all duration-150
                           {{ request()->routeIs('cliente.pedidos.*') ? 'bg-egg-800 text-egg-400 border-l-4 border-egg-400' : 'text-egg-300 hover:text-white hover:bg-egg-800/40' }}">
                            <i class="fas fa-box w-5 text-center"></i>
                            <span>Mis Pedidos</span>
                        </a>
                    </div>

                </div>
            </div>

            {{-- Footer usuario --}}
            <div class="p-4 bg-egg-950/80 border-t border-egg-950">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-egg-400 text-egg-900 flex items-center justify-center font-bold font-heading text-sm">
                        {{ strtoupper(substr(Auth::user()->nombre ?? 'C', 0, 1)) }}
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-semibold text-white truncate">{{ Auth::user()->nombre }}</p>
                        <p class="text-xs text-egg-400 font-medium">Cliente</p>
                    </div>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" title="Cerrar sesión" class="text-egg-500 hover:text-rose-400 transition-colors p-1">
                            <i class="fas fa-sign-out-alt"></i>
                        </button>
                    </form>
                </div>
            </div>
        </aside>

        {{-- Contenido --}}
        <div class="flex-1 flex flex-col min-w-0">
            <header class="h-16 bg-white/90 backdrop-blur-md border-b border-egg-200 px-6 lg:px-10 flex items-center justify-between sticky top-0 z-30 shadow-sm">
                <div class="flex items-center gap-3">
                    <button @click="sidebarOpen = !sidebarOpen" class="lg:hidden text-egg-700 mr-2">
                        <i class="fas fa-bars"></i>
                    </button>
                    <span class="inline-flex items-center gap-2 text-xs font-semibold px-3 py-1.5 rounded-full bg-egg-100 text-egg-800 border border-egg-300">
                        <span class="w-2 h-2 rounded-full bg-egg-400 animate-pulse"></span>
                        Portal Activo
                    </span>
                </div>

                <div class="flex items-center gap-3" x-data="{ open: false }">
                    <a href="{{ route('cliente.carrito.index') }}" class="relative p-2 text-egg-700 hover:text-egg-900 transition-colors">
                        <i class="fas fa-shopping-cart text-lg"></i>
                    </a>
                    <button @click="open = !open" class="flex items-center gap-2 px-3 py-2 rounded-xl hover:bg-egg-50 transition-colors">
                        <div class="w-8 h-8 rounded-lg bg-egg-400 text-egg-900 flex items-center justify-center font-bold text-sm">
                            {{ strtoupper(substr(Auth::user()->nombre ?? 'C', 0, 1)) }}
                        </div>
                        <span class="text-sm font-semibold text-stone-700 hidden sm:block">{{ Auth::user()->nombre }}</span>
                        <i class="fas fa-chevron-down text-xs text-stone-400"></i>
                    </button>

                    <div x-show="open" @click.away="open = false" x-cloak
                         class="absolute right-6 top-14 w-52 bg-white rounded-2xl shadow-xl border border-stone-100 py-2 z-50">
                        <div class="px-4 py-3 border-b border-stone-100">
                            <p class="text-xs text-stone-400">Mi cuenta</p>
                            <p class="text-sm font-bold text-stone-800 truncate">{{ Auth::user()->correo }}</p>
                        </div>
                        <form action="{{ route('logout') }}" method="POST">
                            @csrf
                            <button type="submit" class="w-full flex items-center gap-2 px-4 py-2.5 text-sm text-rose-600 hover:bg-rose-50 font-medium">
                                <i class="fas fa-sign-out-alt"></i> Cerrar Sesión
                            </button>
                        </form>
                    </div>
                </div>
            </header>

            <main class="flex-1 p-6 lg:p-8 max-w-7xl w-full mx-auto">
                @include('components.sweet-alerts')
                @yield('content')
            </main>
        </div>
    </div>

    @yield('js')
</body>
</html>

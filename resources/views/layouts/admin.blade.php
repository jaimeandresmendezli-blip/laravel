<!DOCTYPE html>
<html lang="es" class="h-full bg-amber-50">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>EGG EXPRESS — Panel Administrador</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('logo-egg.svg') }}">

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Outfit:wght@500;600;700;800&display=swap" rel="stylesheet">

    <!-- FontAwesome -->
    <script src="https://kit.fontawesome.com/dcb1bbced2.js" crossorigin="anonymous"></script>

    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <!-- Tailwind CSS CDN -->
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
                            400: '#F5C842', /* Amarillo principal */
                            500: '#e6b020',
                            600: '#c8900a',
                            700: '#a06d05',
                            800: '#7A5B00', /* Marrón secundario */
                            900: '#4A3A00', /* Marrón principal */
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
        .egg-pattern {
            background-image: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23c8900a' fill-opacity='0.08'%3E%3Cellipse cx='30' cy='28' rx='12' ry='15'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
        }
    </style>

    <!-- AlpineJS -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    @yield('css')
</head>

<body class="h-full antialiased text-stone-800 bg-amber-50 selection:bg-egg-400 selection:text-egg-900"
      x-data="{ sidebarOpen: false }">

    <div class="min-h-screen flex flex-col lg:flex-row">

        {{-- ===================== SIDEBAR ===================== --}}
        <aside class="lg:sticky lg:top-0 lg:h-screen w-full lg:w-72 bg-egg-900 text-egg-200 flex-shrink-0 flex flex-col justify-between border-r border-egg-950 z-40">
            <div>
                {{-- Marca --}}
                <div class="h-20 px-6 flex items-center justify-between bg-egg-950/70 border-b border-egg-950">
                    <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-3">
                        <img src="{{ asset('logo-egg.svg') }}" alt="EGG EXPRESS" class="w-10 h-10 rounded-xl shadow-lg shadow-egg-950/40">
                        <div>
                            <span class="font-heading font-extrabold text-xl text-white tracking-tight">EGG<span class="text-egg-400">EXPRESS</span></span>
                            <span class="block text-[10px] text-egg-400 font-semibold tracking-widest uppercase">Panel Administrador</span>
                        </div>
                    </a>
                    <button @click="sidebarOpen = !sidebarOpen" class="lg:hidden text-egg-400 hover:text-white focus:outline-none">
                        <i class="fas fa-bars text-xl"></i>
                    </button>
                </div>

                {{-- Menú de navegación --}}
                <div class="px-4 py-6 space-y-7 overflow-y-auto max-h-[calc(100vh-140px)]"
                     :class="{ 'block': sidebarOpen, 'hidden lg:block': !sidebarOpen }">

                    {{-- Dashboard --}}
                    <div>
                        <a href="{{ route('admin.dashboard') }}"
                           class="flex items-center gap-3 px-4 py-3 rounded-xl font-medium transition-all duration-150
                           {{ request()->routeIs('admin.dashboard') ? 'bg-egg-400 text-egg-900 shadow-md font-bold' : 'text-egg-300 hover:text-white hover:bg-egg-800/60' }}">
                            <i class="fas fa-chart-pie text-lg w-5 text-center"></i>
                            <span>Dashboard Principal</span>
                        </a>
                    </div>

                    {{-- Catálogo --}}
                    <div class="space-y-1">
                        <p class="px-4 text-[11px] font-bold text-egg-600 uppercase tracking-wider">Catálogo</p>

                        <a href="{{ route('admin.productos.index') }}"
                           class="flex items-center gap-3 px-4 py-2.5 rounded-xl text-sm font-medium transition-all duration-150
                           {{ request()->routeIs('admin.productos.*') ? 'bg-egg-800 text-egg-400 border-l-4 border-egg-400' : 'text-egg-300 hover:text-white hover:bg-egg-800/40' }}">
                            <i class="fas fa-egg w-5 text-center"></i>
                            <span>Productos</span>
                        </a>

                        <a href="{{ route('admin.usuarios.index') }}"
                           class="flex items-center gap-3 px-4 py-2.5 rounded-xl text-sm font-medium transition-all duration-150
                           {{ request()->routeIs('admin.usuarios.*') ? 'bg-egg-800 text-egg-400 border-l-4 border-egg-400' : 'text-egg-300 hover:text-white hover:bg-egg-800/40' }}">
                            <i class="fas fa-users w-5 text-center"></i>
                            <span>Usuarios</span>
                        </a>
                    </div>

                    {{-- Inventario --}}
                    <div class="space-y-1">
                        <p class="px-4 text-[11px] font-bold text-egg-600 uppercase tracking-wider">Inventario</p>

                        <a href="{{ route('admin.inventario.index') }}"
                           class="flex items-center gap-3 px-4 py-2.5 rounded-xl text-sm font-medium transition-all duration-150
                           {{ request()->routeIs('admin.inventario.index') ? 'bg-egg-800 text-egg-400 border-l-4 border-egg-400' : 'text-egg-300 hover:text-white hover:bg-egg-800/40' }}">
                            <i class="fas fa-boxes w-5 text-center"></i>
                            <span>Historial Movimientos</span>
                        </a>

                    </div>

                    {{-- Operaciones --}}
                    <div class="space-y-1">
                        <p class="px-4 text-[11px] font-bold text-egg-600 uppercase tracking-wider">Operaciones</p>

                        <a href="{{ route('admin.pedidos.index') }}"
                           class="flex items-center gap-3 px-4 py-2.5 rounded-xl text-sm font-medium transition-all duration-150
                           {{ request()->routeIs('admin.pedidos.*') ? 'bg-egg-800 text-egg-400 border-l-4 border-egg-400' : 'text-egg-300 hover:text-white hover:bg-egg-800/40' }}">
                            <i class="fas fa-shopping-basket w-5 text-center"></i>
                            <span>Pedidos</span>
                        </a>
                    </div>

                    {{-- Reportes --}}
                    <div class="space-y-1">
                        <p class="px-4 text-[11px] font-bold text-egg-600 uppercase tracking-wider">Reportes</p>

                        <a href="{{ route('admin.reportes.index') }}"
                           class="flex items-center gap-3 px-4 py-2.5 rounded-xl text-sm font-medium transition-all duration-150
                           {{ request()->routeIs('admin.reportes.*') ? 'bg-egg-800 text-egg-400 border-l-4 border-egg-400' : 'text-egg-300 hover:text-white hover:bg-egg-800/40' }}">
                            <i class="fas fa-chart-line w-5 text-center"></i>
                            <span>Reportes del Sistema</span>
                        </a>
                    </div>

                </div>
            </div>

            {{-- Footer usuario --}}
            <div class="p-4 mt-auto">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-full bg-egg-800/80 text-egg-200 flex items-center justify-center font-bold font-heading text-sm">
                        {{ strtoupper(substr(Auth::user()->nombre ?? 'A', 0, 1)) }}
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-semibold text-white truncate">{{ Auth::user()->nombre }}</p>
                        <p class="text-[11px] text-egg-400 truncate">{{ Auth::user()->correo ?? 'Administrador' }}</p>
                    </div>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" title="Cerrar sesión" class="text-egg-500 hover:text-egg-300 transition-colors p-2">
                            <i class="fas fa-sign-out-alt text-lg"></i>
                        </button>
                    </form>
                </div>
            </div>
        </aside>

        {{-- ===================== CONTENIDO ===================== --}}
        <div class="flex-1 flex flex-col min-w-0">

            {{-- Header sticky --}}
            <header class="h-16 bg-white/90 backdrop-blur-md border-b border-egg-200 px-6 lg:px-10 flex items-center justify-between sticky top-0 z-30 shadow-sm">
                <div class="flex items-center gap-3">
                    <button @click="sidebarOpen = !sidebarOpen" class="lg:hidden text-egg-700 hover:text-egg-900 mr-2">
                        <i class="fas fa-bars"></i>
                    </button>
                </div>

                <div class="flex items-center gap-3" x-data="{ open: false }">
                    <button @click="open = !open" class="flex items-center gap-2 px-3 py-2 rounded-xl hover:bg-egg-50 transition-colors">
                        <div class="w-8 h-8 rounded-lg bg-egg-400 text-egg-900 flex items-center justify-center font-bold text-sm font-heading">
                            {{ strtoupper(substr(Auth::user()->nombre ?? 'A', 0, 1)) }}
                        </div>
                        <span class="text-sm font-semibold text-stone-700 hidden sm:block">{{ Auth::user()->nombre }}</span>
                        <i class="fas fa-chevron-down text-xs text-stone-400"></i>
                    </button>

                    <div x-show="open" @click.away="open = false" x-cloak
                         class="absolute right-6 top-14 w-52 bg-white rounded-2xl shadow-xl border border-stone-100 py-2 z-50">
                        <div class="px-4 py-3 border-b border-stone-100">
                            <p class="text-xs text-stone-400">Sesión activa</p>
                            <p class="text-sm font-bold text-stone-800 truncate">{{ Auth::user()->correo }}</p>
                        </div>
                        <div class="border-t border-stone-100 mt-1">
                            <form action="{{ route('logout') }}" method="POST">
                                @csrf
                                <button type="submit" class="w-full flex items-center gap-2 px-4 py-2.5 text-sm text-rose-600 hover:bg-rose-50 transition-colors font-medium">
                                    <i class="fas fa-sign-out-alt"></i> Cerrar Sesión
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </header>

            {{-- Contenido principal --}}
            <main class="flex-1 p-6 lg:p-8 max-w-7xl w-full mx-auto">
                @include('components.sweet-alerts')
                @yield('content')
            </main>
        </div>
    </div>

    @stack('scripts')
    @yield('js')
</body>
</html>

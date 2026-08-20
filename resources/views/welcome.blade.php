<!DOCTYPE html>
<html lang="es" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>EGG EXPRESS — Comercializadora de Huevos</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Outfit:wght@500;600;700;800;900&display=swap" rel="stylesheet">
    <script src="https://kit.fontawesome.com/dcb1bbced2.js" crossorigin="anonymous"></script>

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
                    },
                    animation: {
                        'float': 'float 3s ease-in-out infinite',
                        'float-slow': 'float 5s ease-in-out infinite',
                    },
                    keyframes: {
                        float: {
                            '0%, 100%': { transform: 'translateY(0px)' },
                            '50%': { transform: 'translateY(-12px)' },
                        }
                    }
                }
            }
        }
    </script>
    <style>
        body { font-family: 'Inter', sans-serif; }
        h1, h2, h3, h4, h5, h6 { font-family: 'Outfit', sans-serif; }
        .hero-pattern {
            background-color: #4A3A00;
            background-image: url("data:image/svg+xml,%3Csvg width='80' height='80' viewBox='0 0 80 80' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23c8900a' fill-opacity='0.10'%3E%3Cellipse cx='40' cy='38' rx='16' ry='20'/%3E%3Cellipse cx='10' cy='10' rx='7' ry='9'/%3E%3Cellipse cx='70' cy='68' rx='7' ry='9'/%3E%3Cellipse cx='70' cy='10' rx='5' ry='6'/%3E%3Cellipse cx='10' cy='70' rx='5' ry='6'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
        }
        .card-hover { transition: transform 0.3s ease, box-shadow 0.3s ease; }
        .card-hover:hover { transform: translateY(-6px); box-shadow: 0 20px 40px -10px rgba(74,58,0,0.15); }
    </style>
</head>
<body class="antialiased bg-amber-50 text-stone-800 selection:bg-egg-400 selection:text-egg-900">

    {{-- ======================== NAVBAR ======================== --}}
    <nav class="absolute top-0 w-full z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-20">
                {{-- Logo --}}
                <a href="/" class="flex items-center gap-3 group">
                    <div class="w-10 h-10 rounded-xl bg-egg-400 flex items-center justify-center text-egg-900 text-xl shadow-lg group-hover:scale-105 transition-transform">
                        🥚
                    </div>
                    <div>
                        <span class="font-heading font-extrabold text-xl text-white tracking-tight">EGG<span class="text-egg-400">EXPRESS</span></span>
                        <span class="block text-[9px] text-egg-300 font-semibold tracking-widest uppercase leading-none">Comercializadora de Huevos</span>
                    </div>
                </a>

                {{-- Links --}}
                <div class="hidden md:flex items-center gap-8">
                    <a href="#productos" class="text-stone-300 hover:text-white font-medium transition-colors text-sm">Productos</a>
                    <a href="#nosotros" class="text-stone-300 hover:text-white font-medium transition-colors text-sm">Nosotros</a>
                    <a href="#como-funciona" class="text-stone-300 hover:text-white font-medium transition-colors text-sm">¿Cómo funciona?</a>
                </div>

                {{-- Auth buttons --}}
                <div class="flex items-center gap-3">
                    @auth
                        @if(Auth::user()->esAdmin())
                            <a href="{{ route('admin.dashboard') }}"
                               class="text-white bg-egg-600 hover:bg-egg-500 px-5 py-2.5 rounded-xl font-semibold text-sm transition-all shadow-lg">
                                Panel Admin
                            </a>
                        @else
                            <a href="{{ route('cliente.dashboard') }}"
                               class="text-white bg-egg-600 hover:bg-egg-500 px-5 py-2.5 rounded-xl font-semibold text-sm transition-all shadow-lg">
                                Mi Panel
                            </a>
                        @endif
                    @else
                        <a href="{{ route('login') }}" class="text-stone-300 hover:text-white font-medium text-sm transition-colors">
                            Ingresar
                        </a>
                        <a href="{{ route('register') }}"
                           class="bg-egg-400 hover:bg-egg-300 text-egg-900 font-bold px-5 py-2.5 rounded-xl text-sm transition-all shadow-lg hover:shadow-egg-400/30 hover:-translate-y-0.5">
                            Registrarse
                        </a>
                    @endauth
                </div>
            </div>
        </div>
    </nav>

    {{-- ======================== HERO ======================== --}}
    <section class="relative hero-pattern min-h-screen flex items-center overflow-hidden">
        {{-- Overlay gradiente --}}
        <div class="absolute inset-0 bg-gradient-to-b from-egg-950/85 via-egg-900/75 to-egg-900/90"></div>

        {{-- Glows decorativos --}}
        <div class="absolute -top-40 -right-40 w-96 h-96 bg-egg-400/20 rounded-full blur-3xl pointer-events-none animate-pulse"></div>
        <div class="absolute -bottom-40 -left-40 w-96 h-96 bg-egg-500/15 rounded-full blur-3xl pointer-events-none"></div>

        {{-- Huevos flotantes decorativos --}}
        <div class="absolute top-32 right-20 text-6xl opacity-20 animate-float select-none hidden lg:block">🥚</div>
        <div class="absolute bottom-32 right-40 text-4xl opacity-15 animate-float-slow select-none hidden lg:block">🥚</div>
        <div class="absolute top-1/2 left-10 text-3xl opacity-10 animate-float select-none hidden lg:block">🥚</div>

        <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-24 pb-20 w-full">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 items-center">

                {{-- Contenido texto --}}
                <div class="space-y-8 text-center lg:text-left">
                    {{-- Badge --}}
                    <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-egg-400/20 border border-egg-400/30 text-egg-300 text-xs font-semibold backdrop-blur-sm">
                        <span class="relative flex h-2.5 w-2.5">
                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-egg-400 opacity-75"></span>
                            <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-egg-400"></span>
                        </span>
                        Entregas disponibles hoy
                    </div>

                    <h1 class="text-4xl md:text-5xl lg:text-6xl font-extrabold text-white leading-[1.05] font-heading">
                        Huevos frescos,
                        <span class="text-transparent bg-clip-text bg-gradient-to-r from-egg-300 to-egg-400">
                            directo a tu mesa
                        </span>
                    </h1>

                    <p class="text-lg text-stone-300 max-w-xl mx-auto lg:mx-0 leading-relaxed">
                        La mejor selección de huevos frescos: rojos, blancos, codorniz y más.
                        Pedidos en línea con entrega rápida a tu domicilio.
                    </p>

                    <div class="flex flex-col sm:flex-row gap-4 justify-center lg:justify-start">
                        @auth
                            <a href="{{ route('cliente.catalogo.index') }}"
                               class="inline-flex justify-center items-center gap-2 px-8 py-4 bg-egg-400 hover:bg-egg-300 text-egg-900 rounded-xl font-bold text-lg transition-all shadow-[0_0_30px_rgba(245,200,66,0.35)] hover:shadow-[0_0_40px_rgba(245,200,66,0.5)] hover:-translate-y-1 font-heading">
                                🥚 Ver Catálogo
                            </a>
                        @else
                            <a href="{{ route('register') }}"
                               class="inline-flex justify-center items-center gap-2 px-8 py-4 bg-egg-400 hover:bg-egg-300 text-egg-900 rounded-xl font-bold text-lg transition-all shadow-[0_0_30px_rgba(245,200,66,0.35)] hover:shadow-[0_0_40px_rgba(245,200,66,0.5)] hover:-translate-y-1 font-heading">
                                🥚 Pedir ahora
                            </a>
                            <a href="{{ route('login') }}"
                               class="inline-flex justify-center items-center gap-2 px-8 py-4 bg-white/10 hover:bg-white/20 text-white rounded-xl font-semibold text-lg transition-all backdrop-blur-md border border-white/20 hover:-translate-y-0.5">
                                <i class="fas fa-sign-in-alt text-sm"></i> Ingresar
                            </a>
                        @endauth
                    </div>

                    {{-- Stats --}}
                    <div class="flex gap-8 justify-center lg:justify-start pt-4">
                        <div class="text-center lg:text-left">
                            <p class="text-2xl font-extrabold text-egg-400 font-heading">100%</p>
                            <p class="text-xs text-stone-400 font-medium">Frescos</p>
                        </div>
                        <div class="w-px bg-stone-700"></div>
                        <div class="text-center lg:text-left">
                            <p class="text-2xl font-extrabold text-egg-400 font-heading">24h</p>
                            <p class="text-xs text-stone-400 font-medium">Entrega rápida</p>
                        </div>
                        <div class="w-px bg-stone-700"></div>
                        <div class="text-center lg:text-left">
                            <p class="text-2xl font-extrabold text-egg-400 font-heading">3+</p>
                            <p class="text-xs text-stone-400 font-medium">Tipos de huevo</p>
                        </div>
                    </div>
                </div>

                {{-- Visual hero --}}
                <div class="hidden lg:flex items-center justify-center relative">
                    <div class="absolute inset-0 bg-gradient-to-tr from-egg-400/20 to-egg-300/10 rounded-[3rem] blur-2xl"></div>
                    <div class="relative bg-egg-900/50 backdrop-blur-sm border border-egg-700/30 rounded-[3rem] p-10 shadow-2xl">
                        <div class="text-center space-y-4">
                            <div class="text-9xl animate-float select-none">🥚</div>
                            <div class="flex justify-center gap-3">
                                <div class="text-5xl animate-float-slow select-none" style="animation-delay:0.5s">🥚</div>
                                <div class="text-5xl animate-float select-none" style="animation-delay:1s">🥚</div>
                                <div class="text-5xl animate-float-slow select-none" style="animation-delay:1.5s">🥚</div>
                            </div>
                            <p class="text-egg-300 font-bold font-heading text-lg">EGG EXPRESS</p>
                            <p class="text-egg-500 text-sm">Calidad garantizada</p>
                        </div>
                    </div>
                </div>

            </div>
        </div>

        {{-- Wave bottom --}}
        <div class="absolute bottom-0 w-full leading-none z-0">
            <svg class="relative block w-full h-12 md:h-20" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1200 120" preserveAspectRatio="none">
                <path d="M321.39,56.44c58-10.79,114.16-30.13,172-41.86,82.39-16.72,168.19-17.73,250.45-.39C823.78,31,906.67,72,985.66,92.83c70.05,18.48,146.53,26.09,214.34,3V120H0V95.8C59.71,118.08,130.83,120.22,192.4,107.56,236.4,98.63,279.79,78.27,321.39,56.44Z" class="fill-amber-50"></path>
            </svg>
        </div>
    </section>

    {{-- ======================== PRODUCTOS ======================== --}}
    <section id="productos" class="py-20 bg-amber-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center max-w-2xl mx-auto mb-14">
                <span class="text-egg-600 font-bold tracking-widest uppercase text-sm">Catálogo</span>
                <h2 class="text-3xl md:text-5xl font-extrabold text-egg-900 font-heading tracking-tight mt-2">
                    Nuestros Productos
                </h2>
                <p class="mt-4 text-stone-500 text-lg">Huevos frescos seleccionados para ti. Inicia sesión para pedir.</p>
            </div>

            @php
                $productosPublicos = \App\Models\Producto::where('estado','activo')->orderBy('nombre')->take(8)->get();
            @endphp

            @if($productosPublicos->isEmpty())
                <div class="text-center py-12 text-stone-400">
                    <div class="text-6xl mb-4 select-none">🥚</div>
                    <p>No hay productos disponibles aún.</p>
                </div>
            @else
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                @foreach($productosPublicos as $p)
                <div class="bg-white rounded-2xl overflow-hidden border border-stone-100 shadow-sm card-hover flex flex-col">
                    <div class="h-44 bg-amber-50 flex items-center justify-center relative p-4">
                        @if($p->imagen)
                            <img src="{{ asset('storage/'.$p->imagen) }}" alt="{{ $p->nombre }}"
                                 class="max-h-full max-w-full object-contain">
                        @else
                            <div class="text-6xl select-none opacity-50">🥚</div>
                        @endif
                        @if($p->cantidad <= 0)
                            <span class="absolute top-2 right-2 bg-rose-600 text-white text-xs px-2.5 py-1 rounded-full font-bold">Agotado</span>
                        @elseif($p->cantidad <= 5)
                            <span class="absolute top-2 right-2 bg-amber-500 text-white text-xs px-2.5 py-1 rounded-full font-bold">Poco stock</span>
                        @endif
                    </div>
                    <div class="p-5 flex-1 flex flex-col justify-between">
                        <div>
                            @if($p->tipo_huevo)
                                <span class="text-[10px] font-bold uppercase tracking-wider bg-egg-100 text-egg-700 px-2 py-0.5 rounded-full">{{ $p->tipo_huevo }}</span>
                            @endif
                            <h3 class="font-bold text-egg-900 font-heading text-base mt-2 mb-1 line-clamp-2">{{ $p->nombre }}</h3>
                            @if($p->presentacion)
                                <p class="text-xs text-stone-400">{{ $p->presentacion }}</p>
                            @endif
                        </div>
                        <div class="mt-4 border-t border-stone-100 pt-4 flex items-center justify-between">
                            <span class="text-2xl font-extrabold text-egg-800 font-heading">${{ number_format($p->precio,2) }}</span>
                            @auth
                                @if(Auth::user()->esCliente())
                                    <a href="{{ route('cliente.catalogo.show', $p->id_producto) }}"
                                       class="bg-egg-900 hover:bg-egg-800 text-egg-400 font-semibold px-4 py-2 rounded-xl text-xs transition-all shadow-sm">
                                        Ver y Pedir
                                    </a>
                                @endif
                            @else
                                <a href="{{ route('login') }}"
                                   class="bg-egg-900 hover:bg-egg-800 text-egg-400 font-semibold px-4 py-2 rounded-xl text-xs transition-all shadow-sm">
                                    Pedir
                                </a>
                            @endauth
                        </div>
                    </div>
                </div>
                @endforeach
            </div>

            @if($productosPublicos->count() >= 8)
            <div class="text-center mt-10">
                @auth
                    <a href="{{ route('cliente.catalogo.index') }}"
                       class="inline-flex items-center gap-2 bg-egg-900 hover:bg-egg-800 text-egg-400 font-bold px-8 py-4 rounded-xl shadow-lg transition-all font-heading">
                        Ver catálogo completo <i class="fas fa-arrow-right"></i>
                    </a>
                @else
                    <a href="{{ route('register') }}"
                       class="inline-flex items-center gap-2 bg-egg-900 hover:bg-egg-800 text-egg-400 font-bold px-8 py-4 rounded-xl shadow-lg transition-all font-heading">
                        Regístrate para ver todo <i class="fas fa-arrow-right"></i>
                    </a>
                @endauth
            </div>
            @endif
            @endif
        </div>
    </section>

    {{-- ======================== CÓMO FUNCIONA ======================== --}}
    <section id="como-funciona" class="py-20 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center max-w-2xl mx-auto mb-14">
                <span class="text-egg-600 font-bold tracking-widest uppercase text-sm">Proceso</span>
                <h2 class="text-3xl md:text-5xl font-extrabold text-egg-900 font-heading tracking-tight mt-2">
                    ¿Cómo funciona?
                </h2>
                <p class="mt-4 text-stone-500 text-lg">Simple, rápido y seguro.</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                @foreach([
                    ['emoji'=>'📝','num'=>'01','title'=>'Regístrate','desc'=>'Crea tu cuenta gratis en minutos. Solo necesitas tu correo y contraseña.','color'=>'bg-egg-50 border-egg-200'],
                    ['emoji'=>'🥚','num'=>'02','title'=>'Elige tus huevos','desc'=>'Explora nuestro catálogo y agrega al carrito los productos que quieras.','color'=>'bg-amber-50 border-amber-200'],
                    ['emoji'=>'🚚','num'=>'03','title'=>'Confirma tu pedido','desc'=>'Ingresa tu dirección, elige el método de pago y confirma.','color'=>'bg-orange-50 border-orange-200'],
                    ['emoji'=>'✅','num'=>'04','title'=>'Recibe en casa','desc'=>'Nosotros nos encargamos de llevar tus huevos frescos directamente a tu puerta.','color'=>'bg-emerald-50 border-emerald-200'],
                ] as $step)
                <div class="bg-white rounded-[2rem] p-7 border {{ $step['color'] }} border-2 text-center card-hover relative">
                    <span class="absolute top-4 right-5 text-[11px] font-bold text-stone-300 font-mono">{{ $step['num'] }}</span>
                    <div class="text-5xl mb-5 select-none">{{ $step['emoji'] }}</div>
                    <h3 class="text-xl font-bold text-egg-900 font-heading mb-3">{{ $step['title'] }}</h3>
                    <p class="text-stone-500 text-sm leading-relaxed">{{ $step['desc'] }}</p>
                </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- ======================== NOSOTROS ======================== --}}
    <section id="nosotros" class="py-20 bg-egg-900 relative overflow-hidden">
        <div class="absolute inset-0 opacity-5" style="background-image: url(\"data:image/svg+xml,%3Csvg width='80' height='80' viewBox='0 0 80 80' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='%23F5C842' fill-opacity='1'%3E%3Cellipse cx='40' cy='38' rx='16' ry='20'/%3E%3C/g%3E%3C/svg%3E\");"></div>
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 items-center">
                <div class="space-y-6 text-center lg:text-left">
                    <span class="text-egg-400 font-bold tracking-widest uppercase text-sm">Quiénes somos</span>
                    <h2 class="text-3xl md:text-4xl font-extrabold text-white font-heading leading-tight">
                        Pasión por la <span class="text-egg-400">calidad</span> en cada huevo
                    </h2>
                    <p class="text-stone-300 text-lg leading-relaxed">
                        Somos una comercializadora de huevos con años de experiencia conectando productores locales con familias y negocios. Garantizamos frescura, calidad y entrega puntual.
                    </p>
                    <div class="grid grid-cols-2 gap-5">
                        @foreach([
                            ['icon'=>'fa-egg','label'=>'Huevos frescos','val'=>'Diario'],
                            ['icon'=>'fa-truck','label'=>'Entregas','val'=>'A domicilio'],
                            ['icon'=>'fa-shield-alt','label'=>'Calidad','val'=>'Garantizada'],
                            ['icon'=>'fa-heart','label'=>'Clientes felices','val'=>'Siempre'],
                        ] as $f)
                        <div class="flex items-center gap-3 bg-egg-800/50 rounded-2xl p-4 border border-egg-700/30">
                            <div class="w-10 h-10 rounded-xl bg-egg-400/20 text-egg-400 flex items-center justify-center flex-shrink-0">
                                <i class="fas {{ $f['icon'] }}"></i>
                            </div>
                            <div>
                                <p class="font-bold text-egg-400 text-sm font-heading">{{ $f['val'] }}</p>
                                <p class="text-xs text-stone-400">{{ $f['label'] }}</p>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>

                <div class="flex justify-center">
                    <div class="relative">
                        <div class="absolute inset-0 bg-egg-400/20 rounded-[3rem] blur-2xl"></div>
                        <div class="relative bg-egg-800/60 backdrop-blur-sm border border-egg-700/40 rounded-[3rem] p-12 text-center shadow-2xl">
                            <div class="text-8xl mb-4 select-none">🥚</div>
                            <h3 class="text-2xl font-extrabold text-egg-400 font-heading mb-2">EGG EXPRESS</h3>
                            <p class="text-stone-400 text-sm">Tu comercializadora de confianza</p>
                            <div class="mt-6 flex flex-col gap-2 text-sm">
                                <div class="flex items-center gap-2 text-stone-300">
                                    <i class="fas fa-check-circle text-egg-400 text-xs"></i> Huevos de gallina
                                </div>
                                <div class="flex items-center gap-2 text-stone-300">
                                    <i class="fas fa-check-circle text-egg-400 text-xs"></i> Huevos de codorniz
                                </div>
                                <div class="flex items-center gap-2 text-stone-300">
                                    <i class="fas fa-check-circle text-egg-400 text-xs"></i> Diferentes presentaciones
                                </div>
                                <div class="flex items-center gap-2 text-stone-300">
                                    <i class="fas fa-check-circle text-egg-400 text-xs"></i> Precios justos
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- ======================== CTA FINAL ======================== --}}
    <section class="py-20 bg-gradient-to-r from-egg-400 to-egg-300">
        <div class="max-w-4xl mx-auto px-4 text-center space-y-6">
            <div class="text-6xl select-none">🥚</div>
            <h2 class="text-3xl md:text-5xl font-extrabold text-egg-900 font-heading">
                ¿Listo para pedir?
            </h2>
            <p class="text-egg-800 text-lg max-w-xl mx-auto leading-relaxed">
                Crea tu cuenta gratis y realiza tu primer pedido hoy mismo. Huevos frescos a tu puerta.
            </p>
            <div class="flex flex-col sm:flex-row gap-4 justify-center">
                @auth
                    <a href="{{ Auth::user()->esAdmin() ? route('admin.dashboard') : route('cliente.catalogo.index') }}"
                       class="inline-flex items-center gap-2 bg-egg-900 hover:bg-egg-800 text-egg-400 font-bold px-8 py-4 rounded-xl shadow-xl text-lg transition-all font-heading hover:-translate-y-0.5">
                        🥚 Ir al catálogo
                    </a>
                @else
                    <a href="{{ route('register') }}"
                       class="inline-flex items-center gap-2 bg-egg-900 hover:bg-egg-800 text-egg-400 font-bold px-8 py-4 rounded-xl shadow-xl text-lg transition-all font-heading hover:-translate-y-0.5">
                        🥚 Crear cuenta gratis
                    </a>
                    <a href="{{ route('login') }}"
                       class="inline-flex items-center gap-2 bg-white/60 hover:bg-white text-egg-900 font-bold px-8 py-4 rounded-xl text-lg transition-all font-heading hover:-translate-y-0.5">
                        Ya tengo cuenta
                    </a>
                @endauth
            </div>
        </div>
    </section>

    {{-- ======================== FOOTER ======================== --}}
    <footer class="bg-egg-950 text-stone-400 py-10 border-t border-egg-900">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col md:flex-row justify-between items-center gap-4">
                <div class="flex items-center gap-3">
                    <span class="text-2xl">🥚</span>
                    <div>
                        <span class="font-heading font-extrabold text-xl text-white tracking-tight">EGG<span class="text-egg-400">EXPRESS</span></span>
                        <p class="text-xs text-stone-500">Comercializadora de Huevos</p>
                    </div>
                </div>
                <div class="flex items-center gap-6 text-sm">
                    <a href="{{ route('login') }}" class="hover:text-egg-400 transition-colors">Ingresar</a>
                    <a href="{{ route('register') }}" class="hover:text-egg-400 transition-colors">Registrarse</a>
                </div>
                <p class="text-xs text-stone-600">&copy; {{ date('Y') }} EGG EXPRESS. Todos los derechos reservados.</p>
            </div>
        </div>
    </footer>

</body>
</html>

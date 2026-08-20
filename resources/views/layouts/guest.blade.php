<!DOCTYPE html>
<html lang="es" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>EGG EXPRESS — Autenticación</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Outfit:wght@500;600;700;800&display=swap" rel="stylesheet">

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
                    }
                }
            }
        }
    </script>
    <style>
        body { font-family: 'Inter', sans-serif; }
        h1, h2, h3, h4, h5, h6 { font-family: 'Outfit', sans-serif; }
        .egg-hero-pattern {
            background-color: #4A3A00;
            background-image: url("data:image/svg+xml,%3Csvg width='80' height='80' viewBox='0 0 80 80' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23c8900a' fill-opacity='0.12'%3E%3Cellipse cx='40' cy='38' rx='16' ry='20'/%3E%3Cellipse cx='10' cy='10' rx='7' ry='9'/%3E%3Cellipse cx='70' cy='68' rx='7' ry='9'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
        }
    </style>
</head>
<body class="h-full font-sans antialiased selection:bg-egg-400 selection:text-egg-900">

    <div class="min-h-screen flex flex-col lg:flex-row">

        {{-- Panel izquierdo: Branding --}}
        <div class="lg:w-1/2 egg-hero-pattern p-8 lg:p-16 flex flex-col justify-between relative overflow-hidden text-white border-b lg:border-b-0 lg:border-r border-egg-950">
            {{-- Glow --}}
            <div class="absolute -top-32 -left-32 w-96 h-96 bg-egg-400/20 rounded-full blur-3xl pointer-events-none"></div>
            <div class="absolute -bottom-32 -right-32 w-96 h-96 bg-egg-500/15 rounded-full blur-3xl pointer-events-none"></div>

            {{-- Logo --}}
            <div class="relative z-10">
                <a href="{{ url('/') }}" class="inline-flex items-center gap-3 group">
                    <div class="w-14 h-14 rounded-2xl bg-gradient-to-tr from-egg-400 to-egg-300 flex items-center justify-center text-egg-900 shadow-xl text-3xl group-hover:scale-105 transition-transform">
                        🥚
                    </div>
                    <div>
                        <span class="font-heading font-extrabold text-2xl text-white tracking-tight">EGG<span class="text-egg-400">EXPRESS</span></span>
                        <span class="block text-[10px] text-egg-300 font-bold tracking-widest uppercase">Comercializadora de Huevos</span>
                    </div>
                </a>
            </div>

            {{-- Hero content --}}
            <div class="relative z-10 my-12 lg:my-0 space-y-6">
                <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-egg-400/20 border border-egg-400/30 text-egg-300 text-xs font-semibold">
                    <i class="fas fa-shield-alt"></i> Plataforma de Ventas Segura
                </div>
                <h1 class="text-3xl lg:text-5xl font-extrabold font-heading text-white leading-tight">
                    Los mejores huevos, <span class="text-transparent bg-clip-text bg-gradient-to-r from-egg-300 to-egg-400">directo a tu puerta</span>
                </h1>
                <p class="text-stone-300 text-sm lg:text-base max-w-lg leading-relaxed">
                    Gestiona tu pedido de huevos frescos de manera rápida y sencilla. Catálogo actualizado, entrega a domicilio y pagos seguros.
                </p>
                <div class="grid grid-cols-2 gap-4 pt-4 text-xs font-medium text-stone-300">
                    <div class="flex items-center gap-2"><i class="fas fa-check-circle text-egg-400"></i> Huevos frescos</div>
                    <div class="flex items-center gap-2"><i class="fas fa-check-circle text-egg-400"></i> Entrega a domicilio</div>
                    <div class="flex items-center gap-2"><i class="fas fa-check-circle text-egg-400"></i> Pedidos en línea</div>
                    <div class="flex items-center gap-2"><i class="fas fa-check-circle text-egg-400"></i> Pagos seguros</div>
                </div>
            </div>

            <div class="relative z-10 text-xs text-egg-700 flex justify-between items-center border-t border-egg-800/50 pt-6">
                <span>&copy; {{ date('Y') }} EGG EXPRESS. Todos los derechos reservados.</span>
                <a href="{{ url('/') }}" class="text-egg-400 hover:text-white font-medium transition-colors flex items-center gap-1">
                    Ver catálogo <i class="fas fa-arrow-right text-[10px]"></i>
                </a>
            </div>
        </div>

        {{-- Panel derecho: Formulario --}}
        <div class="lg:w-1/2 bg-amber-50 flex items-center justify-center p-6 sm:p-12 lg:p-16">
            <div class="w-full max-w-md space-y-8 bg-white p-8 sm:p-10 rounded-3xl border border-egg-200 shadow-xl">
                {{ $slot }}
            </div>
        </div>
    </div>

</body>
</html>

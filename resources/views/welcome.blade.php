<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>HP Cars - Alquiler de Vehículos</title>
        <meta name="description" content="HP Cars - Sistema de gestión de alquiler de vehículos">

        <link rel="icon" href="/favicon.ico" sizes="any">
        <link rel="icon" href="/favicon.svg" type="image/svg+xml">
        <link rel="apple-touch-icon" href="/apple-touch-icon.png">

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="min-h-screen bg-hpcars-dark text-white antialiased">
        <!-- Hero Section -->
        <div class="relative min-h-screen flex flex-col">
            <!-- Navigation -->
            <header class="absolute top-0 left-0 right-0 z-50">
                <nav class="container mx-auto px-6 py-6">
                    <div class="flex items-center justify-between">
                        <!-- Logo -->
                        <div class="flex items-center gap-3">
                            <div class="size-12 rounded-xl overflow-hidden shadow-lg">
                                <img src="{{ asset('assets/imgs/hpcars-logo.jpeg') }}" alt="HP Cars" class="size-12 object-cover" />
                            </div>
                            <div class="hidden sm:block">
                                <span class="text-primary font-bold text-xl">HP Cars</span>
                                <p class="text-zinc-400 text-xs">Alquiler de Vehículos</p>
                            </div>
                        </div>

                        <!-- Auth Links -->
                        @if (Route::has('login'))
                            <div class="flex items-center gap-3">
                                @auth
                                    <a href="{{ url('/dashboard') }}" class="px-5 py-2.5 bg-primary text-hpcars-dark font-semibold rounded-lg hover:bg-primary-dark transition-colors">
                                        Panel
                                    </a>
                                @else
                                    <a href="{{ route('login') }}" class="px-5 py-2.5 bg-primary text-hpcars-dark font-semibold rounded-lg hover:bg-primary-dark transition-colors">
                                        Iniciar Sesión
                                    </a>
                                @endauth
                            </div>
                        @endif
                    </div>
                </nav>
            </header>

            <!-- Hero Content -->
            <main class="flex-1 flex items-center justify-center px-6">
                <div class="max-w-4xl mx-auto text-center">
                    <!-- Large Logo -->
                    <div class="mb-8 flex justify-center">
                        <div class="size-32 md:size-40 rounded-2xl overflow-hidden shadow-2xl ring-4 ring-primary/20">
                            <img src="{{ asset('assets/imgs/hpcars-logo.jpeg') }}" alt="HP Cars" class="size-full object-cover" />
                        </div>
                    </div>

                    <!-- Tagline -->
                    <h1 class="text-4xl md:text-6xl font-bold mb-4">
                        <span class="text-primary">HP</span> Cars
                    </h1>
                    <p class="text-xl md:text-2xl text-zinc-400 mb-8">
                        Alquiler de Vehículos de Confianza
                    </p>

                    <!-- Features -->
                    <div class="grid md:grid-cols-3 gap-6 mt-12 text-left">
                        <div class="p-6 rounded-xl bg-zinc-900/50 border border-zinc-800">
                            <div class="size-12 rounded-lg bg-primary/10 flex items-center justify-center mb-4">
                                <svg class="size-6 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                            <h3 class="text-lg font-semibold text-white mb-2">Gestión de Flota</h3>
                            <p class="text-zinc-400 text-sm">Control completo de vehículos, disponibilidad y mantenimiento.</p>
                        </div>

                        <div class="p-6 rounded-xl bg-zinc-900/50 border border-zinc-800">
                            <div class="size-12 rounded-lg bg-primary/10 flex items-center justify-center mb-4">
                                <svg class="size-6 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                                </svg>
                            </div>
                            <h3 class="text-lg font-semibold text-white mb-2">Clientes</h3>
                            <p class="text-zinc-400 text-sm">Base de datos de clientes con historial de alquileres.</p>
                        </div>

                        <div class="p-6 rounded-xl bg-zinc-900/50 border border-zinc-800">
                            <div class="size-12 rounded-lg bg-primary/10 flex items-center justify-center mb-4">
                                <svg class="size-6 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                </svg>
                            </div>
                            <h3 class="text-lg font-semibold text-white mb-2">Contratos</h3>
                            <p class="text-zinc-400 text-sm">Generación automática de contratos PDF profesionales.</p>
                        </div>
                    </div>

                    <!-- CTA -->
                    @guest
                        <div class="mt-12">
                            <a href="{{ route('login') }}" class="inline-flex items-center gap-2 px-8 py-4 bg-primary text-hpcars-dark font-bold text-lg rounded-xl hover:bg-primary-dark transition-colors shadow-lg shadow-primary/20">
                                Acceder al Sistema
                                <svg class="size-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"></path>
                                </svg>
                            </a>
                        </div>
                    @endguest
                </div>
            </main>

            <!-- Footer -->
            <footer class="py-6 text-center text-zinc-500 text-sm">
                <p>&copy; {{ date('Y') }} HP Cars. Todos los derechos reservados.</p>
            </footer>
        </div>
    </body>
</html>

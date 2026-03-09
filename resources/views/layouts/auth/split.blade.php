<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        @include('partials.head')
    </head>
    <body class="min-h-screen bg-hpcars-light antialiased dark:bg-hpcars-dark">
        <div class="relative grid h-dvh flex-col items-center justify-center px-8 sm:px-0 lg:max-w-none lg:grid-cols-2 lg:px-0">
            <div class="relative hidden h-full flex-col p-10 text-white lg:flex dark:border-e dark:border-zinc-800">
                <div class="absolute inset-0 bg-hpcars-dark"></div>
                <a href="{{ route('home') }}" class="relative z-20 flex items-center gap-3 text-lg font-medium" wire:navigate>
                    <span class="flex size-12 items-center justify-center rounded-xl overflow-hidden">
                        <img src="{{ asset('assets/imgs/hpcars-logo.jpeg') }}" alt="HP Cars" class="size-12 object-cover" />
                    </span>
                    <span class="text-primary font-semibold">HP Cars</span>
                </a>

                <div class="relative z-20 mt-auto">
                    <blockquote class="space-y-2">
                        <flux:heading size="lg" class="text-primary">&ldquo;Alquiler de vehículos de confianza&rdquo;</flux:heading>
                        <footer><flux:heading class="text-zinc-400">HP Cars - Tu movilidad, nuestra prioridad</flux:heading></footer>
                    </blockquote>
                </div>
            </div>
            <div class="w-full lg:p-8">
                <div class="mx-auto flex w-full flex-col justify-center space-y-6 sm:w-[350px]">
                    <a href="{{ route('home') }}" class="z-20 flex flex-col items-center gap-4 font-medium lg:hidden" wire:navigate>
                        <span class="flex size-20 items-center justify-center rounded-xl bg-hpcars-dark overflow-hidden shadow-lg">
                            <img src="{{ asset('assets/imgs/hpcars-logo.jpeg') }}" alt="HP Cars" class="size-20 object-cover" />
                        </span>
                        <span class="sr-only">{{ config('app.name', 'HP Cars') }}</span>
                    </a>
                    {{ $slot }}
                </div>
            </div>
        </div>
        @fluxScripts
    </body>
</html>

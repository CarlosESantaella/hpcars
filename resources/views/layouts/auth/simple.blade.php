<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        @include('partials.head')
        @if(config('app.demo_mode'))
        <style>
            .demo-mode-active button,
            .demo-mode-active [type="submit"],
            .demo-mode-active input,
            .demo-mode-active form {
                pointer-events: none !important;
                opacity: 0.6 !important;
                cursor: not-allowed !important;
            }
            .demo-mode-active a[href] {
                pointer-events: auto !important;
                opacity: 1 !important;
            }
            .demo-banner {
                background: linear-gradient(90deg, #cdd722, #b5bf1e);
                color: #030102;
                text-align: center;
                padding: 0.5rem;
                font-weight: 600;
                font-size: 0.875rem;
                position: fixed;
                top: 0;
                left: 0;
                right: 0;
                z-index: 9999;
            }
        </style>
        @endif
    </head>
    <body class="min-h-screen bg-hpcars-light antialiased dark:bg-hpcars-dark @if(config('app.demo_mode')) demo-mode-active @endif">
        @if(config('app.demo_mode'))
        <div class="demo-banner">
            MODO DEMO - Vista de diseño
        </div>
        @endif
        <div class="flex min-h-svh flex-col items-center justify-center gap-6 p-6 md:p-10">
            <div class="flex w-full max-w-sm flex-col gap-2">
                <a href="{{ route('home') }}" class="flex flex-col items-center gap-4 font-medium" wire:navigate>
                    <span class="flex size-20 items-center justify-center rounded-xl bg-hpcars-dark overflow-hidden shadow-lg">
                        <img src="{{ asset('assets/imgs/hpcars-logo.jpeg') }}" alt="HP Cars" class="size-20 object-cover" />
                    </span>
                    <span class="sr-only">{{ config('app.name', 'HP Cars') }}</span>
                </a>
                <div class="flex flex-col gap-6 mt-4">
                    {{ $slot }}
                </div>
            </div>
        </div>
        @fluxScripts
    </body>
</html>

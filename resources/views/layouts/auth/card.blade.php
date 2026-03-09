<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        @include('partials.head')
    </head>
    <body class="min-h-screen bg-hpcars-light antialiased dark:bg-hpcars-dark">
        <div class="flex min-h-svh flex-col items-center justify-center gap-6 p-6 md:p-10">
            <div class="flex w-full max-w-md flex-col gap-6">
                <a href="{{ route('home') }}" class="flex flex-col items-center gap-4 font-medium" wire:navigate>
                    <span class="flex size-20 items-center justify-center rounded-xl bg-hpcars-dark overflow-hidden shadow-lg">
                        <img src="{{ asset('assets/imgs/hpcars-logo.jpeg') }}" alt="HP Cars" class="size-20 object-cover" />
                    </span>
                    <span class="sr-only">{{ config('app.name', 'HP Cars') }}</span>
                </a>

                <div class="flex flex-col gap-6">
                    <div class="rounded-xl border border-zinc-200 bg-white dark:bg-zinc-900 dark:border-zinc-800 text-zinc-800 dark:text-zinc-200 shadow-sm">
                        <div class="px-10 py-8">{{ $slot }}</div>
                    </div>
                </div>
            </div>
        </div>
        @fluxScripts
    </body>
</html>

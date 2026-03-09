<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">

<head>
    @include('partials.head')
    @if (config('app.demo_mode'))
        <style>
            /* Demo Mode Styles - Disable all interactive elements */
            .demo-mode-active button:not(.demo-nav-allowed),
            .demo-mode-active [type="submit"],
            .demo-mode-active input,
            .demo-mode-active select,
            .demo-mode-active textarea,
            .demo-mode-active [wire\:click],
            .demo-mode-active [wire\:submit],
            .demo-mode-active [wire\:model],
            .demo-mode-active form {
                pointer-events: none !important;
                opacity: 0.6 !important;
                cursor: not-allowed !important;
            }

            /* Keep navigation working */
            .demo-mode-active a[href],
            .demo-mode-active [wire\:navigate],
            .demo-mode-active flux-sidebar-item,
            .demo-mode-active [data-flux-sidebar-item],
            .demo-mode-active .demo-nav-allowed {
                pointer-events: auto !important;
                opacity: 1 !important;
                cursor: pointer !important;
            }

            /* Demo banner */
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

            .demo-mode-active {
                padding-top: 2.5rem;
            }
        </style>
    @endif
</head>

<body class="min-h-screen bg-hpcars-light dark:bg-hpcars-dark @if (config('app.demo_mode')) demo-mode-active @endif">
    @if (config('app.demo_mode'))
        {{-- <div class="demo-banner">
            🎨 MODO DEMOSTRACIÓN - Solo visualización de diseños
        </div> --}}
    @endif
    <flux:sidebar sticky collapsible="mobile"
        class="border-e border-zinc-200 bg-white dark:border-zinc-800 dark:bg-zinc-950">
        <flux:sidebar.header>
            <x-app-logo :sidebar="true" href="{{ route('dashboard') }}" wire:navigate />
            <flux:sidebar.collapse class="lg:hidden" />
        </flux:sidebar.header>

        <flux:sidebar.nav>
            <flux:sidebar.group heading="Plataforma" class="grid">
                <flux:sidebar.item icon="home" :href="route('dashboard')" :current="request()->routeIs('dashboard')"
                    wire:navigate>
                    Panel
                </flux:sidebar.item>
                <flux:sidebar.item icon="bell" :href="route('notifications.index')"
                    :current="request()->routeIs('notifications.*')" wire:navigate
                    :badge="App\Models\Notification::unread()->count() ?: null">
                    Notificaciones
                </flux:sidebar.item>
            </flux:sidebar.group>

            <flux:sidebar.group heading="Gestión" class="grid">
                <flux:sidebar.item icon="truck" :href="route('vehicles.index')"
                    :current="request()->routeIs('vehicles.*')" wire:navigate>
                    Vehículos
                </flux:sidebar.item>
                <flux:sidebar.item icon="users" :href="route('clients.index')"
                    :current="request()->routeIs('clients.*')" wire:navigate>
                    Clientes
                </flux:sidebar.item>
                <flux:sidebar.item icon="calendar" :href="route('reservations.index')"
                    :current="request()->routeIs('reservations.*')" wire:navigate>
                    Reservas
                </flux:sidebar.item>
                <flux:sidebar.item icon="document-text" :href="route('contracts.index')"
                    :current="request()->routeIs('contracts.*')" wire:navigate>
                    Contratos
                </flux:sidebar.item>
            </flux:sidebar.group>
        </flux:sidebar.nav>

        <flux:spacer />

        <x-desktop-user-menu class="hidden lg:block" :name="auth()->user()->name" />
    </flux:sidebar>


    <!-- Mobile User Menu -->
    <flux:header class="lg:hidden">
        <flux:sidebar.toggle class="lg:hidden" icon="bars-2" inset="left" />

        <flux:spacer />

        <flux:dropdown position="top" align="end">
            <flux:profile :initials="auth()->user()->initials()" icon-trailing="chevron-down" />

            <flux:menu>
                <flux:menu.radio.group>
                    <div class="p-0 text-sm font-normal">
                        <div class="flex items-center gap-2 px-1 py-1.5 text-start text-sm">
                            <flux:avatar :name="auth()->user()->name" :initials="auth()->user()->initials()" />

                            <div class="grid flex-1 text-start text-sm leading-tight">
                                <flux:heading class="truncate">{{ auth()->user()->name }}</flux:heading>
                                <flux:text class="truncate">{{ auth()->user()->email }}</flux:text>
                            </div>
                        </div>
                    </div>
                </flux:menu.radio.group>

                <flux:menu.separator />

                <flux:menu.radio.group>
                    <flux:menu.item :href="route('profile.edit')" icon="cog" wire:navigate>
                        Configuración
                    </flux:menu.item>
                </flux:menu.radio.group>

                <flux:menu.separator />

                <form method="POST" action="{{ route('logout') }}" class="w-full">
                    @csrf
                    <flux:menu.item as="button" type="submit" icon="arrow-right-start-on-rectangle"
                        class="w-full cursor-pointer" data-test="logout-button">
                        Cerrar Sesión
                    </flux:menu.item>
                </form>
            </flux:menu>
        </flux:dropdown>
    </flux:header>

    {{ $slot }}

    @fluxScripts
</body>

</html>

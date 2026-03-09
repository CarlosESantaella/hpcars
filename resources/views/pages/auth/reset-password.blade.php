<x-layouts::auth>
    <div class="flex flex-col gap-6">
        <x-auth-header title="Restablecer contraseña" description="Ingresa tu nueva contraseña a continuación" />

        <!-- Estado de Sesión -->
        <x-auth-session-status class="text-center" :status="session('status')" />

        <form method="POST" action="{{ route('password.update') }}" class="flex flex-col gap-6">
            @csrf
            <!-- Token -->
            <input type="hidden" name="token" value="{{ request()->route('token') }}">

            <!-- Correo Electrónico -->
            <flux:input
                name="email"
                value="{{ request('email') }}"
                label="Correo electrónico"
                type="email"
                required
                autocomplete="email"
            />

            <!-- Contraseña -->
            <flux:input
                name="password"
                label="Contraseña"
                type="password"
                required
                autocomplete="new-password"
                placeholder="Contraseña"
                viewable
            />

            <!-- Confirmar Contraseña -->
            <flux:input
                name="password_confirmation"
                label="Confirmar contraseña"
                type="password"
                required
                autocomplete="new-password"
                placeholder="Confirmar contraseña"
                viewable
            />

            <div class="flex items-center justify-end">
                <flux:button type="submit" variant="primary" class="w-full" data-test="reset-password-button">
                    Restablecer contraseña
                </flux:button>
            </div>
        </form>
    </div>
</x-layouts::auth>

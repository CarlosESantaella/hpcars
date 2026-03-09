@props([
    'sidebar' => false,
])

@if($sidebar)
    <flux:sidebar.brand name="HP Cars" {{ $attributes }}>
        <x-slot name="logo" class="flex aspect-square size-10 items-center justify-center rounded-lg bg-hpcars-dark overflow-hidden">
            <img src="{{ asset('assets/imgs/hpcars-logo.jpeg') }}" alt="HP Cars" class="size-10 object-cover" />
        </x-slot>
    </flux:sidebar.brand>
@else
    <flux:brand name="HP Cars" {{ $attributes }}>
        <x-slot name="logo" class="flex aspect-square size-10 items-center justify-center rounded-lg bg-hpcars-dark overflow-hidden">
            <img src="{{ asset('assets/imgs/hpcars-logo.jpeg') }}" alt="HP Cars" class="size-10 object-cover" />
        </x-slot>
    </flux:brand>
@endif

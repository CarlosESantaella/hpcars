@props([
    'title',
    'description',
])

<div class="flex w-full flex-col text-center">
    <flux:heading size="xl" class="text-zinc-900 dark:text-white">{{ $title }}</flux:heading>
    <flux:subheading class="text-zinc-600 dark:text-zinc-400">{{ $description }}</flux:subheading>
</div>

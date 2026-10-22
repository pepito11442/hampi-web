@props([
    'icon' => 'info',
    'title' => null,
    'description' => null,
])

<div {{ $attributes->merge(['class' => 'border border-dashed border-ink-300 bg-paper-50/70 px-6 py-10 text-center']) }}>
    <span class="icon-seal mx-auto">
        <x-icon :name="$icon" class="h-5 w-5" />
    </span>

    <h3 class="mt-4 font-display text-lg font-bold text-ink-950">{{ $title }}</h3>

    @if ($description)
        <p class="mx-auto mt-2 max-w-md text-sm leading-relaxed text-ink-600">{{ $description }}</p>
    @endif

    @isset($action)
        <div class="mt-5 flex justify-center">{{ $action }}</div>
    @endisset
</div>

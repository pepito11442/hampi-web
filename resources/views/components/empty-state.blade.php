@props([
    'icon' => 'info',
    'title' => null,
    'description' => null,
])

<div {{ $attributes->merge(['class' => 'rounded-3xl border border-dashed border-ink-200 bg-white/70 px-6 py-12 text-center']) }}>
    <span class="mx-auto grid h-14 w-14 place-items-center rounded-2xl bg-brand-50 text-brand-700">
        <x-icon :name="$icon" class="h-7 w-7" />
    </span>

    <h3 class="mt-4 text-base font-bold text-ink-900">{{ $title }}</h3>

    @if ($description)
        <p class="mx-auto mt-2 max-w-md text-sm leading-relaxed text-ink-500">{{ $description }}</p>
    @endif

    @isset($action)
        <div class="mt-6 flex justify-center">{{ $action }}</div>
    @endisset
</div>

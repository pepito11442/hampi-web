@props([
    'eyebrow' => null,
    'title' => null,
    'description' => null,
    'icon' => 'shield-plus',
])

<section class="relative overflow-hidden">
    <div class="container-page relative py-10 sm:py-14">
        <div class="flex flex-col gap-6 sm:flex-row sm:items-center sm:justify-between">
            <div class="max-w-2xl">
                @if ($eyebrow)
                    <span class="eyebrow">{{ $eyebrow }}</span>
                @endif

                <h1 class="mt-3 text-3xl font-bold leading-tight tracking-tight text-ink-900 sm:text-4xl">
                    {{ $title ?? $slot }}
                </h1>

                @if ($description)
                    <p class="mt-3 text-sm leading-relaxed text-ink-500 sm:text-base">{{ $description }}</p>
                @endif
            </div>

            <div class="hidden shrink-0 sm:grid" aria-hidden="true">
                <span class="grid h-20 w-20 place-items-center rounded-3xl bg-gradient-to-br from-brand-500 to-brand-700 text-white shadow-glow">
                    <x-icon :name="$icon" class="h-9 w-9" />
                </span>
            </div>
        </div>

        @isset($actions)
            <div class="mt-6 flex flex-wrap items-center gap-3">
                {{ $actions }}
            </div>
        @endisset
    </div>
</section>

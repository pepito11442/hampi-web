@props([
    'eyebrow' => null,
    'title' => null,
    'description' => null,
    'icon' => 'shield-plus',
])

<section class="border-b border-ink-300">
    <div class="container-page py-8 sm:py-11">
        <div class="flex flex-col gap-6 lg:flex-row lg:items-end lg:justify-between">
            <div class="max-w-3xl">
                <div class="mb-5 flex flex-wrap items-center justify-between gap-3 border-b border-ink-300 pb-2">
                    <span class="mono-label text-ink-600">Hampi / {{ $eyebrow ?? 'Microred Socabaya' }}</span>
                    <span class="font-mono text-[10px] uppercase tracking-[0.06em] text-ink-500">
                        {{ now()->locale('es')->isoFormat('D MMM YYYY') }}
                    </span>
                </div>

                <h1 class="section-title">
                    {{ $title ?? $slot }}
                </h1>

                @if ($description)
                    <p class="section-lead mt-4">{{ $description }}</p>
                @endif
            </div>

            <div class="flex shrink-0 flex-col items-start gap-3 lg:items-end">
                <span class="icon-seal hidden lg:grid" aria-hidden="true">
                    <x-icon :name="$icon" class="h-5 w-5" />
                </span>

                @isset($actions)
                    <div class="flex flex-wrap items-center gap-2">
                        {{ $actions }}
                    </div>
                @endisset
            </div>
        </div>
    </div>
</section>

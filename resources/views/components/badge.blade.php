@props(['color' => 'teal'])

@php
    $palette = match ($color) {
        'sky' => 'badge badge-sky',
        'amber' => 'badge badge-amber',
        'red', 'rose' => 'badge badge-red',
        'slate' => 'badge badge-slate',
        default => 'badge badge-teal',
    };
@endphp

<span {{ $attributes->merge(['class' => $palette]) }}>
    {{ $slot }}
</span>

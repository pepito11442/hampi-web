@props([
    'nav' => null,
])

{{--
    Tablero común de las páginas internas: una hoja blanca sobre el
    corcho de la posta. El hero y el contenido viven en hojas separadas
    para que todas las pantallas compartan la misma silueta.
--}}
<section {{ $attributes->class([
    'container-page',
    'grid',
    'grid-cols-1',
    'bg-ledger-carteles',
    'gap-8',
    'px-5',
    'pt-5',
    'pb-16',
    'sm:px-8',
    'sm:pt-8',
    'sm:pb-20',
    'tarjeta',
    'backdrop-blur-sm',
]) }}>
    @isset($hero)
        <section class="sheet-doctor p-3 sm:p-4 tarjeta">
            {{ $hero }}
        </section>
    @endisset

    @isset($nav)
        <div>{{ $nav }}</div>
    @endisset

    <section class="sheet-doctor p-3 sm:p-4 tarjeta">
        {{ $slot }}
    </section>
</section>

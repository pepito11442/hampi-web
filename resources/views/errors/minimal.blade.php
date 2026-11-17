{{--
    Reemplaza la página de error de Laravel (403, 404, 419, 500, 503…).
    La original trae <style> en línea, que la CSP ya no permite. Esta es
    independiente del layout para no depender de la base de datos.
--}}
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex">
    <title>@yield('title') · Hampi</title>
    @vite(['resources/css/app.css'])
</head>
<body class="flex min-h-screen flex-col bg-paper-100 text-ink-800">
    <main class="container-page flex flex-1 items-center justify-center py-16">
        <div class="mx-auto w-full max-w-lg border border-ink-300 bg-paper-50 p-6 text-center shadow-lift sm:p-9">
            <span class="stamp">Error @yield('code')</span>
            <h1 class="mt-3 font-display text-3xl font-bold tracking-[-0.035em] text-ink-950">@yield('message')</h1>
            <p class="mt-4 text-sm leading-relaxed text-ink-600">
                No pudimos mostrar esta página. Vuelve al portal e inténtalo de nuevo.
            </p>
            <div class="mt-7 flex justify-center">
                <a href="/" class="btn btn-primary">Ir al portal</a>
            </div>
        </div>
    </main>
</body>
</html>

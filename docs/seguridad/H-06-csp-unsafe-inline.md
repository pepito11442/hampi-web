# H-06 · CSP con `style-src 'unsafe-inline'`

| Campo | Detalle |
|---|---|
| Categoría | A02:2025 Security Misconfiguration · WSTG-CONF-12 (CSP) |
| Severidad | Baja · 2.6 · `CVSS:3.1/AV:N/AC:H/PR:L/UI:R/S:U/C:L/I:N/A:N` |
| Estado | Corregido (falta desplegar en Render) |

**Por qué 2.6**: haría falta primero una inyección de HTML (todo el texto se escapa con `{{ }}`, así que AC:H), una cuenta que pueda publicar texto (biografía o campaña, PR:L) y que otra persona abra la página (UI:R). Con CSS inyectado solo se puede filtrar poca información o engañar visualmente (C:L).

## Pasos para reproducir
1. `curl -sI https://hampi-bqb8.onrender.com | grep -i content-security-policy` → `style-src 'self' 'unsafe-inline'`.
2. `grep -rn 'style="' resources/views` → 6 atributos `style` (gráfico del admin, barras de progreso, retraso de animación).

## Evidencia
```
style-src 'self' 'unsafe-inline'
resources/views/hampi/admin_dashboard.blade.php:127: style="height: {{ max($alto, …) }}%"
resources/views/hampi/admin_dashboard.blade.php:128: style="height: {{ $altoAtendidas }}%"
resources/views/empleado/panel.blade.php:92:        style="width: {{ $progreso }}%"
resources/views/hampi/admin/horarios.blade.php:167: style="width: {{ $progreso }}%"
resources/views/hampi/admin/equipo.blade.php:164:   style="--reveal-delay: …ms"
resources/views/hampi/admin/campanas.blade.php:133: style="--reveal-delay: …ms"
```
Además, las páginas de error de Laravel (404, 500…) traen `<style>` en línea.

## Impacto
Si apareciera una inyección de HTML, `'unsafe-inline'` permitiría usar CSS para leer valores de atributos (por ejemplo, el token CSRF con selectores `[value^=…]`) o tapar la página con un formulario falso.

## Remediación
- `SecurityHeaders.php`: `style-src 'self' 'nonce-{$nonce}'` (sin `'unsafe-inline'`).
- Vistas: `style="…"` → `data-alto`, `data-ancho`, `data-reveal-delay`.
- `resources/js/app.js` → `initEstilosDinamicos()`: aplica esos valores con `el.style.height = …` (CSSOM, que la CSP sí permite), limitando el porcentaje a 0–100.
- `resources/views/errors/minimal.blade.php` (nuevo): página de error propia con el CSS de Vite, sin `<style>` en línea y sin depender de la base de datos.

## Verificación
PHPUnit (`HallazgosSeguridadTest`):
- `test_h06_la_csp_no_permite_estilos_en_linea`.
- `test_h06_el_grafico_del_admin_no_usa_style_en_linea`.
- `test_h06_la_pagina_404_no_usa_estilos_en_linea`.

Tras desplegar, revisión manual: abrir `/admin/dashboard`, `/admin/horarios`, `/empleado/panel` y una URL inexistente con DevTools → Console. No debe haber errores *Refused to apply inline style*, y las barras del gráfico deben tener altura.

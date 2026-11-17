# H-05 · `X-Frame-Options: SAMEORIGIN` en vez de `DENY`

| Campo | Detalle |
|---|---|
| Categoría | A02:2025 Security Misconfiguration · WSTG-CLNT-09 (clickjacking) |
| Severidad | Baja · 3.1 · `CVSS:3.1/AV:N/AC:H/PR:N/UI:R/S:U/C:N/I:L/A:N` |
| Estado | Corregido (falta desplegar en Render) |

**Por qué 3.1**: con `SAMEORIGIN` solo una página del propio dominio puede incrustar Hampi, así que el atacante necesitaría antes otra falla (por ejemplo, un HTML subido al mismo origen) (AC:H) y que la víctima haga clic (UI:R). El efecto es una acción no deseada (I:L).

## Pasos para reproducir
1. `curl -sI https://hampi-bqb8.onrender.com | grep -i -E "x-frame|frame-ancestors"`.

## Evidencia
`evidencia/cabeceras-produccion.txt`:
```
x-frame-options: SAMEORIGIN
content-security-policy: … frame-ancestors 'self'; …
```

## Impacto
Si apareciera una forma de publicar HTML en el mismo origen, un atacante podría superponer Hampi en un `<iframe>` invisible y hacer que un médico o admin pulse "Cancelar cita" o "Eliminar" sin saberlo. Hampi no usa iframes propios, así que `DENY` no rompe nada.

## Remediación
`app/Http/Middleware/SecurityHeaders.php`:
```diff
- $respuesta->headers->set('X-Frame-Options', 'SAMEORIGIN');
+ $respuesta->headers->set('X-Frame-Options', 'DENY');
…
- "frame-ancestors 'self'",
+ "frame-ancestors 'none'",
```

## Verificación
- PHPUnit: `HallazgosSeguridadTest::test_h05_el_portal_no_se_puede_incrustar_en_iframes`.
- Tras desplegar: repetir el `curl` (esperado `x-frame-options: DENY` y `frame-ancestors 'none'`), CP-14 del plan.

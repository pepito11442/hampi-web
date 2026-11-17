# H-07 · CORS abierto (`Access-Control-Allow-Origin: *`) en `/api/*`

| Campo | Detalle |
|---|---|
| Categoría | A01:2025 Broken Access Control, A02:2025 Security Misconfiguration · WSTG-CLNT-07 |
| Severidad | Baja · 3.1 · `CVSS:3.1/AV:N/AC:H/PR:N/UI:R/S:U/C:L/I:N/A:N` |
| Estado | Corregido (falta desplegar en Render) |

Hallazgo nuevo (B-7): no estaba en la revisión de código. Apareció en la evidencia del `curl` de H-02.

**Por qué 3.1**: la respuesta ya era pública (H-02), así que CORS solo añade que otro sitio pueda hacer las consultas desde el navegador y la IP de sus visitantes (UI:R), repartiendo el límite por IP (AC:H). Expone nombres (C:L).

## Pasos para reproducir
1. `curl -s -i -H "Origin: https://sitio-ajeno.example" https://hampi-bqb8.onrender.com/api/consultar-dni/72345678 | grep -i access-control`.

## Evidencia
`evidencia/consultar-dni-produccion.txt`:
```
access-control-allow-origin: *
```
Causa: el proyecto no tenía `config/cors.php`, así que Laravel usaba su configuración por defecto (`paths: ['api/*']`, `allowed_origins: ['*']`).

## Impacto
Un sitio malicioso podría consultar DNIs desde el navegador de cada visitante y saltarse así el límite por IP. Tras H-02 ya no funciona sin token, pero no hay motivo para permitir otros orígenes.

## Remediación
`config/cors.php` (nuevo) con `'paths' => []` y `'allowed_origins' => []`: Laravel deja de enviar cabeceras CORS. El único consumidor de `/api/*` es el propio formulario (mismo origen).

## Verificación
- PHPUnit: `HallazgosSeguridadTest::test_h07_la_api_no_envia_cors_abierto`.
- Tras desplegar: repetir el `curl` (no debe aparecer `access-control-allow-origin`).

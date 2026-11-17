# H-01 · Laravel 11 sin soporte y 13 avisos de seguridad en dependencias

| Campo | Detalle |
|---|---|
| Categoría | A03:2025 Software Supply Chain Failures · WSTG-CONF-01 |
| Severidad | Alta · 7.5 · `CVSS:3.1/AV:N/AC:L/PR:N/UI:N/S:U/C:N/I:H/A:N` |
| Estado | Corregido (falta desplegar en Render) |

**Por qué 7.5**: se toma el peor aviso aplicable (inyección CRLF en la regla `email` de Laravel, severidad *high*): se explota por red, sin cuenta ni interacción, y permite alterar cabeceras de correo (integridad alta); no expone datos por sí solo.

## Pasos para reproducir
1. `grep -n "laravel/framework" composer.json` → `"^11.36"`.
2. `grep -A1 '"name": "laravel/framework"' composer.lock` → `v11.57.0`.
3. `composer audit` → 13 avisos en 6 paquetes.

El commit `3ee5c11` ("Laravel 12") solo modificó `.claude/settings.local.json`; la versión nunca cambió.

## Evidencia
- `evidencia/composer-audit-antes.txt`: 5 avisos de `laravel/framework` (uno *high*: CRLF en la regla `email`; uno *medium*: confusión de rutas en URLs firmadas temporales) y 8 de `symfony/*` (proxies de confianza, cabeceras `Forwarded`, inyección en cabeceras MIME, DoS en el parser YAML).
- `npm audit`: 0 vulnerabilidades.

## Impacto
Un atacante podría usar fallos ya publicados, con parche disponible, contra un framework que dejó de recibir correcciones. Hampi valida correos con la regla `email` (recuperación de clave, alta de personal) y envía correos por Brevo, así que la inyección de cabeceras es la más relevante. `trustProxies(at: '*')` en `bootstrap/app.php` también hace relevantes los avisos de cabeceras reenviadas de Symfony.

## Remediación
```diff
- "laravel/framework": "^11.36",
+ "laravel/framework": "^12.0",
```
`composer require laravel/framework:^12.0 -W` y `composer update symfony/yaml` (el último aviso venía de `laravel/roster`, dependencia de desarrollo).
Resultado: `laravel/framework v12.73.0`, `symfony/* 7.4.22–7.4.23`. No hizo falta cambiar código: la estructura de `bootstrap/app.php` ya era la de Laravel 11/12.

## Verificación
- `composer audit` → *No security vulnerability advisories found* (`evidencia/composer-audit-despues.txt`).
- `php artisan test` → 79 pruebas pasan (65 previas + 14 nuevas).
- PHPUnit: `HallazgosSeguridadTest::test_h01_el_framework_es_laravel_12_o_superior`.
- Pendiente: desplegar en Render y revisar que el build de Docker (`composer:2` + `php:8.3-apache`) termine sin errores.

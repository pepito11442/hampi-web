# Seguridad · Hampi

Pruebas de seguridad del curso (Semana 14). Plan completo: [PLAN_PRUEBAS_SEGURIDAD.md](PLAN_PRUEBAS_SEGURIDAD.md).

## Hallazgos (ordenados por severidad)

| ID | Hallazgo | Top 10 | Severidad | CVSS | Estado |
|---|---|---|---|---|---|
| [H-04](H-04-cuentas-demo.md) | Cuentas demo con contraseña pública (`socabaya2024`) y `SEED_DEMO=true` | A07 | **Crítica** | 9.8 | Abierto: lo corrige el autor en Render |
| [H-01](H-01-laravel-sin-soporte.md) | Laravel 11.57 sin soporte + 13 avisos en `composer audit` | A03 | Alta | 7.5 | Corregido · falta desplegar |
| [H-02](H-02-consulta-dni-sin-sesion.md) | `/api/consultar-dni` entrega nombres sin sesión | A01, A06 | Media | 5.3 | Corregido · falta desplegar |
| [H-03](H-03-sin-auditoria.md) | Sin auditoría de acciones sensibles | A09 | Media | — | Corregido · falta desplegar |
| [H-05](H-05-x-frame-options.md) | `X-Frame-Options: SAMEORIGIN` | A02 | Baja | 3.1 | Corregido · falta desplegar |
| [H-07](H-07-cors-abierto-api.md) | CORS `*` en `/api/*` (nuevo, visto en la evidencia) | A01, A02 | Baja | 3.1 | Corregido · falta desplegar |
| [H-06](H-06-csp-unsafe-inline.md) | CSP `style-src 'unsafe-inline'` | A02 | Baja | 2.6 | Corregido · falta desplegar |

Pruebas de regresión: `tests/Feature/HallazgosSeguridadTest.php` (14 pruebas). Suite completa: 79 pruebas, 427 aserciones, todas pasan.

## Pasos manuales del autor

### H-04: cerrar las cuentas demo (Render)
1. Render → hampi → *Environment*: `SEED_DEMO=false`. Cambiar lo mismo en `render.yaml`.
2. `/admin/equipo`: contraseña nueva (≥ 14 caracteres) para cada cuenta demo, o eliminarlas.
3. Contraseña nueva para el admin `enfermeria@hampi.pe` (enlace "olvidé mi clave" o `tinker` en la Shell de Render).
4. Quitar la tabla "Credenciales demo" del `README.md` público.
5. Comprobar que `socabaya2024` ya no entra en `/personal/login` ni en `/equipo/login`.

### Después de desplegar
- `curl -sI https://hampi-bqb8.onrender.com` → `x-frame-options: DENY`, `frame-ancestors 'none'`, `style-src` sin `'unsafe-inline'`.
- `curl -s -i https://hampi-bqb8.onrender.com/api/consultar-dni/72345678` → `403` y sin `access-control-allow-origin`.
- Revisar en el navegador `/admin/dashboard` y `/empleado/panel`: las barras tienen altura y la consola no muestra errores de CSP.
- Render → Logs: aparecen líneas JSON con `"accion":"…"` al generar códigos o cambiar estados.

## Por confirmar con ZAP o prueba manual

La auditoría SAST con la skill de Cloudflare (sección 8, paso 0) **todavía no se ejecutó**: instalar la skill desde Claude Code necesita permiso explícito del autor. Cuando se ejecute, los hallazgos `needs_validation` se listarán aquí.

| Origen | Hallazgo | Cómo confirmarlo |
|---|---|---|
| — | (pendiente de las corridas de la skill) | — |

## Evidencia

| Archivo | Contenido |
|---|---|
| [evidencia/composer-audit-antes.txt](evidencia/composer-audit-antes.txt) | 13 avisos con Laravel 11.57 |
| [evidencia/composer-audit-despues.txt](evidencia/composer-audit-despues.txt) | 0 avisos con Laravel 12.73; `npm audit` y `php artisan test` |
| [evidencia/cabeceras-produccion.txt](evidencia/cabeceras-produccion.txt) | Cabeceras de Render antes del despliegue (CP-12, CP-14) |
| [evidencia/consultar-dni-produccion.txt](evidencia/consultar-dni-produccion.txt) | Respuesta de `/api/consultar-dni` sin sesión, con el nombre tapado |

## Observaciones sin hallazgo
- **CP-12 (cookies)**: `hampi-session` ya sale con `Secure; HttpOnly; SameSite=Lax`. `XSRF-TOKEN` sin `HttpOnly` es lo normal en Laravel.
- **Falsos positivos de Semgrep esperados**: `{!! !!}` en `components/icon.blade.php` (SVG fijos) y `partials/speculation.blade.php` (JSON generado por la app).
- **`trustProxies(at: '*')`** en `bootstrap/app.php`: en Render el tráfico siempre pasa por su proxy, así que es aceptable, pero confía en `X-Forwarded-For` para la IP de los límites por IP y de la auditoría. Revisar si Render publica rangos fijos de proxy.

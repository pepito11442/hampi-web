# Plan de pruebas de seguridad · Hampi

> Curso Programación Web · Semana 14 (Principios de pruebas de seguridad) · Mg. Victor Cornejo.
> Metodología: **Planificar → Ejecutar → Documentar**, con OWASP Top 10:2025, OWASP WSTG y CVSS v3.1.
> Este archivo es también la guía de trabajo para Claude Code (ver sección 8).

---

## 1. Alcance y autorización

- **Sistema propio**: Hampi lo desarrolló el autor del proyecto; esta línea es la autorización escrita (Ley N.° 30096).
- **En alcance**: `https://hampi-bqb8.onrender.com` + su base MySQL de Aiven, y el código del repositorio.
- **Fuera de alcance**: plataformas de terceros (Render, Aiven, Brevo, RENIEC/apis.net.pe).

**Precauciones antes de atacar producción**

1. Respaldo de Aiven antes de cada escaneo activo (Aiven → Backups, o `mysqldump` con el CA).
2. Probar cuando nadie use el portal; Render Free tiene 0.1 CPU.
3. Excluir del escaneo activo: `/equipo/olvide-mi-clave` (gasta correos de Brevo), `/api/consultar-dni` (cuota RENIEC), los `DELETE` de `/admin`, los logout.
4. ZAP con 1–2 hilos y 200 ms de retraso.
5. Al terminar: limpiar registros basura o restaurar el respaldo.
6. Si hay pacientes reales, tapar DNI y nombres en las capturas.

## 2. Superficie de ataque

| Endpoint | Parámetro | Vector | Top 10 |
|---|---|---|---|
| `POST /ingreso-dni` | dni, codigo | Fuerza bruta del código; SQLi | A07, A05 |
| `POST /crear-codigo` | dni, nombres, apellidos, codigo | Apropiarse de ficha ajena; XSS almacenado; códigos débiles | A01, A05, A07 |
| `GET /api/consultar-dni/{dni}` | dni | Enumerar nombres por DNI | A01, A06 |
| `/mi-panel/citas/{cita}/cancelar`, `/calendario.ics` | cita | IDOR entre ciudadanos | A01 |
| `GET /mi-panel/disponibilidad` | medico, fecha | Entradas fuera de rango, SQLi | A05, A10 |
| `POST /mi-panel/citas` | medico, fecha, hora | Fuera de turno, doble reserva, >3 citas | A06 |
| `/empleado/citas/{cita}/estado`, `/codigo` | cita | IDOR entre médicos | A01 |
| `/admin/*` | sesión de médico/enfermero | Escalada de privilegios | A01 |
| `POST /empleado/perfil` | biografia, foto_perfil | XSS en `/medicos-hoy`; archivo no imagen | A05, A08 |
| `POST /admin/campanas` | título, descripción | XSS en `/campanas-preventivas` | A05 |
| `/personal/login`, `/equipo/login` | usuario, contraseña | Fuerza bruta; enumeración | A07 |
| `/equipo/olvide-mi-clave`, `/equipo/restablecer` | identificador, token | Enumeración; reuso de token | A07 |
| Todas las respuestas | cabeceras | CSP, HSTS, X-Frame-Options, nosniff | A02 |
| `Set-Cookie` | sesión | Sin HttpOnly/Secure/SameSite | A07 |
| `composer.lock`, `package-lock.json` | dependencias | CVE conocidos; Laravel sin soporte | A03 |
| Páginas de error | cualquiera | Traza con `APP_DEBUG=true` | A02, A10 |

## 3. Casos de prueba

Usuarios de prueba: ciudadanos A y B, médicos 1 y 2, un enfermero, un admin.
Resultado esperado = comportamiento seguro; cualquier otra cosa es un hallazgo.

| ID | WSTG · Top 10 | Prueba | Resultado esperado | Resultado |
|---|---|---|---|---|
| CP-01 | INPV-05 · A05 | `' OR '1'='1` y `1 UNION SELECT email,password FROM users--` en `/ingreso-dni`, `/crear-codigo`, `/mi-panel/disponibilidad` (con ZAP/curl) | 422 o redirección con mensaje; nunca datos ni error SQL | Pendiente |
| CP-02 | INPV-02 · A05 | `<script>alert(document.cookie)</script>` y `<img src=x onerror=alert(1)>` en biografía, campaña y nombre | Texto escapado; nada se ejecuta | Pendiente |
| CP-03 | ATHZ-04 · A01 | Ciudadano A abre `.ics` y cancela la cita de B por ID | 404; cita de B intacta | Pendiente |
| CP-04 | ATHZ-04 · A01 | Médico 1 cambia estado / genera código de cita del médico 2 | 404; sin cambios | Pendiente |
| CP-05 | ATHZ-02 · A01 | Médico/enfermero abre `/admin/dashboard` y envía formularios de `/admin/equipo` | 403 o redirección | Pendiente |
| CP-06 | ATHN-03 · A07 | 6 códigos incorrectos en `/ingreso-dni` y luego el correcto | Bloqueo 5 min tras el 5.º | Pendiente |
| CP-07 | ATHN-03 · A07 | 10 intentos en `/personal/login` y `/equipo/login` | 429 desde el 6.º por minuto | Pendiente |
| CP-08 | IDNT-04 · A07 | Logins y recuperación con usuario existente vs inexistente | Mismo mensaje y código HTTP | Pendiente |
| CP-09 | BUSL-01 · A01 | Crear código para un DNI con ficha creada por un médico | Rechazo; ficha sin cambios | Pendiente |
| CP-10 | BUSL-01 · A06 | Reservar fuera de turno, bloque ocupado, fecha pasada, +31 días, 4.ª cita | Todo rechazado | Pendiente |
| CP-11 | BUSL-08 · A08 | Subir `.php` renombrado, SVG con script, archivo de 10 MB | Rechazo; solo imágenes | Pendiente |
| CP-12 | SESS-02 · A07 | Revisar `Set-Cookie` en Render | HttpOnly, Secure, SameSite=Lax | Pendiente |
| CP-13 | SESS-06 · A07 | Reusar cookie tras logout; `GET /cerrar-sesion-ciudadano` | Sesión vieja inválida; 405 | Pendiente |
| CP-14 | CONF-07/ERRH-01 · A02, A10 | Revisar cabeceras; forzar error (ruta/ID inválido) | Cabeceras presentes; sin traza | Pendiente |
| CP-15 | ATHZ-04 · A01 | `/api/consultar-dni/{dni}` 20 veces sin sesión | Decidir política; mínimo 429 tras 10/min | Pendiente |

## 4. Herramientas

| Método | Herramienta | Uso en Hampi |
|---|---|---|
| SAST | [Cloudflare security-audit skill](https://github.com/cloudflare/security-audit-skill) (2–3 corridas) | Auditoría de código con verificación independiente de cada hallazgo |
| SAST | Semgrep (`--config p/php --config p/laravel`) + grep | `{!!`, `DB::raw`, `whereRaw`, rutas sin `auth`/`role` |
| SCA | `composer audit`, `npm audit`, Dependabot | CVE y versión de Laravel |
| DAST | OWASP ZAP: spider → pasivo → activo → reporte HTML | Un contexto por rol (ciudadano, médico, admin) |
| Manual | Navegador + ZAP proxy, 2 usuarios por rol | IDOR, roles, lógica de negocio |
| Regresión | `php artisan test` | Cada hallazgo corregido → prueba PHPUnit |

## 5. Brechas ya confirmadas por revisión de código (17-11-2026)

| # | Brecha | Top 10 | Severidad | CVSS (estimado) |
|---|---|---|---|---|
| B-1 | Sigue en **Laravel 11.57** (sin parches desde mar-2026). El commit "Laravel 12" (`3ee5c11`) solo tocó `.claude/settings.local.json` | A03 | Alta | 7.5 (verificar con `composer audit`) |
| B-2 | `/api/consultar-dni/{dni}` devuelve nombres de cualquier DNI sin sesión (10/min) | A01, A06 | Media | 5.3 `AV:N/AC:L/PR:N/UI:N/S:U/C:L/I:N/A:N` |
| B-3 | Sin auditoría de acciones sensibles (códigos generados, contraseñas cambiadas, usuarios borrados) | A09 | Media | — (no aplica CVSS directo) |
| B-4 | Cuentas demo `socabaya2024` y `SEED_DEMO=true` en Render | A07 | Crítica (repo público con la contraseña) | 9.8 `AV:N/AC:L/PR:N/UI:N/S:U/C:H/I:H/A:H` si quedan |
| B-5 | `X-Frame-Options: SAMEORIGIN` (el curso recomienda `DENY`) | A02 | Baja | 3.1 |
| B-6 | CSP con `style-src 'unsafe-inline'` (barras del gráfico admin) | A02 | Baja | 2.6 |
| B-7 | CORS `Access-Control-Allow-Origin: *` en `/api/*` (sin `config/cors.php`); visto en la evidencia de B-2 | A01, A02 | Baja | 3.1 `AV:N/AC:H/PR:N/UI:R/S:U/C:L/I:N/A:N` |

Estado de cada brecha y enlace a su documento H-XX: [README.md](README.md).

**Falsos positivos esperados en Semgrep**: `{!! !!}` en `components/icon.blade.php` (SVG fijos) y `partials/speculation.blade.php` (JSON generado por la app). No reciben texto del usuario.

## 6. Fases

1. **Planificar (día 1)**: alcance, respaldo, usuarios de prueba.
2. **Corregir B-1 (día 1)**: `composer require laravel/framework:^12.0 -W` → `php artisan test` → commit de `composer.json` y `composer.lock`.
3. **SAST + SCA (día 2)**: lista de alertas + falsos positivos justificados.
4. **DAST (día 3)**: ZAP sobre Render con precauciones; exportar informe HTML.
5. **Manual (días 4–5)**: CP-03 a CP-15 con capturas.
6. **Documentar, corregir, re-probar (día 6)**: un documento por hallazgo, orden por CVSS, prueba PHPUnit por corrección.

## 7. Plantilla de hallazgo (6 campos del curso)

```markdown
# H-XX · <Título>

| Campo | Detalle |
|---|---|
| Categoría | AXX:2025 · WSTG-XXXX-NN |
| Severidad | <Crítica/Alta/Media/Baja> · <puntaje> · `<vector CVSS v3.1>` |
| Estado | Abierto / Corregido / Verificado |

## Pasos para reproducir
1. ...

## Evidencia
<captura de petición y respuesta (ZAP), salida de comando o línea de código>

## Impacto
Un atacante podría ...

## Remediación
<cambio concreto en código o configuración, con el diff>

## Verificación
<caso CP-XX repetido + prueba PHPUnit que lo cubre>
```

Escala CVSS: Crítica 9.0–10 · Alta 7.0–8.9 · Media 4.0–6.9 · Baja 0.1–3.9. Calculadora: https://www.first.org/cvss/calculator/3.1

---

## 8. Instrucciones para Claude Code

0. **Auditoría SAST con la skill de Cloudflare** (antes de corregir nada):
   - Instalar: `npx skills add https://github.com/cloudflare/security-audit-skill --skill security-audit`
   - Ejecutar "security audit this codebase" **2 o 3 veces** (las corridas suman hallazgos).
   - Los resultados quedan en `~/security-audit-skill/hampi-web/run-<N>/`. Copiar `REPORT.md`, `FINDINGS-DETAIL.md` y `NEEDS-VALIDATION.md` a `docs/seguridad/auditoria-skill/`.
   - Cada hallazgo `confirmed` que no esté en B-1…B-6 se agrega a la sección 5 como B-7, B-8… y recibe su propio documento H-XX con la plantilla de la sección 7.
   - Los `needs_validation` se listan en `docs/seguridad/README.md` como "por confirmar con ZAP o prueba manual" (los confirma el autor).
   - Las notas de endurecimiento (hardening) se registran con severidad Baja.
1. Crear `docs/seguridad/` con un archivo por hallazgo: `H-01-laravel-sin-soporte.md` … `H-06-csp-unsafe-inline.md`, usando la plantilla de la sección 7 y los datos de la sección 5.
   - **Evidencia real**: ejecutar y pegar la salida de `composer audit`, `npm audit`, `grep -n "laravel/framework" composer.json`, `curl -sI https://hampi-bqb8.onrender.com` (cabeceras) y `curl -s https://hampi-bqb8.onrender.com/api/consultar-dni/72345678` (una sola vez).
   - Recalcular cada CVSS con el vector y justificarlo en una línea.
2. Corregir en este orden (uno por commit, con prueba PHPUnit):
   - **B-1**: actualizar a Laravel 12 de verdad; `php artisan test` debe pasar.
   - **B-2**: exigir un token de un solo uso emitido por `/crear-codigo` (sesión) para `/api/consultar-dni`; bajar el límite a 3/min por IP; registrar cada consulta en el log.
   - **B-3**: registrar en log (canal `stderr`) quién generó códigos, cambió contraseñas, borró usuarios o cambió estados de cita: usuario, acción, ID afectado, IP, fecha.
   - **B-5**: `X-Frame-Options: DENY` y `frame-ancestors 'none'` en la CSP.
   - **B-6**: quitar `'unsafe-inline'` de `style-src`; reemplazar los `style="height:…%"` del gráfico por clases o un atributo `data-` + JS con nonce.
   - **B-4**: no se corrige en código: dejar en el README los pasos (cambiar `SEED_DEMO=false`, rotar contraseñas demo).
3. Al terminar cada corrección: actualizar en su documento el campo **Estado** a "Corregido" y la sección **Verificación**.
4. No ejecutar escaneos activos ni fuerza bruta contra producción desde Claude Code; eso lo hace el autor con ZAP siguiendo la sección 1.
5. Crear `docs/seguridad/README.md` con el índice de hallazgos ordenado por severidad y su estado.

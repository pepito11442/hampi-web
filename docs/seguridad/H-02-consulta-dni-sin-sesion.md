# H-02 · `/api/consultar-dni` entrega nombres de cualquier DNI sin sesión

| Campo | Detalle |
|---|---|
| Categoría | A01:2025 Broken Access Control, A06:2025 Insecure Design · WSTG-ATHZ-02 |
| Severidad | Media · 5.3 · `CVSS:3.1/AV:N/AC:L/PR:N/UI:N/S:U/C:L/I:N/A:N` |
| Estado | Corregido (falta desplegar en Render) |

**Por qué 5.3**: se explota por internet, sin cuenta ni interacción; expone nombres y apellidos (confidencialidad baja: dato personal, no de salud) y no modifica nada.

## Pasos para reproducir
1. Sin cookies: `curl -s https://hampi-bqb8.onrender.com/api/consultar-dni/72345678`.
2. La respuesta trae nombres y apellidos reales del padrón.
3. Cambiar el DNI y repetir: 10 consultas por minuto por IP (`x-ratelimit-limit: 10`), ~14 400 al día.

## Evidencia
`evidencia/consultar-dni-produccion.txt` (una sola consulta, nombre tapado):
```
HTTP/2 200
x-ratelimit-limit: 10
{"success":true,"nombres":"J*****","apellidos":"P***** T*****","origen":"Padrón RENIEC Oficial"}
```
Código antes: `routes/web.php` → `Route::get('/api/consultar-dni/{dni}', …)->middleware('throttle:10,1');` sin ninguna otra condición.

## Impacto
Un atacante podría recorrer rangos de DNI y armar una base de nombres con el token RENIEC de la microred (gastando su cuota), o confirmar el nombre de una persona concreta antes de un fraude o suplantación.

## Remediación
- `app/Support/TokenReniec.php` (nuevo): token aleatorio de 40 caracteres emitido al abrir `GET /crear-codigo`; se guarda en la sesión y en la caché del servidor (15 min).
- `HampiController::consultarDniReniec`: exige la cabecera `X-Reniec-Token`, la compara con la sesión (`hash_equals`) y la consume con `Cache::pull`. Como la sesión vive en una cookie, la marca en caché evita "revivir" el token reenviando una cookie vieja. Sin token válido → `403`.
- `routes/web.php`: `throttle:10,1` → `throttle:3,1`.
- Cada consulta (permitida o no) se registra en el canal `auditoria` con el DNI oculto (`72****78`), IP y fecha.
- `registro.blade.php` + `resources/js/app.js`: el formulario envía el token una vez; si la persona corrige el DNI, escribe sus nombres a mano.

```php
$tokenRecibido = (string) $request->header('X-Reniec-Token', '');
$tokenSesion = (string) $request->session()->pull(TokenReniec::CLAVE_SESION, '');
$valido = $tokenRecibido !== ''
    && hash_equals($tokenSesion, $tokenRecibido)
    && TokenReniec::consumir($tokenRecibido);
```

**Riesgo residual**: quien automatice `GET /crear-codigo` + consulta sigue obteniendo 3 nombres por minuto por IP. Si se quiere cerrar del todo, quitar el autocompletado y pedir los nombres siempre a mano.

## Verificación
PHPUnit (`tests/Feature/HallazgosSeguridadTest.php`):
- `test_h02_consultar_dni_sin_token_es_rechazado` → 403 y no se llama a RENIEC.
- `test_h02_el_token_del_formulario_sirve_una_sola_vez` → 1.ª consulta 200, 2.ª 403.
- `test_h02_un_token_ya_usado_no_revive_reenviando_la_sesion_vieja` → 403.
- `test_h02_maximo_tres_consultas_por_minuto` → la 4.ª da 429.
- `test_h02_cada_consulta_queda_registrada_con_dni_oculto`.

Tras desplegar: repetir el `curl` del paso 1 (esperado `403`) y el caso CP-15 del plan.

## Actualización (19/11/2026): riesgo residual cerrado

Se quitó el registro en línea ("Crea tu código"). Ya no existen `GET/POST /crear-codigo`, `/api/consultar-dni/{dni}` ni `app/Support/TokenReniec.php`. El autocompletado con RENIEC se movió a la ventanilla de admisión: `GET /admin/ciudadanos/reniec/{dni}` exige sesión con rol admin o admisión, tiene un límite de 20 consultas por minuto y queda en la auditoría. El código de acceso solo lo entrega la posta (ventanilla o médico) después de ver el DNI físico. Prueba de regresión: `test_h02_la_consulta_reniec_ya_no_existe`.

# H-03 · Sin registro de auditoría de acciones sensibles

| Campo | Detalle |
|---|---|
| Categoría | A09:2025 Security Logging and Alerting Failures · WSTG-CONF-02 (registro) |
| Severidad | Media · sin CVSS (no es una vulnerabilidad explotable por sí misma; agrava a las demás) |
| Estado | Corregido (falta desplegar en Render) |

## Pasos para reproducir
1. Como admin, generar un código de acceso en ventanilla, cambiar la contraseña de un médico y eliminarlo.
2. Como médico, cancelar una cita.
3. Revisar Render → Logs: no aparece ninguna línea de esas acciones (solo hay avisos de fuerza bruta en los logins).

## Evidencia
Antes de la corrección, `grep -rn "Log::" app/Http/Controllers` solo devolvía avisos de fuerza bruta (`AuthController`, `EmpleadoController`, `CiudadanoController::login`) y un error de correo. `Admin\CiudadanoController::codigo`, `Admin\EquipoController::update/destroy`, `EmpleadoCitaController::nuevoCodigo/actualizarEstado` y `RecuperarClaveController::update` no registraban nada. Además `LOG_LEVEL=warning` en Render descarta cualquier `Log::info`.

## Impacto
Si una cuenta del personal se ve comprometida (por ejemplo, con H-04), el atacante podría generar códigos para entrar como cualquier paciente, borrar cuentas o cambiar contraseñas sin dejar rastro. No habría forma de saber qué pasó ni a quién avisar.

## Remediación
- `config/logging.php`: canal `auditoria` → `php://stderr`, JSON, nivel `info` fijo (no depende de `LOG_LEVEL`).
- `app/Support/Auditoria.php`: `Auditoria::registrar($accion, $detalle)` agrega usuario, rol, ciudadano en sesión, IP y fecha ISO 8601. Nunca guarda códigos ni contraseñas.
- Acciones registradas:

| Acción | Dónde |
|---|---|
| `codigo.generado` | ventanilla (`Admin\CiudadanoController`), médico (`EmpleadoCitaController::nuevoCodigo` y paciente nuevo en `store`) |
| `contrasena.cambiada` | admin (`EquipoController::update`), enlace de recuperación (`RecuperarClaveController::update`) |
| `usuario.creado`, `usuario.eliminado`, `usuario.permisos_cambiados` | `EquipoController` |
| `usuario.correo_cambiado` | `EmpleadoController::updatePerfil` (el correo es la vía de recuperación) |
| `cita.estado_cambiado` | médico (`actualizarEstado`) y ciudadano (`cancelarCita`) |
| `reniec.consulta` | `HampiController::consultarDniReniec` (ver H-02) |

Ejemplo de línea:
```json
{"message":"usuario.eliminado","context":{"accion":"usuario.eliminado","usuario_id":1,"rol":"admin","ciudadano_id":null,"ip":"190.x.x.x","fecha":"2026-11-17T10:50:02-05:00","usuario_afectado":7},"level_name":"INFO","channel":"production"}
```

**Límite**: Render Free guarda los logs pocos días. Para conservarlos habría que enviarlos a un servicio externo (Better Stack, Papertrail) o a una tabla `auditoria`.

## Verificación
PHPUnit (`HallazgosSeguridadTest`):
- `test_h03_se_audita_el_codigo_generado_en_ventanilla` (y que el código no aparece en el registro).
- `test_h03_se_audita_el_cambio_de_contrasena_y_el_borrado_de_usuarios`.
- `test_h03_se_audita_el_cambio_de_estado_de_una_cita`.

Tras desplegar: repetir los pasos 1–3 y buscar `usuario.eliminado` en Render → Logs.

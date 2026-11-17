# H-04 · Cuentas demo con contraseña pública en producción

| Campo | Detalle |
|---|---|
| Categoría | A07:2025 Authentication Failures · WSTG-ATHN-02 (credenciales por defecto) |
| Severidad | Crítica · 9.8 · `CVSS:3.1/AV:N/AC:L/PR:N/UI:N/S:U/C:H/I:H/A:H` (si las cuentas existen en Render) |
| Estado | Abierto: se corrige en Render, no en código (lo hace el autor) |

**Por qué 9.8**: el repositorio `github.com/pepito11442/hampi-web` es público (HTTP 200 sin sesión) y el `README.md` publica el correo del admin y la contraseña `socabaya2024`. Basta con leerlo (AC:L, PR:N). Con la cuenta admin se ven y modifican datos de pacientes y personal (C:H, I:H) y se pueden borrar cuentas y turnos (A:H).
El plan estimaba 8.1 (`AC:H`); se sube porque la contraseña no hay que adivinarla. Mientras la base tenga solo datos de prueba el daño real es menor, pero el puntaje base no cambia.

## Pasos para reproducir
1. Abrir `README.md` → "Credenciales demo": admin `enfermeria@hampi.pe`, médicos y enfermeros por DNI, todos con `socabaya2024`.
2. `render.yaml` → `SEED_DEMO: "true"`; el contenedor siembra esas cuentas si la base está vacía (`database/seeders/DatabaseSeeder.php`).
3. **(Lo confirma el autor, no Claude Code)**: un solo intento en `/personal/login` con esas credenciales.

## Evidencia
```
README.md:705:| Lic. Carmen Alfaro | `enfermeria@hampi.pe` | `socabaya2024` | `/personal/login` |
README.md:711-714: 4 cuentas del personal con `socabaya2024`
render.yaml:80-81:  - key: SEED_DEMO
                      value: "true"          # cambia a "false" cuando cargues datos reales
database/seeders/DatabaseSeeder.php:20: $passwordDemo = Hash::make('socabaya2024');
```
Queda pendiente la captura del paso 3.

## Impacto
Cualquiera que lea el repositorio podría entrar como administrador: generar códigos y abrir el panel de cualquier paciente, ver y cancelar citas, cambiar contraseñas del personal y borrar cuentas, todo sin dejar rastro antes de H-03.

## Remediación (en Render, sin cambios de código)
1. Render → hampi → *Environment*: `SEED_DEMO=false` → *Save* (y cambiar el mismo valor en `render.yaml` para que no vuelva).
2. Entrar como admin y, en `/admin/equipo`, poner una contraseña nueva y larga (≥ 14 caracteres, de un gestor de contraseñas) a cada cuenta demo, o eliminar las que no se usen.
3. Cambiar también la contraseña del admin (`enfermeria@hampi.pe`). Si no hay pantalla para eso, usar el enlace "olvidé mi clave" o `php artisan tinker` en la Shell de Render: `User::where('email','enfermeria@hampi.pe')->first()->update(['password'=>Hash::make('<nueva>')]);`
4. En el `README.md` público, reemplazar la tabla por: "Las credenciales demo solo funcionan en local (`php artisan db:seed`)".
5. Antes de cargar pacientes reales: borrar las cuentas demo que sobren.

## Verificación
- Intentar entrar en `/personal/login` y `/equipo/login` con `socabaya2024` → "credenciales incorrectas".
- Render → Logs: deben aparecer líneas `contrasena.cambiada` (H-03) por cada cuenta rotada.
- No aplica prueba PHPUnit: es configuración del despliegue.

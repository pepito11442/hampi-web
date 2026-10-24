# Hampi · Sistema visual y de interfaz

> Documento de diseño de la demo académica **Hampi · Microred Socabaya**.
> Dirección visual: **“El parte de la posta”**. Fecha de esta revisión: **21 de octubre de 2026**.
> Implementación: Laravel 11 + Blade + Tailwind CSS 4 + Vite; tokens y componentes en `resources/css/app.css`.

---

## 1. La idea

El portal no se presenta como una landing de clínica, sino como **la hoja diaria de una posta de salud**: un parte de atención con folio, fecha, filas de registro, sellos y una fotografía prendida con cinta. Es un mundo cívico-operativo: papel hueso, tinta verde, señal naranja y sellos rojos.

Esa hoja se adapta a tres contextos:

| Superficie | Metáfora | Trabajo principal |
| --- | --- | --- |
| Portal público | La hoja clavada en la pared | Entender qué hay hoy y entrar con DNI. |
| Paneles de personal y administración | La copia de escritorio | Consultar, registrar y operar sin ambigüedad. |
| Panel ciudadano | La ficha personal | Ver próximas citas e historial con claridad. |

La portada y los paneles tienen el mismo peso: la primera debe generar confianza y acceso; los segundos deben demostrar que la demo es operable.

### Principios

1. **Hoy primero.** Turnos, stock de vacunas, próxima campaña y citas se leen antes que cualquier mensaje de marca.
2. **El DNI es la puerta, no el trámite.** El formulario de acceso vive dentro de la hoja principal, en el primer viewport.
3. **Papel compartido, densidades distintas.** Público y operación usan el mismo mundo; la operación reduce decoración y aumenta precisión.
4. **La especificidad local es confianza.** Nombres de postas, fechas, sedes y lenguaje de Socabaya antes que genéricos de salud.
5. **La demo no miente.** Datos simulados, sello **DEMO ACADÉMICA** y ningún claim institucional no verificable.

---

## 2. Tipografía

| Rol | Fuente | Uso | Pesos |
| --- | --- | --- | --- |
| Display | **Archivo** | Titulares, nombres, números grandes, títulos de sección | 500, 600, 700, 800 |
| Texto / UI | **Instrument Sans** | Párrafos, botones, formularios, navegación | 400, 500, 600, 700 |
| Dato | **Martian Mono** | Fechas, folios, horas, estados, etiquetas, códigos | 400, 500, 600 |

Decisiones:

- **Sin serif.** La autoridad viene del documento y la retícula, no de una tipografía editorial clásica.
- **Archivo** da titulares compactos y firmes, con tracking negativo (`-0.025em` a `-0.06em`).
- **Martian Mono** se reserva para información verificable: fecha, folio, hora, DNI, estado, contador. No se usa en párrafos largos.
- Las fuentes están **autoalojadas** en `public/fonts/hampi/` (WOFF2). No hay dependencia de Bunny Fonts ni de Google Fonts en tiempo de ejecución.

### Escala base

- Cuerpo: `16px` (`text-base`), interlineado `1.5–1.7`.
- Etiquetas mono: `10px`, mayúsculas, tracking `0.08em–0.12em`.
- Texto auxiliar: `12px–14px`.
- Titular de portada: `clamp` visual vía Tailwind, desde 40px móvil hasta 80px escritorio.
- Números de estadística: 36px (`stat-number`), con Archivo.

Las etiquetas pequeñas nunca son el único vehículo de una acción crítica: siempre hay texto, icono o estado acompañante. El calendario usa 10–13px para que siga siendo legible en escritorio y móvil.

---

## 3. Color

El color pertenece a un mundo de papelería de salud, no a un dashboard SaaS.

| Token | Hex | Función |
| --- | --- | --- |
| `paper-50` | `#fbfaf6` | Hoja principal, tarjetas y campos. |
| `paper-100` | `#f4f2ea` | Fondo único del portal. |
| `paper-200` | `#e8e5d9` | Hover, zonas secundarias. |
| `ink-950` | `#0b0f0c` | Tinta principal, cabeceras y texto de máximo contraste. |
| `ink-800` | `#202821` | Texto de cuerpo intenso. |
| `ink-600` | `#37423a` | Texto secundario. |
| `ink-300` | `#aab1a6` | Reglas y bordes. |
| `brand-700` | `#17522f` | Verde posta: acción primaria, enlaces. |
| `brand-900` | `#0d2f1d` | Panel editorial oscuro. |
| `signal-600` | `#c9471b` | Naranja de señal: subrayado activo, avisos. |
| `stamp-600` | `#b3261e` | Rojo sello: error, demo, acción destructiva. |
| `cobalt-700` | `#234a87` | Información administrativa y enlaces de apoyo. |
| `maize-400` | `#e7b32a` | Atención, cinta de foto, campañas. |

Reglas:

- **Nunca comunicar un estado solo con color.** Los badges llevan texto (“Disponible”, “Por agotarse”, “Agotado”, “Confirmada”) y, cuando aporta, icono.
- El fondo global es `bg-ledger` (papel hueso con retícula tenue). Las páginas internas montan un tablero `bg-ledger-carteles` con el componente `x-page-board`, que agrupa el hero y el contenido en dos hojas blancas separadas. La portada y los accesos conservan su tratamiento propio.
- El verde no es decorativo: identifica la acción principal y la pertenencia a la posta.
- El rojo se reserva para sello, error y acciones destructivas; no para “destacar” contenido.
- El naranja señala lo activo y lo que requiere atención; no reemplaza al rojo de error.

---

## 4. Retícula y superficies

### Estructura global

- `body` es una columna flexible con un fondo fijo `bg-ledger`.
- `container-page`: ancho máximo `80rem` (1280px), padding `16px → 24px → 32px`.
- Header sticky de 64px; barra de utilidades de 36px solo en escritorio (`lg`).
- Navegación principal tipo tabs en escritorio; menú desplegable y barra inferior de cinco accesos en móvil.
- El contenido público termina con un cierre tipo “ventanilla” en negro y el pie de página; el pie deja espacio para la barra inferior móvil.

### La hoja

`.sheet` es el contenedor de referencia: borde de 1px, fondo `paper-50`, sombra suave y radio de 2px. `.sheet-head` es una banda de tinta negra con datos monoespaciados (folio, fecha, sección). Las reglas internas usan `.sheet-rule` o `border-ink-200`.

`.sheet-doctor` es la variante de las páginas internas (fondo blanco, borde y sombra). El componente `x-page-board` coloca el hero y el contenido en dos `.sheet-doctor` sobre un tablero `bg-ledger-carteles`, con el nav administrativo entre ambas hojas cuando corresponde.

### Ritmo

- Secciones públicas: `py-10` a `py-16`.
- Paneles: `py-6` a `py-9`, con más densidad.
- Separación entre bloques: 24–32px.
- Las tarjetas no tienen radios grandes: 2px en hojas y sellos, 4–6px en controles. La geometría debe sentirse impresa, no “burbuja”.

### Breakpoints

Se usan los breakpoints de Tailwind:

- `< 640px`: móvil, una columna, barra inferior, formularios apilados.
- `640–767px`: móvil ancho / tablet pequeña.
- `768–1023px`: tablet, navegación aún en menú hamburguesa.
- `1024px+`: escritorio, navegación principal visible, utilidades en header.
- `1280px+`: contenedor completo; se mantiene la misma retícula.

Los paneles internos no muestran la barra inferior ciudadana.

---

## 5. Componentes

### `site-logo`
Cuadrado verde con “H” blanca y una pequeña esquina naranja; wordmark **Hampi** y bajada **Microred Socabaya**. Variante con badge “SOCABAYA” en escritorio. En el pie se reutiliza con subtítulo “Microred Socabaya · Arequipa”.

### `page-hero`
Cabecera editorial de páginas internas y públicas:
- eyebrow mono (“Administración”, “Agenda médica”, “Panel ciudadano”);
- `h1` Archivo;
- descripción breve;
- acciones a la derecha o debajo;
- sello icono decorativo;
- regla inferior.

Es la firma visual que mantiene unida la portada con los paneles sin repetir el hero de marketing.

### `page-board`
Tablero común de las páginas internas. Recibe el hero como slot y el contenido como slot por defecto; opcionalmente recibe el `admin-nav` en un slot intermedio. Renderiza:
- fondo `bg-ledger-carteles` con sombra y blur;
- hero dentro de `.sheet-doctor`;
- contenido dentro de otra `.sheet-doctor`;
- padding y separación uniformes.

Todas las pantallas internas (públicas, del personal y de administración) lo usan para que el hero y el contenido compartan la misma silueta. La portada y los accesos no lo usan.

### `auth-shell`
Marco de dos columnas en escritorio:
- panel verde oscuro con patrón de puntos, marca, titular y bullets de confianza;
- formulario en papel con `h1`, explicación, campos, errores y nota de demo.

En móvil queda solo el formulario para evitar duplicar el logo del header. Las pantallas de acceso muestran **Portal ciudadano** en el header, no “Acceso con DNI”.

### `stamp`
Sello de 2px en rojo, mayúsculas y rotación leve. Usos:
- **DEMO ACADÉMICA** en portada, footer y panel de acceso;
- estados de demostración;
- no usar como decoración vacía en cada tarjeta.

### `taped-photo`
Foto con marco de papel, sombra y cinta maize. Es el único gesto “manual” grande de la portada. La imagen es **de referencia**, no una fotografía oficial de la microred; el crédito está en el pie de la figura.

### `ledger-row`
Fila de libro para servicios y datos: número/índice, título, descripción, estado y enlace. En móvil se apila; en escritorio conserva la lectura horizontal.

### Controles
- `.btn`: altura mínima 44px, radio 6px, texto 14px semibold, icono opcional.
- `.btn-primary`: verde posta.
- `.btn-secondary`: papel con borde.
- `.btn-ghost`: acción terciaria.
- `.input`: altura mínima 44px, borde ink-300, foco con borde brand-600 y halo brand-100; `font-size: 16px` para evitar zoom involuntario en iOS.
- `.label`: mono 10px, mayúsculas, tracking amplio.
- `.badge`: 2px de radio, mono 10px, estado textual.
- `.filter-pill`: filtros del pizarrín, con `aria-pressed`.

### Tablas y calendarios
- `.data-table` con cabecera mono y filas separadas por reglas.
- Calendario mensual: retícula de 7 columnas, turnos con color + etiqueta textual, domingo “Sin atención”, día actual marcado con “HOY”.
- En móvil el calendario se convierte en una agenda vertical con fecha, turno, horario y horas.
- La leyenda de turnos es obligatoria; el color nunca es la única explicación.

### Navegación administrativa
`admin-nav` muestra tabs **Panel · Equipo de salud · Horarios · Campañas preventivas** con iconos Lucide. En móvil permite desplazamiento horizontal y mantiene `aria-current`.

### Estados vacíos
`empty-state` usa un icono en sello, título, explicación y una acción. Se usa en citas, vacunas y listas sin datos. No se rellena con datos falsos para “evitar el vacío”.

---

## 6. Superficies

### Portada pública
Primer viewport:
- hoja asimétrica con folio, fecha y sello **DEMO ACADÉMICA**;
- titular “La salud de tu barrio, hoy.”;
- formulario DNI integrado;
- tres filas de estado (médicos, vacunas, próxima campaña);
- foto con cinta;
- enlace al directorio.

Debajo: servicios en ledger, preguntas frecuentes y cierre tipo ventanilla. La acción de DNI está en el primer viewport a 390×844.

### Páginas públicas
- **Médicos de hoy:** rol por turno, estado, especialidad y posta; foto de perfil si el médico la cargó y, si no, iniciales; acceso a directorio.
- **Pizarrín de vacunas:** avisos con stock, grupo objetivo, requisitos y establecimiento; filtros por estado.
- **Campañas preventivas:** campaña destacada en ficha oscura, siguientes campañas en ledger y pasos de acceso.
- **Directorio de postas:** fichas con categoría, dirección, horario, teléfono y servicios; sin mapa embebido.

### Accesos
- **Administración:** correo + contraseña, tono operativo, nota de demo y control de intentos.
- **Personal de salud:** DNI + contraseña, bullets de horario/perfil, enlace de soporte.
- **Ciudadanía:** formulario de DNI en la portada, sin cuenta.

### Administración
- Dashboard con el parte del día, accesos y avisos.
- Equipo: tabs por rol, listado y formulario de alta/edición; acciones destructivas separadas.
- Horarios: selector de trabajador/mes/año, resumen de 150 h, calendario mensual y acciones de regenerar/eliminar.
- Campañas: publicación, estado y retiro, con fechas y lugar.

### Personal de salud
- Panel con resumen mensual, calendario, perfil editable y datos de la cuenta.
- Citas: agenda del médico, paciente, fecha, bloques de 30 minutos y estados.
- La edición de perfil y las acciones destructivas usan confirmaciones explícitas.

### Panel ciudadano
- Saludo personal, DNI y jurisdicción.
- Próxima cita en ficha destacada; si no hay, estado vacío **“No tienes citas próximas”**.
- Historial de citas y accesos rápidos.
- El acceso directo sin sesión redirige al formulario de la portada.

---

## 7. Movimiento

- **Entrada de página:** los bloques fuera del primer viewport aparecen con `data-reveal` (opacidad + 10px), 500ms, una sola vez.
- **Sello:** `stamp-in` únicamente en la portada; no hay animaciones infinitas.
- **Contadores:** `count-in` una vez.
- **Header:** sombra sutil al hacer scroll.
- **Hover:** cambios de color/borde y elevación de 2px en tarjetas; no se dispersan efectos por toda la página.
- **`prefers-reduced-motion: reduce`:** desactiva reveal, sello, contadores y transiciones; todo el contenido queda visible desde el inicio.
- El JS es progresivo: si falla, el CSS no oculta contenido porque `reveal-pending` solo se añade desde `app.js`. Además, `initReveal` muestra de inmediato lo que ya está en el viewport.

---

## 8. Accesibilidad

Objetivo: **WCAG 2.2 AA** en la demo, con verificación manual y automatizada.

### Decisiones implementadas

- Un `h1` por pantalla; títulos jerárquicos `h2`/`h3` para secciones y tarjetas.
- Skip link “Saltar al contenido principal” visible al foco, con destino `main#contenido` y `tabindex="-1"`.
- Foco visible: outline de 2px signal-600 con offset de 3px; los campos añaden un halo brand-100. No se elimina el outline sin reemplazo.
- Targets de al menos 44px en botones, navegación, barra móvil y controles.
- Formularios con `label` asociado, `aria-describedby` para ayudas, `role="alert"` para errores y `aria-live` en estados de RENIEC.
- Menú móvil con `aria-expanded`, `aria-controls`, foco al abrir, cierre con Escape y retorno de foco.
- Estados no dependientes solo del color; badges con texto.
- Imágenes con `alt` descriptivo; la foto decorativa de referencia se describe y se acredita.
- Navegación inferior móvil con etiquetas visibles, no solo iconos.
- Contraste revisado en texto, badges y estados; los tonos ink sobre paper cumplen AA en los tamaños usados.
- `prefers-reduced-motion` respetado en CSS y JS.
- Diseño sin dependencia de JavaScript para leer el portal público y el formulario de acceso (el JS solo mejora la consulta RENIEC y la disponibilidad de citas).

### Verificación realizada

- **axe-core 4.10.2** sobre 15 pantallas representativas: portada, páginas públicas, ambos logins, dashboard/equipo/horarios/campañas, panel del trabajador, citas y creación de cita. Resultado final: **0 violaciones** de las reglas WCAG 2.2 A/AA configuradas.
- Revisión manual de foco con teclado, skip link, menú móvil y Escape.
- Revisión de `prefers-reduced-motion`: sin elementos ocultos y transiciones reducidas.
- Revisión responsive en **320, 390, 768, 1024 y 1440 px** sin scroll horizontal.
- Revisión de formularios, estados vacíos, errores y login real con MySQL 8.4 y PHP 8.3.
- Sin errores de JavaScript en consola en las rutas capturadas.

La auditoría automatizada es una ayuda, no una certificación. Antes de publicar un servicio real habría que revisar contenido, privacidad, datos personales y flujos de accesibilidad con personas usuarias.

---

## 9. Voz y copy

- Español peruano, claro y directo.
- Términos locales: **posta**, **microred**, **pizarrín**, **rol de atención**, **campaña preventiva**.
- Sin paternalismo, sin promesas de resultados, sin “revolucionario” ni métricas inventadas.
- La demo se declara en la portada, el pie y el login: **DEMO ACADÉMICA · Datos simulados · No es un servicio oficial**.
- No se usan nombres de instituciones externas, leyes, certificaciones ni convenios que no estén verificados.
- Los textos de acción priorizan verbos concretos: “Entrar a mi panel”, “Ver el rol de hoy”, “Regenerar horario”, “Confirmar cita”.

---

## 10. Archivos y mantenimiento

| Archivo | Rol |
| --- | --- |
| `resources/css/app.css` | Tokens, fuentes, base, componentes, foco y motion. |
| `resources/js/app.js` | Menú, reveal, password toggle, filtros y disponibilidad de citas. |
| `resources/views/hampi/layout.blade.php` | Header, navegación, fondo único, footer y barra móvil. |
| `resources/views/hampi/login.blade.php` | Portada y formulario DNI. |
| `resources/views/hampi/partials/calendario.blade.php` | Calendario de escritorio y agenda móvil. |
| `resources/views/components/` | `site-logo`, `page-hero`, `page-board`, `auth-shell`, `empty-state`, `icon`. |
| `public/fonts/hampi/` | Fuentes WOFF2 autoalojadas. |
| `public/images/comunidad-cuidado.*` | Foto de referencia de la portada, optimizada. |
| `DESIGN.md` | Este documento. |
| `PRODUCT.md` | Verdad de producto y restricciones. |

### Para añadir una pantalla

1. Definir si es **persuadir** (público) u **operar** (panel).
2. Reutilizar `x-page-board`, `x-page-hero`, `.sheet`, `.mono-label`, `.badge`, `.btn` y `.input`.
3. Usar una sola `h1`; el resto de la jerarquía en `h2`/`h3`.
4. Incluir estados vacíos, error y carga; no inventar datos.
5. Verificar 320px, teclado, foco, zoom 200%, contraste y reduced motion.
6. Actualizar `DESIGN.md` si se crea un patrón nuevo.
7. Ejecutar `npm run build` y `php artisan test`.

### Evitar

- El hero genérico de clínica con stock de bata y estetoscopio.
- Gradientes teal suaves, cards iguales de colores o “eyebrows” decorativos sobre cada título.
- Esquinas muy redondeadas, glassmorphism y sombras difusas sin relación con el papel.
- Mostrar datos simulados como oficiales.
- Añadir una segunda tipografía display, una paleta paralela o un segundo fondo de página.
- Ocultar contenido crítico detrás de JS o de animaciones de entrada.

---

## 11. Créditos de imágenes y fuentes

### Fotografía

- **Título:** *Young person's hand holding an old person's hand*.
- **Autor:** Kampus Production.
- **Fuente:** Pexels, foto `7551677`.
- **URL:** <https://www.pexels.com/photo/young-persons-hand-holding-an-old-persons-hand-7551677/>.
- **Licencia:** [Pexels License](https://www.pexels.com/license/). Uso gratuito; se otorga crédito de forma voluntaria.
- **Transformación:** recorte a 1200×900, ligera optimización y exportación a JPG/WebP para la demo.
- **Uso:** imagen de referencia de la portada. **No** es una fotografía oficial de la Microred Socabaya ni de una posta real.

### Tipografías

- **Archivo** — SIL Open Font License 1.1.
- **Instrument Sans** — SIL Open Font License 1.1.
- **Martian Mono** — SIL Open Font License 1.1.

Se sirven autoalojadas en `public/fonts/hampi/` para evitar peticiones a terceros y mantener la demo reproducible sin conexión. Los textos completos de las licencias están en `public/fonts/hampi/licenses/`.

### Stack y recursos

- Laravel, Tailwind CSS, Vite, Lucide (iconos SVG).
- Datos, establecimientos, campañas y credenciales son ficticios y viven en seeders y base de datos de demostración.

---

## 12. Verificación rápida

```bash
# Backend
php artisan test

# Assets y fuentes
npm ci
npm run build

# Servidor local (ejemplo)
php artisan serve
```

Checklist de demo:

- [ ] Portada a 390px: formulario DNI y filas de estado visibles sin desplazamiento horizontal.
- [ ] Login de administración y de personal; cierre de sesión.
- [ ] Panel ciudadano tras ingresar con DNI; estado vacío y cita de ejemplo.
- [ ] Calendario de horarios en escritorio y móvil.
- [ ] Citas: cambio de fecha, carga de bloques y confirmación.
- [ ] Teclado: skip link, menú móvil, foco visible, Escape.
- [ ] Reduced motion: todo el contenido visible.
- [ ] Footer: sello de demo, créditos y ausencia de claims institucionales.

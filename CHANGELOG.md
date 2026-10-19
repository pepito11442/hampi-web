# Changelog

Todos los cambios relevantes de este proyecto se registran aquí.

Formato inspirado en [Keep a Changelog](https://keepachangelog.com/es-ES/1.1.0/). Fechas en `AAAA-MM-DD`.

## [Sin publicar]

## [2026-10-19] — Rediseño visual del portal + datos reales

### Añadido

- Sistema de diseño Hampi (turquesa sanitaria + azul profundo) sobre Tailwind CSS 4 compilado con Vite: tokens de color, sombras, radios y componentes reutilizables (`.btn`, `.card`, `.input`, `.badge`, `.filter-pill`, ...).
- Componentes Blade `x-icon` (set de más de 50 iconos SVG locales, sin CDN), `x-badge`, `x-page-hero` y `x-empty-state`.
- Interacciones de interfaz: menú móvil accesible, sombra del encabezado al hacer scroll, animaciones de entrada con respeto por `prefers-reduced-motion`, mostrar/ocultar contraseña y filtros del pizarrín de vacunas.
- Imágenes locales para el hero y la sección comunitaria (`public/images/`, JPG + WebP) y favicon propio.
- Documentación del proyecto: `README.md`, `CONTRIBUTING.md` y este `CHANGELOG.md`.

### Cambiado

- Rediseño integral de las 8 vistas (portada, médicos, pizarrín, postas, panel ciudadano, login del personal, panel admin y error 429) con enfoque mobile-first, accesibilidad y componentes compartidos.
- Tailwind y los iconos pasan de CDN a un build propio con Vite (`npm run build`); el portal ya no depende de red para mostrarse correctamente.
- Los indicadores de la portada y las citas del panel ciudadano se alimentan de la base de datos (antes usaban contenido fijo/ficticio).
- El modelo `Cita` define conversión de `fecha_hora` y relaciones `posta`/`ciudadano` que simplifican las vistas.
- Dependencias del proyecto actualizadas/migradas a Laravel 11 en `composer.json` y `composer.lock`.

### Corregido

- El acceso con DNI ya no se bloquea si la consulta a RENIEC falla: se habilitan campos manuales como respaldo.
- Pluralización correcta de los indicadores (“1 posta” / “N postas”).
- El panel ciudadano verifica la sesión registrada antes de mostrar sus citas.

### Notas

- La consulta RENIEC se configura con `RENIEC_API_TOKEN` en `.env`; nunca se versiona.
- `public/build` está ignorado por git: tras clonar hay que ejecutar `npm install && npm run build`.

## Historial anterior (antes de este changelog)

- `a56222a` (2026-10-19) — feat: proyecto hampi completo con seguridad y panel administrativo.
- `6a891a5` (2026-10-11) — feat: integración de la API RENIEC para consulta de DNI y layout responsivo.
- `09c23f8` (2026-10-10) — commit inicial.

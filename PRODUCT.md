# Product

<!-- impeccable:product-schema 1 -->

## Platform

web

## Users

- **Vecinos y familias de Socabaya, Arequipa.** Llegan principalmente desde el celular, muchas veces sin cuenta y con una necesidad concreta: saber quién atiende hoy, si hay vacunas, dónde queda una posta o qué pasó con su cita. Pueden tener poca familiaridad con trámites digitales y necesitan leer y actuar sin ambigüedad.
- **Personal de salud (médicos, enfermeros y enfermeras).** Usa el portal para consultar su horario mensual, revisar su agenda de citas y mantener actualizado su perfil profesional. Trabaja en horarios de atención y necesita densidad, rapidez y estados claros.
- **Administración y admisión.** Publica el rol de médicos, el pizarrín de vacunas, el equipo, los horarios y las campañas preventivas. Necesita operar sin ambigüedad y con confirmaciones seguras en acciones destructivas.

La portada pública y los paneles internos tienen el mismo peso estratégico en esta demo: la primera debe generar confianza y acceso; los segundos deben demostrar que el sistema es operable.

## Product Purpose

Hampi conecta a los vecinos de Socabaya con la información y los servicios de su microred de salud: médicos de turno, disponibilidad de vacunas, campañas preventivas, establecimientos y citas asociadas al DNI. También da al personal de salud una herramienta para publicar y consultar esa información.

El éxito de esta demo académica es que una persona pueda entender qué ofrece la microred, encontrar el dato que necesita y entrar con su DNI en pocos pasos; y que el personal pueda completar una tarea administrativa sin sentirse perdido.

## Positioning

Hampi no es un portal genérico de clínica: es la puerta digital de una microred concreta de Socabaya. Su mecanismo distintivo es reunir el **tablón operativo del día** —quién atiende, qué vacunas hay, qué campaña viene— con un acceso ciudadano de baja fricción mediante DNI, y separar con claridad esa experiencia pública de la operación interna del personal de salud.

## Operating Context

- Aplicación Laravel + MySQL, pensada para ejecutarse en local, Docker o GitHub Codespaces.
- Demo académica funcional: los datos de médicos, vacunas, campañas, postas y citas son de demostración.
- El acceso ciudadano usa DNI; la consulta a RENIEC es opcional y depende de un token configurado en el entorno. Si no está disponible, existe un flujo de respaldo.
- El personal de salud ingresa con DNI y contraseña. La administración y admisión ingresan con correo y contraseña.
- El portal se usa principalmente en navegador web, con especial atención al móvil para la ciudadanía y al escritorio para la operación administrativa.
- La presentación principal es una demo para un profesor; el README es parte del entregable y debe explicar cómo levantarlo y verificarlo.

## Capabilities and Constraints

- Portal público: inicio, médicos de turno, pizarrín de vacunas, campañas preventivas y directorio de postas.
- Acceso ciudadano por DNI, panel con próximas citas e historial.
- Panel del personal: horario mensual, calendario de turnos, perfil profesional y agenda de citas del médico.
- Panel administrativo: rol de médicos, pizarrín de vacunas, equipo de salud, horarios mensuales y campañas preventivas.
- Roles: `admin`, `admision`, `doctor`, `enfermero`, `enfermera` y ciudadanía sin cuenta.
- No se deben inventar testimonios, convenios, métricas oficiales, precios, certificaciones ni claims de producción.
- El token real de RENIEC no se versiona ni se muestra en documentación, capturas o demo.
- La demo debe ser honesta sobre su carácter académico y sobre el origen simulado de los datos.
- Se conservan las rutas, roles, flujos y funcionalidad existentes salvo que un cambio de UX lo justifique y sea verificado.

## Brand Commitments

- Nombre de producto: **Hampi**.
- Contexto institucional: **Microred Socabaya**, Arequipa, Perú.
- Idioma: español peruano. Se conservan términos locales y del dominio: *pizarrín*, *posta*, *microred*, *rol de atención*, *campaña preventiva*.
- Tono: claro, humano, respetuoso y directo. Sin paternalismo, sin jerga administrativa innecesaria y sin hype comercial.
- La identidad Hampi y la referencia a la Microred Socabaya se mantienen juntas.

## Evidence on Hand

- Implementación Laravel completa en este repositorio, con rutas, controladores, vistas, migraciones y seeders.
- Datos de demostración en `database/seeders/DatabaseSeeder.php`.
- Documentación operativa y credenciales demo en `README.md`.
- Imagen de apoyo en `public/images/comunidad-cuidado.*`: fotografía de referencia de Kampus Production en Pexels (foto 7551677, licencia Pexels), recortada y optimizada para la demo. No se presenta como fotografía oficial de la microred. Créditos completos en `README.md` y `DESIGN.md`.
- No hay testimonios, casos de éxito, métricas oficiales ni material institucional verificable. No deben fabricarse.

## Product Principles

1. **Hoy primero.** La información que cambia día a día —turnos, stock de vacunas, próxima campaña— debe ser lo más fácil de encontrar y entender.
2. **El DNI es la puerta, no el trámite.** El acceso ciudadano debe sentirse breve, seguro y comprensible; no debe exigir crear una cuenta ni memorizar credenciales.
3. **Lo público y lo operativo se distinguen.** Un vecino no debería tropezar con herramientas de gestión; el personal no debería atravesar mensajes de marketing para trabajar.
4. **La especificidad local es confianza.** Nombres de establecimientos, direcciones, fechas y lenguaje de Socabaya valen más que una estética genérica de salud.
5. **La demo no miente.** Se etiqueta lo simulado, se evitan claims institucionales no verificados y se protege la información sensible.

## Accessibility & Inclusion

- Objetivo: WCAG 2.2 AA para texto, controles, foco y estados.
- Diseño mobile-first para la ciudadanía, con targets táctiles cómodos y tipografía legible para personas mayores.
- No depender solo del color para comunicar estados; usar texto, iconos y patrones.
- Navegación completa por teclado, foco visible, mensajes de estado accesibles y respeto por `prefers-reduced-motion`.
- El contenido debe seguir siendo comprensible sin JavaScript para las tareas públicas esenciales.

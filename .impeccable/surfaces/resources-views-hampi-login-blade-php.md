---
version: 1
slug: "resources-views-hampi-login-blade-php"
primary_target: "resources/views/hampi/login.blade.php"
related_targets: ["resources/views/hampi/layout.blade.php","resources/views/hampi/admin_dashboard.blade.php","resources/views/empleado/panel.blade.php","resources/views/hampi/partials/calendario.blade.php"]
---

# Hampi portal — surface brief

## Scope and mode

Whole web portal. Public home and informational pages are **Persuade**; admin, employee, patient panel, and forms are **Operate**. The same world carries both, with less decoration and more density in Operate.

## Audience, job, action, proof

- **Vecino de Socabaya:** understand what is available today, find a posta or vaccine, and enter with DNI without creating an account. Proof: real daily counts, current status, local names, clear timestamps.
- **Personal de salud:** see today's shift, month hours, appointments, and profile. Action: scan, act, update. Proof: precise states and no ambiguity.
- **Administración:** publish the day's roster, vaccines, team, schedules, and campaigns. Action: create, edit, publish, retire. Proof: guarded destructive actions and visible results.
- **Academic demo:** every page must remain honest about simulated data and must not fabricate institutional claims.

## Chosen direction

**El parte de la posta.** The portal is the health post's daily report sheet, brought to the web: a ruled paper form with a folio, date, stamps, typed labels, and handwritten-feeling annotations. It is a civic-operational world, not a marketing landing and not a retro pastiche.

- **Palette:** bone paper ground, deep posta green as institutional ink, signal orange for action, stamp red for urgency/destruction, cobalt for links/info, maize for attention. No rainbow service cards.
- **Type:** Archivo for display and headings; Instrument Sans for body/UI; Martian Mono for dates, counters, codes, and status labels. No serif.
- **Materials:** rules, perforations, taped photo, stamped status, folio numbers, checkboxes, ledger rows. One unified paper ground with a faint grid; no full-width opaque sections.
- **Composition:** asymmetric sheet, not a split hero. The DNI form is inside the first viewport and is the primary action. The daily report rows are the proof.
- **Motion:** one page-load settle; the stamp lands; counters tick once. No infinite float, no scattered hover effects. Reduced motion removes all of it.
- **Cross-surface:** public pages are the posted sheet; admin is the desk copy; employee panel is the shift sheet; patient panel is the personal record.

## Memorable moment

The first viewport reads like today's sheet already pinned to the wall: date, folio, three live rows, a taped community photograph, and a red **DEMO ACADÉMICA** stamp. The DNI field is part of the sheet, not a separate marketing form.

## Direction contract

**THESIS:** The portal is the health post's daily report sheet, not a generic clinic landing. Refuse the soft teal gradient, stock doctor hero, and equal colored cards.

**OWN-WORLD:** Bone paper, deep posta green, signal orange, stamp red, cobalt and maize; ruled sheets, folio labels, stamps, taped photograph, ledger rows, mono data. Archivo display, Instrument Sans body, Martian Mono data. Six-pixel controls, one-pixel sheet borders, two-pixel stamps, subtle paper lift.

**STORY:** A vecino understands what is available today and enters with DNI in one move; staff sees the day and acts; administration trusts the record. The interface proves local, current information rather than claiming care.

**FIRST VIEWPORT:** An asymmetric daily sheet with date, folio, headline, embedded DNI form, three live rows, a taped community photo, and a visible academic-demo stamp. The primary action sits inside the sheet; the data rows are the proof.

**FORM:** El parte de la posta, the daily report sheet; position 6 of 7 grounded directions. seed:1fbdcf05c54f170e

**FINISH:** unreviewed and undocumented is unfinished; this build ends with the finish review, the verdict, DESIGN.md, and every shipping raster carrying its provenance.

## Constraints

- Preserve routes, roles, data, and behavior.
- Keep one unified page background.
- No real RENIEC token or sensitive data in UI/docs.
- WCAG 2.2 AA; 44 px targets; keyboard; reduced motion; no color-only states.
- Stock imagery must be credited and must not imply official institutional photography.

## Resolved decisions

- The unused `hero-atencion.jpg/.webp` rasters were removed; the new home only uses `comunidad-cuidado.*`.
- The active photo is Kampus Production / Pexels 7551677, under the Pexels License; provenance and transformation are documented in `README.md` and `DESIGN.md`.
- The final build uses self-hosted Archivo, Instrument Sans, and Martian Mono; the Bunny Fonts integration was removed from Vite.

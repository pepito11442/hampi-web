/**
 * Hampi · interacciones de interfaz
 * JavaScript progresivo: la página funciona aunque este archivo no cargue.
 */

const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

/* -------------------------------------------------------------------------- */
/* Menú móvil                                                                  */
/* -------------------------------------------------------------------------- */
function initMobileMenu() {
    const toggle = document.querySelector('[data-menu-toggle]');
    const menu = document.getElementById('mobile-menu');
    const iconOpen = document.querySelector('[data-menu-icon-open]');
    const iconClose = document.querySelector('[data-menu-icon-close]');

    if (!toggle || !menu) return;

    const setOpen = (open) => {
        menu.hidden = !open;
        toggle.setAttribute('aria-expanded', String(open));
        iconOpen?.classList.toggle('hidden', open);
        iconClose?.classList.toggle('hidden', !open);

        if (open) {
            menu.querySelector('a, button')?.focus();
        }
    };

    toggle.addEventListener('click', () => {
        setOpen(menu.hidden);
    });

    menu.querySelectorAll('a').forEach((link) => {
        link.addEventListener('click', () => setOpen(false));
    });

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && !menu.hidden) {
            setOpen(false);
            toggle.focus();
        }
    });

    window.addEventListener('resize', () => {
        if (window.innerWidth >= 768 && !menu.hidden) {
            menu.hidden = true;
            toggle.setAttribute('aria-expanded', 'false');
            iconOpen?.classList.remove('hidden');
            iconClose?.classList.add('hidden');
        }
    });
}

/* -------------------------------------------------------------------------- */
/* Encabezado con sombra al hacer scroll                                       */
/* -------------------------------------------------------------------------- */
function initHeaderScroll() {
    const header = document.querySelector('[data-site-header]');
    if (!header) return;

    const update = () => header.classList.toggle('is-scrolled', window.scrollY > 8);
    update();
    window.addEventListener('scroll', update, { passive: true });
}

/* -------------------------------------------------------------------------- */
/* Animaciones de entrada discretas                                            */
/* -------------------------------------------------------------------------- */
function initReveal() {
    const elements = document.querySelectorAll('[data-reveal]');
    if (!elements.length) return;

    const showImmediately = (element) => {
        element.classList.remove('reveal-pending');
        element.classList.add('is-visible');
    };

    if (prefersReducedMotion || !('IntersectionObserver' in window)) {
        elements.forEach(showImmediately);
        return;
    }

    const observer = new IntersectionObserver(
        (entries) => {
            entries.forEach((entry) => {
                if (entry.isIntersecting) {
                    showImmediately(entry.target);
                    observer.unobserve(entry.target);
                }
            });
        },
        { threshold: 0.12, rootMargin: '0px 0px -40px 0px' }
    );

    elements.forEach((element) => {
        // Solo se ocultan los elementos que están fuera del primer viewport.
        if (element.getBoundingClientRect().top < window.innerHeight * 0.95) {
            showImmediately(element);
            return;
        }

        element.classList.add('reveal-pending');
        observer.observe(element);
    });
}

/* -------------------------------------------------------------------------- */
/* Mostrar / ocultar contraseña                                                */
/* -------------------------------------------------------------------------- */
function initPasswordToggles() {
    document.querySelectorAll('[data-password-toggle]').forEach((button) => {
        const input = document.getElementById(button.dataset.passwordToggle);
        if (!input) return;

        button.addEventListener('click', () => {
            const isPassword = input.type === 'password';
            input.type = isPassword ? 'text' : 'password';
            button.setAttribute('aria-pressed', String(isPassword));
            button.querySelector('[data-password-label]').textContent = isPassword ? 'Ocultar' : 'Mostrar';
        });
    });
}

/* -------------------------------------------------------------------------- */
/* Filtros del Pizarrín de Vacunas                                             */
/* -------------------------------------------------------------------------- */
function initStockFilters() {
    const filterButtons = document.querySelectorAll('[data-stock-filter]');
    const cards = document.querySelectorAll('[data-vaccine-card]');
    const counter = document.querySelector('[data-vaccine-count]');
    const emptyMessage = document.querySelector('[data-filter-empty]');

    if (!filterButtons.length || !cards.length) return;

    const applyFilter = (filter) => {
        let visible = 0;

        cards.forEach((card) => {
            const matches = filter === 'all' || card.dataset.stock === filter;
            card.hidden = !matches;
            if (matches) visible += 1;
        });

        filterButtons.forEach((button) => {
            button.setAttribute('aria-pressed', String(button.dataset.stockFilter === filter));
        });

        if (counter) {
            counter.textContent = String(visible);
        }

        if (emptyMessage) {
            emptyMessage.hidden = visible !== 0;
        }
    };

    filterButtons.forEach((button) => {
        button.addEventListener('click', () => applyFilter(button.dataset.stockFilter));
    });

    applyFilter('all');
}

/* -------------------------------------------------------------------------- */
/* Horarios disponibles al agendar una cita                                     */
/* -------------------------------------------------------------------------- */
function initCitaSlots() {
    const form = document.querySelector('[data-citas-form]');
    if (!form) return;

    const fecha = form.querySelector('[data-citas-fecha]');
    const hora = form.querySelector('[data-citas-hora]');
    const resumen = form.querySelector('[data-citas-resumen]');
    const submit = form.querySelector('[data-citas-submit]');
    const url = hora?.dataset.disponibilidadUrl;

    if (!fecha || !hora || !url) return;

    let request;

    const setStatus = (message) => {
        if (resumen) resumen.textContent = message;
    };

    const setDisabled = (disabled) => {
        hora.disabled = disabled;
        if (submit) submit.disabled = disabled;
    };

    const renderOptions = (slots) => {
        hora.innerHTML = '';

        if (!Array.isArray(slots) || !slots.length) {
            const option = document.createElement('option');
            option.value = '';
            option.textContent = 'Sin horarios disponibles';
            hora.appendChild(option);
            return false;
        }

        slots.forEach((slot) => {
            const option = document.createElement('option');
            option.value = slot.hora;
            option.textContent = slot.etiqueta;
            hora.appendChild(option);
        });

        return true;
    };

    const cargar = async () => {
        if (!fecha.value) return;

        request?.abort();
        request = new AbortController();
        setDisabled(true);
        setStatus('Consultando horarios…');

        try {
            const response = await fetch(`${url}?fecha=${encodeURIComponent(fecha.value)}`, {
                credentials: 'same-origin',
                headers: { Accept: 'application/json' },
                signal: request.signal,
            });

            if (!response.ok) throw new Error('No se pudo consultar la disponibilidad.');

            const data = await response.json();
            const haySlots = renderOptions(data.slots);

            if (haySlots) {
                setDisabled(false);
                setStatus(
                    data.turno && data.horario
                        ? `Turno ${data.turno}: ${data.horario}`
                        : 'Horarios actualizados.'
                );
            } else {
                setDisabled(true);
                setStatus(data.mensaje || 'No hay horarios disponibles para esa fecha.');
            }
        } catch (error) {
            if (error.name === 'AbortError') return;

            setDisabled(true);
            setStatus('No se pudieron cargar los horarios. Vuelve a intentarlo.');
        }
    };

    fecha.addEventListener('change', cargar);
}

document.addEventListener('DOMContentLoaded', () => {
    initMobileMenu();
    initHeaderScroll();
    initReveal();
    initPasswordToggles();
    initStockFilters();
    initCitaSlots();
});

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
        if (window.innerWidth >= 1024 && !menu.hidden) {
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
    const medico = form.querySelector('[data-citas-medico]');
    const bloqueado = form.hasAttribute('data-citas-bloqueado');
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
        if (submit) submit.disabled = disabled || bloqueado;
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
            const params = new URLSearchParams({ fecha: fecha.value });
            if (medico) params.set('medico', medico.value);

            const response = await fetch(`${url}?${params}`, {
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
    medico?.addEventListener('change', cargar);
}

/* -------------------------------------------------------------------------- */
/* Confirmaciones y envíos automáticos (sin JavaScript en línea, apto CSP)     */
/* -------------------------------------------------------------------------- */
function initConfirmaciones() {
    document.addEventListener('submit', (event) => {
        const form = event.target.closest?.('form[data-confirm]');
        if (form && !window.confirm(form.dataset.confirm)) {
            event.preventDefault();
        }
    });

    document.querySelectorAll('[data-autosubmit]').forEach((control) => {
        control.addEventListener('change', () => control.form?.requestSubmit());
    });
}

/* -------------------------------------------------------------------------- */
/* Campos que solo aceptan dígitos (DNI y códigos)                             */
/* -------------------------------------------------------------------------- */
function initSoloDigitos() {
    document.querySelectorAll('[data-solo-digitos]').forEach((input) => {
        input.addEventListener('input', () => {
            const max = Number(input.getAttribute('maxlength')) || undefined;
            const limpio = input.value.replace(/\D/g, '').slice(0, max);
            if (limpio !== input.value) input.value = limpio;
        });
    });
}

/* -------------------------------------------------------------------------- */
/* Tamaños dinámicos sin style="" (la CSP no permite estilos en línea)         */
/* -------------------------------------------------------------------------- */
function initEstilosDinamicos() {
    const porcentaje = (valor) => `${Math.min(Math.max(Number(valor) || 0, 0), 100)}%`;

    document.querySelectorAll('[data-alto]').forEach((el) => {
        el.style.height = porcentaje(el.dataset.alto);
    });
    document.querySelectorAll('[data-ancho]').forEach((el) => {
        el.style.width = porcentaje(el.dataset.ancho);
    });
    document.querySelectorAll('[data-reveal-delay]').forEach((el) => {
        el.style.setProperty('--reveal-delay', `${Number(el.dataset.revealDelay) || 0}ms`);
    });
}

/* -------------------------------------------------------------------------- */
/* Autocompletar nombres con RENIEC al crear el código (opcional)              */
/* -------------------------------------------------------------------------- */
function initReniec() {
    const form = document.querySelector('[data-reniec-form]');
    if (!form) return;

    const dni = form.querySelector('[data-reniec-dni]');
    const nombres = form.querySelector('[data-reniec-nombres]');
    const apellidos = form.querySelector('[data-reniec-apellidos]');
    const estado = form.querySelector('[data-reniec-estado]');
    // Token de un solo uso emitido con el formulario: permite una consulta.
    let token = form.dataset.reniecToken || '';

    dni?.addEventListener('input', async () => {
        if (dni.value.length !== 8 || !token) return;
        const usado = token;
        token = '';
        estado.textContent = 'Buscando…';

        try {
            const respuesta = await fetch(`/api/consultar-dni/${encodeURIComponent(dni.value)}`, {
                headers: { Accept: 'application/json', 'X-Reniec-Token': usado },
            });
            const data = await respuesta.json();

            if (data.success) {
                if (!nombres.value) nombres.value = data.nombres || '';
                if (!apellidos.value) apellidos.value = (data.apellidos || '').trim();
                estado.textContent = 'Datos encontrados';
            } else {
                estado.textContent = 'Escribe tus nombres';
            }
        } catch {
            estado.textContent = 'Escribe tus nombres';
        }
    });
}

document.addEventListener('DOMContentLoaded', () => {
    initEstilosDinamicos();
    initMobileMenu();
    initHeaderScroll();
    initReveal();
    initPasswordToggles();
    initStockFilters();
    initCitaSlots();
    initConfirmaciones();
    initSoloDigitos();
    initReniec();
});

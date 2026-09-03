/**
 * Microinteractions for the Hexágono Research landing page
 * (resources/views/welcome.blade.php).
 *
 * Plain JavaScript, no framework: a subtle animated hexagon honeycomb
 * canvas behind the hero, a header that solidifies on scroll,
 * scroll-triggered fade-ins, a mobile nav toggle, and a fetch()-based
 * contact form submit that shows success/validation errors without a
 * full page reload.
 */

document.addEventListener('DOMContentLoaded', () => {
    initHoneycombCanvas();
    initHeaderScrollState();
    initMobileNav();
    initScrollReveal();
    initContactForm();
});

/**
 * Draws a grid of pointy-top hexagons (echoing the brand mark) tiling the
 * hero background, each gently breathing in and out of brightness on its
 * own phase so the honeycomb reads as quietly "alive" without being
 * flashy. Skipped entirely when the visitor prefers reduced motion.
 */
function initHoneycombCanvas() {
    const canvas = document.getElementById('honeycomb-canvas');

    if (!canvas) {
        return;
    }

    const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    const ctx = canvas.getContext('2d');

    if (!ctx || prefersReducedMotion) {
        return;
    }

    const hexSize = 32; // center-to-vertex radius
    const hexGap = 2; // shrinks each drawn hexagon slightly so cell edges read as separate

    /** @type {{x: number, y: number, phase: number, speed: number}[]} */
    let cells = [];
    let width = 0;
    let height = 0;

    function buildGrid() {
        const hexWidth = Math.sqrt(3) * hexSize;
        const vertSpacing = hexSize * 1.5;
        const cols = Math.ceil(width / hexWidth) + 2;
        const rows = Math.ceil(height / vertSpacing) + 2;

        const grid = [];

        for (let row = -1; row < rows; row++) {
            const y = row * vertSpacing;
            const xOffset = row % 2 !== 0 ? hexWidth / 2 : 0;

            for (let col = -1; col < cols; col++) {
                grid.push({
                    x: col * hexWidth + xOffset,
                    y,
                    phase: Math.random() * Math.PI * 2,
                    speed: 0.3 + Math.random() * 0.5,
                });
            }
        }

        return grid;
    }

    function resize() {
        width = canvas.clientWidth;
        height = canvas.clientHeight;
        canvas.width = width * window.devicePixelRatio;
        canvas.height = height * window.devicePixelRatio;
        ctx.setTransform(window.devicePixelRatio, 0, 0, window.devicePixelRatio, 0, 0);

        cells = buildGrid();
    }

    function tracePath(x, y, size) {
        ctx.beginPath();

        for (let i = 0; i < 6; i++) {
            const angle = ((Math.PI * 2) / 6) * i - Math.PI / 2;
            const px = x + size * Math.cos(angle);
            const py = y + size * Math.sin(angle);

            if (i === 0) {
                ctx.moveTo(px, py);
            } else {
                ctx.lineTo(px, py);
            }
        }

        ctx.closePath();
    }

    /**
     * @param {number} now DOMHighResTimeStamp from requestAnimationFrame
     */
    function step(now) {
        const t = now / 1000;
        ctx.clearRect(0, 0, width, height);

        for (const cell of cells) {
            const pulse = (Math.sin(t * cell.speed + cell.phase) + 1) / 2; // 0..1

            tracePath(cell.x, cell.y, hexSize - hexGap);
            ctx.strokeStyle = `rgba(74, 158, 255, ${0.04 + pulse * 0.12})`;
            ctx.lineWidth = 1;
            ctx.stroke();

            if (pulse > 0.82) {
                ctx.fillStyle = `rgba(74, 158, 255, ${(pulse - 0.82) * 0.5})`;
                ctx.fill();
            }
        }

        requestAnimationFrame(step);
    }

    resize();
    window.addEventListener('resize', resize);
    requestAnimationFrame(step);
}

/**
 * Swaps the header from transparent to a solid, blurred panel once the
 * page has scrolled past the hero.
 */
function initHeaderScrollState() {
    const header = document.getElementById('site-header');

    if (!header) {
        return;
    }

    const applyState = () => {
        header.classList.toggle('is-scrolled', window.scrollY > 24);
    };

    applyState();
    window.addEventListener('scroll', applyState, { passive: true });
}

/**
 * Toggles the mobile navigation panel and keeps the trigger's
 * aria-expanded state in sync for keyboard/screen-reader users.
 */
function initMobileNav() {
    const toggle = document.getElementById('mobile-nav-toggle');
    const panel = document.getElementById('mobile-nav-panel');

    if (!toggle || !panel) {
        return;
    }

    toggle.addEventListener('click', () => {
        const isOpen = panel.classList.toggle('hidden') === false;
        toggle.setAttribute('aria-expanded', String(isOpen));
    });

    panel.querySelectorAll('a').forEach((link) => {
        link.addEventListener('click', () => {
            panel.classList.add('hidden');
            toggle.setAttribute('aria-expanded', 'false');
        });
    });
}

/**
 * Fades sections/cards in as they enter the viewport. Elements are marked
 * up with the `.reveal` class (see resources/css/app.css); this only ever
 * adds `.is-visible`, so content stays readable if JS fails to load.
 */
function initScrollReveal() {
    const targets = document.querySelectorAll('.reveal');

    if (!('IntersectionObserver' in window) || targets.length === 0) {
        targets.forEach((el) => el.classList.add('is-visible'));

        return;
    }

    const observer = new IntersectionObserver(
        (entries) => {
            for (const entry of entries) {
                if (entry.isIntersecting) {
                    entry.target.classList.add('is-visible');
                    observer.unobserve(entry.target);
                }
            }
        },
        { threshold: 0.15, rootMargin: '0px 0px -40px 0px' }
    );

    targets.forEach((el) => observer.observe(el));
}

/**
 * Submits the contact form via fetch() so the visitor gets inline
 * success/validation feedback without leaving the page. Sends
 * "Accept: application/json" so ContactController@store returns JSON
 * instead of redirecting.
 */
function initContactForm() {
    const form = document.getElementById('contact-form');

    if (!form) {
        return;
    }

    const submitButton = form.querySelector('button[type="submit"]');
    const statusBox = document.getElementById('contact-form-status');

    form.addEventListener('submit', async (event) => {
        event.preventDefault();
        clearFieldErrors(form);
        setStatus(statusBox, null);
        setLoading(submitButton, true);

        try {
            const response = await fetch(form.action, {
                method: 'POST',
                headers: {
                    Accept: 'application/json',
                },
                body: new FormData(form),
            });

            const data = await response.json();

            if (response.status === 422) {
                showFieldErrors(form, data.errors || {});
                setStatus(statusBox, 'error', 'Revisa los campos marcados e inténtalo de nuevo.');

                return;
            }

            if (!response.ok) {
                throw new Error('Unexpected response');
            }

            form.reset();
            setStatus(statusBox, 'success', data.message);
        } catch (error) {
            setStatus(
                statusBox,
                'error',
                'No fue posible enviar tu mensaje. Inténtalo de nuevo o escríbenos directamente por correo.'
            );
        } finally {
            setLoading(submitButton, false);
        }
    });
}

/**
 * @param {HTMLButtonElement|null} button
 * @param {boolean} isLoading
 */
function setLoading(button, isLoading) {
    if (!button) {
        return;
    }

    button.disabled = isLoading;
    button.dataset.originalText ??= button.textContent ?? '';
    button.textContent = isLoading ? 'Enviando…' : button.dataset.originalText;
}

/**
 * @param {HTMLElement|null} box
 * @param {'success'|'error'|null} type
 * @param {string} [message]
 */
function setStatus(box, type, message) {
    if (!box) {
        return;
    }

    if (!type) {
        box.classList.add('hidden');
        box.textContent = '';

        return;
    }

    box.textContent = message ?? '';
    box.classList.remove('hidden', 'text-red-400', 'text-electric', 'border-red-400/30', 'border-electric/30');
    box.classList.add(
        type === 'success' ? 'text-electric' : 'text-red-400',
        type === 'success' ? 'border-electric/30' : 'border-red-400/30'
    );
}

/**
 * @param {HTMLFormElement} form
 * @param {Record<string, string[]>} errors
 */
function showFieldErrors(form, errors) {
    for (const [field, messages] of Object.entries(errors)) {
        const el = form.querySelector(`[data-error-for="${field}"]`);

        if (el) {
            el.textContent = messages[0] ?? '';
            el.classList.remove('hidden');
        }

        const input = form.querySelector(`[name="${field}"]`);
        input?.classList.add('border-red-400/60');
    }
}

/**
 * @param {HTMLFormElement} form
 */
function clearFieldErrors(form) {
    form.querySelectorAll('[data-error-for]').forEach((el) => {
        el.textContent = '';
        el.classList.add('hidden');
    });

    form.querySelectorAll('[name]').forEach((el) => el.classList.remove('border-red-400/60'));
}

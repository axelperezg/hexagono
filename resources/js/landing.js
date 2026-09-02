/**
 * Microinteractions for the Hexágono Research landing page
 * (resources/views/welcome.blade.php).
 *
 * Plain JavaScript, no framework: a subtle animated node/network canvas
 * behind the hero, a header that solidifies on scroll, scroll-triggered
 * fade-ins, a mobile nav toggle, and a fetch()-based contact form submit
 * that shows success/validation errors without a full page reload.
 */

document.addEventListener('DOMContentLoaded', () => {
    initNetworkCanvas();
    initHeaderScrollState();
    initMobileNav();
    initScrollReveal();
    initContactForm();
});

/**
 * Draws a sparse field of nodes connected by thin lines when close enough,
 * gently drifting. Suggests "data / infrastructure" without being flashy.
 * Skipped entirely when the visitor prefers reduced motion.
 */
function initNetworkCanvas() {
    const canvas = document.getElementById('network-canvas');

    if (!canvas) {
        return;
    }

    const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    const ctx = canvas.getContext('2d');

    if (!ctx || prefersReducedMotion) {
        return;
    }

    /** @type {{x: number, y: number, vx: number, vy: number}[]} */
    let nodes = [];
    let width = 0;
    let height = 0;
    const linkDistance = 140;

    function resize() {
        width = canvas.clientWidth;
        height = canvas.clientHeight;
        canvas.width = width * window.devicePixelRatio;
        canvas.height = height * window.devicePixelRatio;
        ctx.setTransform(window.devicePixelRatio, 0, 0, window.devicePixelRatio, 0, 0);

        const nodeCount = Math.max(24, Math.round((width * height) / 28000));
        nodes = Array.from({ length: nodeCount }, () => ({
            x: Math.random() * width,
            y: Math.random() * height,
            vx: (Math.random() - 0.5) * 0.25,
            vy: (Math.random() - 0.5) * 0.25,
        }));
    }

    function step() {
        ctx.clearRect(0, 0, width, height);

        for (const node of nodes) {
            node.x += node.vx;
            node.y += node.vy;

            if (node.x < 0 || node.x > width) {
                node.vx *= -1;
            }

            if (node.y < 0 || node.y > height) {
                node.vy *= -1;
            }
        }

        for (let i = 0; i < nodes.length; i++) {
            for (let j = i + 1; j < nodes.length; j++) {
                const a = nodes[i];
                const b = nodes[j];
                const dx = a.x - b.x;
                const dy = a.y - b.y;
                const distance = Math.sqrt(dx * dx + dy * dy);

                if (distance < linkDistance) {
                    ctx.strokeStyle = `rgba(74, 158, 255, ${0.12 * (1 - distance / linkDistance)})`;
                    ctx.lineWidth = 1;
                    ctx.beginPath();
                    ctx.moveTo(a.x, a.y);
                    ctx.lineTo(b.x, b.y);
                    ctx.stroke();
                }
            }
        }

        for (const node of nodes) {
            ctx.fillStyle = 'rgba(74, 158, 255, 0.45)';
            ctx.beginPath();
            ctx.arc(node.x, node.y, 1.4, 0, Math.PI * 2);
            ctx.fill();
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

import { animate, inView, stagger } from 'motion';

// Chart.js is only needed on the admin dashboard, so load it on demand.
if (document.querySelector('canvas[data-chart]')) {
    import('./charts');
}

const reduced = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
const ease = [0.22, 1, 0.36, 1];

function boot() {
    window.__motionReady = true;
    if (reduced) {
        return;
    }

    // Grids and lists: children rise in one after another when the block scrolls into view.
    document.querySelectorAll('[data-stagger]').forEach((group) => {
        const items = [...group.children];
        inView(group, () => {
            animate(
                items,
                { opacity: [0, 1], transform: ['translateY(18px)', 'translateY(0px)'] },
                { duration: 0.5, delay: stagger(0.05, { startDelay: 0.05 }), ease },
            );
        }, { margin: '0px 0px -8% 0px' });
    });

    // Flash messages drop in once.
    document.querySelectorAll('[data-flash]').forEach((el) => {
        animate(el, { opacity: [0, 1], transform: ['translateY(-8px)', 'translateY(0px)'] }, { duration: 0.35, ease });
    });

    // Dashboard numbers count up.
    document.querySelectorAll('[data-count]').forEach((el) => {
        const target = parseFloat(el.dataset.count);
        if (Number.isNaN(target)) return;
        const prefix = el.dataset.prefix || '';
        inView(el, () => {
            animate(0, target, {
                duration: 1,
                ease,
                onUpdate: (v) => { el.textContent = prefix + Math.round(v).toLocaleString('en-US'); },
            });
        });
    });

    // Cart badge bumps when the count changed since the last page.
    const badge = document.querySelector('[data-cart-count]');
    if (badge) {
        const now = badge.dataset.cartCount;
        let before = null;
        try {
            before = sessionStorage.getItem('cartCount');
            sessionStorage.setItem('cartCount', now);
        } catch (e) { /* storage blocked */ }
        if (before !== null && before !== now) {
            animate(badge, { transform: ['scale(1)', 'scale(1.5)', 'scale(1)'] }, { duration: 0.5, ease });
        }
    }
}

boot();

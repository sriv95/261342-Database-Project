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

// Carousel: native scroll-snap track with arrows and page dots.
document.querySelectorAll('[data-carousel]').forEach((root) => {
    const track = root.querySelector('[data-track]');
    const prev = root.querySelector('[data-prev]');
    const next = root.querySelector('[data-next]');
    const dotsBox = root.querySelector('[data-dots]');
    const behavior = reduced ? 'auto' : 'smooth';
    let dots = [];

    const pages = () => Math.max(1, Math.round(track.scrollWidth / track.clientWidth));
    const current = () => Math.round(track.scrollLeft / track.clientWidth);
    const go = (i) => track.scrollTo({ left: i * track.clientWidth, behavior });

    const buildDots = () => {
        dotsBox.textContent = '';
        dots = Array.from({ length: pages() }, (_, i) => {
            const b = document.createElement('button');
            b.type = 'button';
            b.setAttribute('aria-label', `Page ${i + 1}`);
            b.className = 'h-2.5 rounded-full bg-line transition-all duration-300';
            b.addEventListener('click', () => go(i));
            dotsBox.append(b);
            return b;
        });
        dotsBox.hidden = dots.length < 2;
    };

    const sync = () => {
        const i = current();
        dots.forEach((d, n) => {
            d.style.width = n === i ? '1.5rem' : '0.625rem';
            d.style.opacity = n === i ? '1' : '0.4';
            n === i ? d.setAttribute('aria-current', 'true') : d.removeAttribute('aria-current');
        });
        prev.disabled = i <= 0;
        next.disabled = i >= pages() - 1;
    };

    prev.addEventListener('click', () => go(Math.max(0, current() - 1)));
    next.addEventListener('click', () => go(Math.min(pages() - 1, current() + 1)));
    track.addEventListener('keydown', (e) => {
        if (e.key === 'ArrowRight') { e.preventDefault(); next.click(); }
        if (e.key === 'ArrowLeft') { e.preventDefault(); prev.click(); }
    });

    let frame;
    track.addEventListener('scroll', () => { cancelAnimationFrame(frame); frame = requestAnimationFrame(sync); }, { passive: true });
    window.addEventListener('resize', () => { buildDots(); sync(); });

    buildDots();
    sync();
});

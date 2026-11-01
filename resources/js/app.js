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

// In-place actions: wish list toggle and add to cart submit with fetch instead of reloading the page.
const toast = (message, isError = false) => {
    let box = document.getElementById('toast');
    if (!box) {
        box = document.createElement('div');
        box.id = 'toast';
        box.setAttribute('role', 'status');
        box.className = 'fixed inset-x-0 bottom-6 z-50 flex justify-center px-4 pointer-events-none';
        document.body.append(box);
    }
    const el = document.createElement('div');
    el.className = 'pointer-events-auto rounded-full px-5 py-2.5 text-sm font-medium shadow-lg '
        + (isError ? 'bg-red-700 text-white' : 'bg-gray-900 text-white');
    el.textContent = message;
    box.replaceChildren(el);
    if (!reduced) animate(el, { opacity: [0, 1], transform: ['translateY(12px)', 'translateY(0px)'] }, { duration: 0.25, ease });
    clearTimeout(box._t);
    box._t = setTimeout(() => el.remove(), 2400);
};

const csrfBody = (form) => new FormData(form);
const post = async (form) => {
    const res = await fetch(form.action, {
        method: 'POST',
        headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
        body: csrfBody(form),
        credentials: 'same-origin',
    });
    const data = await res.json().catch(() => ({}));
    if (!res.ok) throw new Error(data.message || Object.values(data.errors || {})[0]?.[0] || 'Something went wrong. Try again.');
    return data;
};

const heartIcon = (on) => `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" stroke-width="1.8" class="h-5 w-5 ${on ? 'fill-red-500 stroke-red-500' : 'fill-none stroke-gray-600 hover:stroke-red-500'}"><path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12Z"/></svg>`;

const applyWish = (form, wished) => {
    form.dataset.wished = wished ? '1' : '0';
    const btn = form.querySelector('button');
    const label = wished ? 'Remove from wish list' : 'Add to wish list';
    btn.setAttribute('aria-label', label);
    btn.title = label;
    if (btn.hasAttribute('data-wish-label')) {
        btn.textContent = wished ? '♥ Saved' : '♡ Wish list';
    } else {
        btn.classList.toggle('opacity-100', wished);
        ['opacity-0', 'group-hover:opacity-100', 'pointer-coarse:opacity-100'].forEach((c) => btn.classList.toggle(c, !wished));
        btn.innerHTML = heartIcon(wished);
        if (!reduced) animate(btn, { transform: ['scale(1)', 'scale(1.25)', 'scale(1)'] }, { duration: 0.35, ease });
    }
};

const removeFromWishPage = (form) => {
    const card = form.closest('.group');
    const grid = card?.closest('[data-wish-page]');
    if (!card || !grid) return;
    const done = () => {
        card.remove();
        if (!grid.children.length) location.reload();
    };
    reduced ? done() : animate(card, { opacity: 0, transform: 'scale(0.96)' }, { duration: 0.25, ease }).finished.then(done);
};

const bumpCart = (count, silent = false) => {
    const link = document.querySelector('[data-cart-link]');
    if (!link) return;
    let badge = link.querySelector('[data-cart-count]');
    if (!badge) {
        badge = document.createElement('span');
        badge.className = 'ml-1 inline-block min-w-5 rounded-full bg-shuttle px-1.5 text-center text-xs font-semibold leading-5 text-gray-900';
        link.append(badge);
    }
    badge.dataset.cartCount = count;
    badge.textContent = count;
    badge.hidden = count < 1;
    try { sessionStorage.setItem('cartCount', String(count)); } catch (e) { /* storage blocked */ }
    if (!reduced && !silent) animate(badge, { transform: ['scale(1)', 'scale(1.5)', 'scale(1)'] }, { duration: 0.5, ease });
};

const money = (n) => '฿' + Math.round(n).toLocaleString('en-US');

// Pending state: a spinner on text buttons while a request is in flight.
const spin = (form, on) => {
    if (['wish', 'cart-update'].includes(form.dataset.ajax)) return;
    form.querySelectorAll('button:not([data-step])').forEach((b) => {
        if (b.textContent.trim()) b.classList.toggle('is-loading', on);
    });
};

const cartAfter = {
    'cart-update': (form, data) => {
        const row = form.closest('[data-cart-row]');
        const input = form.querySelector('input[name="quantity"]');
        input.value = data.quantity;
        row.querySelector('[data-line-total]').textContent = money(data.lineTotal);
        row.querySelector('[data-stock-warning]')?.classList.toggle('hidden', data.quantity <= Number(form.dataset.stock));
        document.querySelectorAll('[data-subtotal]').forEach((el) => { el.textContent = money(data.subtotal); });
        bumpCart(data.cartCount, true);
    },
    'cart-remove': (form, data) => {
        const row = form.closest('[data-cart-row]');
        document.querySelectorAll('[data-subtotal]').forEach((el) => { el.textContent = money(data.subtotal); });
        bumpCart(data.cartCount, true);
        const done = () => { row.remove(); if (!data.remaining) location.reload(); };
        reduced ? done() : animate(row, { opacity: 0, transform: 'translateX(-16px)' }, { duration: 0.25, ease }).finished.then(done);
    },
};

document.addEventListener('submit', async (e) => {
    const form = e.target.closest('form[data-ajax]');
    if (!form) return;
    e.preventDefault();
    if (form.dataset.busy) { form.dataset.again = '1'; return; }
    form.dataset.busy = '1';
    const row = form.closest('[data-cart-row]');
    row?.classList.add('opacity-60');
    spin(form, true);
    try {
        const data = await post(form);
        const kind = form.dataset.ajax;
        if (kind === 'wish') {
            applyWish(form, data.wished);
            if (!data.wished) removeFromWishPage(form);
        } else if (kind === 'cart') {
            bumpCart(data.cartCount);
            if ('move' in form.dataset) removeFromWishPage(form);
        } else if (cartAfter[kind]) {
            cartAfter[kind](form, data);
        }
        if (kind !== 'cart-update') toast(data.message);
    } catch (err) {
        toast(err.message, true);
    } finally {
        row?.classList.remove('opacity-60');
        spin(form, false);
        delete form.dataset.busy;
        if (form.dataset.again) { delete form.dataset.again; form.requestSubmit(); }
    }
});

// Cart quantity stepper: instant feedback, one save after the clicking settles.
const clampQty = (form, value) => Math.min(Number(form.dataset.max) || 20, Math.max(1, Math.round(Number(value)) || 1));
const queueSave = (form) => {
    const input = form.querySelector('input[name="quantity"]');
    const row = form.closest('[data-cart-row]');
    input.value = clampQty(form, input.value);
    row.querySelector('[data-line-total]').textContent = money(input.value * Number(row.dataset.price));
    clearTimeout(form._save);
    form._save = setTimeout(() => form.requestSubmit(), 350);
};
document.addEventListener('click', (e) => {
    const step = e.target.closest('[data-step]');
    if (!step) return;
    const form = step.closest('form');
    const input = form.querySelector('input[name="quantity"]');
    input.value = clampQty(form, Number(input.value) + Number(step.dataset.step));
    queueSave(form);
});
document.addEventListener('change', (e) => {
    const form = e.target.closest('form[data-ajax="cart-update"]');
    if (form && e.target.name === 'quantity') queueSave(form);
});

// Plain forms (login, checkout, reviews, address ...): show pending state while the page loads.
document.addEventListener('submit', (e) => {
    const form = e.target;
    if (form.dataset.ajax) return;
    setTimeout(() => {
        if (e.defaultPrevented) return;
        form.querySelectorAll('button:not([type="button"])').forEach((b) => {
            if (b.textContent.trim()) b.classList.add('is-loading');
        });
    }, 0);
});
window.addEventListener('pageshow', (e) => {
    if (e.persisted) document.querySelectorAll('.is-loading').forEach((b) => b.classList.remove('is-loading'));
});

// Product photos fade in once loaded (the tile shimmers until then).
document.querySelectorAll('.tile img').forEach((img) => {
    if (img.complete) { img.classList.add('loaded'); return; }
    img.addEventListener('load', () => img.classList.add('loaded'), { once: true });
    img.addEventListener('error', () => img.classList.add('loaded'), { once: true });
});

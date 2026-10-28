document.addEventListener('DOMContentLoaded', () => {
    const root = document.documentElement;
    const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)');
    const motionEnabled = () => root.classList.contains('motion-ready') && !reducedMotion.matches;
    let revealObserver;

    // Enhance visible HTML; content stays usable when JavaScript or animation is unavailable.
    function enableMotion() {
        if (reducedMotion.matches || !('IntersectionObserver' in window)) return;
        const title = document.querySelector('.studio-masthead');
        if (title && !title.querySelector('.masthead-letter')) {
            const label = title.textContent.trim();
            const letters = document.createDocumentFragment();
            Array.from(label).forEach((character, index) => {
                const letter = document.createElement('span');
                letter.className = 'masthead-letter' + (character === '.' ? ' masthead-dot' : '');
                letter.textContent = character;
                letter.style.setProperty('--letter-index', index);
                letter.setAttribute('aria-hidden', 'true');
                letters.append(letter);
            });
            title.setAttribute('aria-label', label);
            title.replaceChildren(letters);
        }

        const selector = [
            '.lookbook-top', '.lookbook-photo', '.lookbook-bottom > *',
            '.section-heading', '.treatment-row', '.collection-item',
            '.customer-summary', '.review-card', '.page-intro', '.filter-bar',
            '.product-card', '.auth-visual', '.auth-form',
            '.checkout-grid > .panel', '.booking-row', '.confirmation-heading',
            '.confirmation-card', '.stats-grid > *',
            '.footer-contact', '.footer-main', '.footer-bottom',
        ].join(', ');

        revealObserver = new IntersectionObserver(entries => {
            entries.forEach(entry => {
                if (!entry.isIntersecting) return;
                entry.target.classList.add('is-visible');
                revealObserver.unobserve(entry.target);
            });
        }, { threshold: 0.06, rootMargin: '0px 0px -24px 0px' });

        document.querySelectorAll(selector).forEach(element => {
            if (element.parentElement.closest('[data-reveal]')) return;
            const image = element.matches('.lookbook-photo, .auth-visual');
            element.dataset.reveal = image ? 'image' : element.matches('.treatment-row') ? 'row' : 'up';
            const index = Array.from(element.parentElement.children).indexOf(element);
            const hero = element.matches('.lookbook-photo');
            const stagger = element.matches('.product-card, .collection-item, .review-card, .treatment-row, .stat-card');
            const delay = hero ? 180 + index * 120 : stagger ? Math.min(index % 3, 2) * 85 : 0;
            element.style.setProperty('--reveal-delay', delay + 'ms');
            revealObserver.observe(element);
        });
        root.classList.add('motion-ready');
    }

    function disableMotion() {
        root.classList.remove('motion-ready', 'is-leaving');
        revealObserver?.disconnect();
        document.querySelectorAll('[data-reveal]').forEach(element => element.classList.add('is-visible'));
    }

    try {
        enableMotion();
    } catch {
        disableMotion();
    }

    reducedMotion.addEventListener('change', event => {
        if (event.matches) disableMotion();
        // Enabling animations later never hides content the visitor has already reached.
    });

    // A keyboard user can focus content before it has crossed the scroll threshold.
    document.addEventListener('focusin', event => {
        const target = event.target.closest('[data-reveal]');
        if (target) {
            target.classList.add('is-visible');
            revealObserver?.unobserve(target);
        }
    });

    const menuToggle = document.querySelector('[data-menu-toggle]');
    const menu = document.getElementById('mobile-nav');
    let menuTimer;
    function setMenu(open, restoreFocus = false) {
        if (!menuToggle || !menu) return;
        clearTimeout(menuTimer);
        menu.classList.remove('is-menu-closing');
        menuToggle.setAttribute('aria-expanded', String(open));
        menuToggle.setAttribute('aria-label', open ? 'Tutup menu' : 'Buka menu');
        if (open) {
            menu.inert = false;
            menu.hidden = false;
        } else if (motionEnabled() && !menu.hidden) {
            menu.inert = true;
            menu.classList.add('is-menu-closing');
            menuTimer = setTimeout(() => {
                menu.hidden = true;
                menu.classList.remove('is-menu-closing');
            }, 200);
        } else {
            menu.inert = true;
            menu.hidden = true;
        }
        if (restoreFocus) menuToggle.focus();
    }
    menuToggle?.addEventListener('click', () => {
        setMenu(menuToggle.getAttribute('aria-expanded') !== 'true');
    });
    document.addEventListener('keydown', event => {
        if (event.key === 'Escape' && menu && !menu.hidden) setMenu(false, true);
    });

    document.querySelectorAll('[data-password-toggle]').forEach(button => {
        button.addEventListener('click', () => {
            const input = document.getElementById(button.dataset.passwordToggle);
            if (!input) return;
            const visible = input.type === 'password';
            input.type = visible ? 'text' : 'password';
            button.textContent = visible ? 'Sembunyi' : 'Lihat';
            button.setAttribute('aria-pressed', String(visible));
            button.setAttribute('aria-label', visible ? 'Sembunyikan password' : 'Tampilkan password');
        });
    });

    const dialog = document.querySelector('[data-gallery-dialog]');
    let dialogTimer;
    let dialogTrigger;
    function closeGallery() {
        if (!dialog?.open || dialog.classList.contains('is-closing')) return;
        clearTimeout(dialogTimer);
        dialog.classList.remove('is-opening');
        if (!motionEnabled()) {
            dialog.close();
            return;
        }
        dialog.classList.add('is-closing');
        dialogTimer = setTimeout(() => dialog.close(), 180);
    }
    if (dialog) {
        document.querySelectorAll('[data-gallery-open]').forEach(button => {
            button.addEventListener('click', () => {
                clearTimeout(dialogTimer);
                dialogTrigger = button;
                dialog.classList.remove('is-closing', 'is-opening');
                const image = dialog.querySelector('[data-gallery-image]');
                delete image.dataset.fallback;
                image.src = button.dataset.image;
                image.alt = button.dataset.title;
                dialog.querySelector('[data-gallery-title]').textContent = button.dataset.title;
                dialog.querySelector('[data-gallery-description]').textContent = button.dataset.description;
                if (!dialog.open) dialog.showModal();
                if (motionEnabled()) dialog.classList.add('is-opening');
            });
        });
        dialog.querySelector('[data-gallery-close]')?.addEventListener('click', closeGallery);
        dialog.addEventListener('cancel', event => {
            event.preventDefault();
            closeGallery();
        });
        dialog.addEventListener('click', event => {
            if (event.target !== dialog) return;
            const bounds = dialog.getBoundingClientRect();
            if (event.clientX < bounds.left || event.clientX > bounds.right ||
                event.clientY < bounds.top || event.clientY > bounds.bottom) closeGallery();
        });
        dialog.addEventListener('close', () => {
            clearTimeout(dialogTimer);
            dialog.classList.remove('is-closing', 'is-opening');
            if (dialogTrigger?.isConnected) dialogTrigger.focus({ preventScroll: true });
        });
    }

    document.querySelectorAll('form[data-confirm]').forEach(form => {
        form.addEventListener('submit', event => {
            if (!window.confirm(form.dataset.confirm)) event.preventDefault();
        });
    });
    document.querySelectorAll('form').forEach(form => {
        form.addEventListener('submit', event => {
            if (event.defaultPrevented || !form.checkValidity()) return;
            const submit = event.submitter;
            if (submit && !form.hasAttribute('data-confirm')) {
                submit.dataset.submitPending = 'true';
                submit.disabled = true;
                submit.setAttribute('aria-busy', 'true');
            }
        });
    });

    // Internal links keep their native URL, modifier keys and new-tab behaviour.
    let navigationTimer;
    document.addEventListener('click', event => {
        if (event.defaultPrevented || event.button !== 0 || event.metaKey || event.ctrlKey ||
            event.shiftKey || event.altKey || !motionEnabled()) return;
        const link = event.target.closest('a[href]');
        if (!link || link.hasAttribute('download') || (link.target && link.target !== '_self')) return;
        const destination = new URL(link.href, location.href);
        if (!['http:', 'https:'].includes(destination.protocol) || destination.origin !== location.origin) return;
        if (destination.pathname === location.pathname && destination.search === location.search) return;
        event.preventDefault();
        clearTimeout(navigationTimer);
        root.classList.add('is-leaving');
        navigationTimer = setTimeout(() => location.assign(destination.href), 160);
    });

    window.addEventListener('pageshow', () => {
        clearTimeout(navigationTimer);
        root.classList.remove('is-leaving');
        document.querySelectorAll('[data-submit-pending]').forEach(button => {
            button.disabled = false;
            button.removeAttribute('aria-busy');
            delete button.dataset.submitPending;
        });
    });

    document.querySelectorAll('img').forEach(image => {
        const fallback = () => {
            if (image.dataset.fallback) return;
            image.dataset.fallback = 'true';
            image.src = new URL('uploads/Gambar1.jpg', document.querySelector('link[rel="stylesheet"]').href.split('css/')[0]).href;
        };
        image.addEventListener('error', fallback);
        if (image.getAttribute('src') && image.complete && image.naturalWidth === 0) fallback();
    });
});


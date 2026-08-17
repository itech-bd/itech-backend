import './bootstrap';
import './auth-modals';
import './recaptcha-forms';

import Alpine from 'alpinejs';

function forceLightExperience() {
    document.documentElement.classList.remove('dark');
    document.documentElement.dataset.theme = 'light';
    document.documentElement.style.colorScheme = 'light';

    try {
        window.localStorage?.setItem('theme', 'light');
    } catch (_) {
        // Ignore storage errors.
    }
}

forceLightExperience();

function keepDashboardSidebarInPlace() {
    const sidebars = document.querySelectorAll('[data-dashboard-sidebar-scroll]');

    if (!sidebars.length) {
        return;
    }

    const currentPath = window.location.pathname.replace(/\/+$/, '') || '/';

    sidebars.forEach((sidebar) => {
        const sidebarName = sidebar.getAttribute('data-dashboard-sidebar-scroll') || 'default';
        const storageKey = `itech:dashboard-sidebar-scroll:${sidebarName}`;
        const savedScrollTop = Number(window.sessionStorage?.getItem(storageKey));

        if (Number.isFinite(savedScrollTop) && savedScrollTop > 0) {
            sidebar.scrollTop = savedScrollTop;
        }

        const links = Array.from(sidebar.querySelectorAll('a[href]'));
        const activeLink = links.reduce((bestMatch, link) => {
            const linkPath = new URL(link.getAttribute('href'), window.location.origin).pathname.replace(/\/+$/, '') || '/';
            const isMatch = currentPath === linkPath || (linkPath !== '/' && currentPath.startsWith(`${linkPath}/`));

            if (!isMatch) {
                return bestMatch;
            }

            if (!bestMatch || linkPath.length > bestMatch.path.length) {
                return { link, path: linkPath };
            }

            return bestMatch;
        }, null)?.link;

        if (activeLink) {
            const sidebarRect = sidebar.getBoundingClientRect();
            const linkRect = activeLink.getBoundingClientRect();
            const topOffset = linkRect.top - sidebarRect.top;
            const bottomOffset = linkRect.bottom - sidebarRect.bottom;

            if (topOffset < 20 || bottomOffset > -20) {
                activeLink.scrollIntoView({ block: 'center' });
            }
        }

        sidebar.addEventListener('click', (event) => {
            const link = event.target.closest('a[href]');

            if (link) {
                window.sessionStorage?.setItem(storageKey, String(sidebar.scrollTop));
            }
        });
    });
}

keepDashboardSidebarInPlace();

window.Alpine = Alpine;

Alpine.data('batchRotator', (batches = []) => ({
    batches: Array.isArray(batches) ? batches : [],
    currentIndex: 0,
    timerId: null,

    get currentBatch() {
        return this.batches[this.currentIndex] ?? null;
    },

    init() {
        if (this.batches.length <= 1) {
            return;
        }

        this.timerId = window.setInterval(() => {
            this.currentIndex = (this.currentIndex + 1) % this.batches.length;
        }, 3000);
    },

    destroy() {
        if (this.timerId) {
            window.clearInterval(this.timerId);
            this.timerId = null;
        }
    },
}));

Alpine.data('courseCardRotator', (total = 0) => ({
    total: Number.isFinite(Number(total)) ? Number(total) : 0,
    currentIndex: 0,
    timerId: null,
    frameId: null,
    resizeHandler: null,

    init() {
        this.$nextTick(() => {
            this.syncHeight();
        });

        this.resizeHandler = () => this.syncHeight();
        window.addEventListener('resize', this.resizeHandler);

        if (this.total <= 1) {
            return;
        }

        this.timerId = window.setInterval(() => {
            this.currentIndex = (this.currentIndex + 1) % this.total;
        }, 3000);
    },

    isActive(index) {
        return this.currentIndex === index;
    },

    syncHeight() {
        if (!this.$refs.cardsWrap) {
            return;
        }

        if (this.frameId) {
            window.cancelAnimationFrame(this.frameId);
        }

        this.frameId = window.requestAnimationFrame(() => {
            const cards = this.$refs.cardsWrap.querySelectorAll('[data-hero-course-card]');
            let maxHeight = 0;

            cards.forEach((card) => {
                const height = card.offsetHeight;
                if (height > maxHeight) {
                    maxHeight = height;
                }
            });

            if (maxHeight > 0) {
                this.$refs.cardsWrap.style.minHeight = `${maxHeight}px`;
            }
        });
    },

    destroy() {
        if (this.timerId) {
            window.clearInterval(this.timerId);
            this.timerId = null;
        }

        if (this.frameId) {
            window.cancelAnimationFrame(this.frameId);
            this.frameId = null;
        }

        if (this.resizeHandler) {
            window.removeEventListener('resize', this.resizeHandler);
            this.resizeHandler = null;
        }
    },
}));

Alpine.start();

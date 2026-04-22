import Alpine from 'alpinejs';

window.Alpine = Alpine;

const prefersReducedMotion = () =>
    window.matchMedia('(prefers-reduced-motion: reduce)').matches;

// Reviews page: stats strip count-up animation. Each entry in `targets`
// is { ref: 'avg' | 'total' | 'pct', value: number, suffix?: string }.
Alpine.data('countUpStats', (targets = []) => ({
    reduced: prefersReducedMotion(),
    init() {
        // x-intersect.once on the parent fires runStats() — keep init small.
    },
    runStats() {
        targets.forEach(({ ref, value, suffix = '' }) => {
            const el = this.$refs[ref];
            if (!el) return;
            this.tween(el, value, suffix);
        });
    },
    tween(el, target, suffix) {
        if (this.reduced) {
            el.textContent = (Number.isInteger(target) ? target : target.toFixed(1)) + suffix;
            return;
        }
        let current = 0;
        const step = target / 30;
        const interval = setInterval(() => {
            current = Math.min(current + step, target);
            el.textContent = (Number.isInteger(target) ? Math.round(current) : current.toFixed(1)) + suffix;
            if (current >= target) clearInterval(interval);
        }, 30);
    },
}));

// Reviews page: rating distribution bars animate from 0% to data-pct on first scroll.
Alpine.data('ratingBars', () => ({
    reduced: prefersReducedMotion(),
    init() {
        if (this.reduced) return;
        this.$el.querySelectorAll('.rating-bar-fill').forEach((b) => {
            b.style.width = '0%';
        });
    },
    animate() {
        if (this.reduced) return;
        this.$el.querySelectorAll('.rating-bar-fill').forEach((b) => {
            b.style.width = `${b.dataset.pct}%`;
        });
    },
}));

// Reviews page: per-card fade-up on first scroll into view, staggered by index.
Alpine.data('reviewCardFadeIn', (index = 0) => ({
    reduced: prefersReducedMotion(),
    init() {
        if (this.reduced) return;
        this.$el.style.opacity = '0';
        this.$el.style.transform = 'translateY(1rem)';
        this.$el.style.transition = 'opacity 0.5s ease-out, transform 0.5s ease-out';
    },
    show() {
        if (this.reduced) return;
        this.$el.style.transitionDelay = `${index * 75}ms`;
        this.$el.style.opacity = '1';
        this.$el.style.transform = 'translateY(0)';
    },
}));

Alpine.start();

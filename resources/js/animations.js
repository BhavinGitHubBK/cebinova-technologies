import { gsap } from 'gsap';
import { ScrollTrigger } from 'gsap/ScrollTrigger';

gsap.registerPlugin(ScrollTrigger);

function fillProgress(id) {
    const el = document.getElementById(id);
    if (el) {
        el.style.transform = 'scaleX(1)';
    }
}

function initCatalogFilters() {
    document.querySelectorAll('[data-filter-group]').forEach((group) => {
        const buttons = group.querySelectorAll('[data-filter]');
        const items = document.querySelectorAll(group.dataset.filterGroup);

        buttons.forEach((button) => {
            button.addEventListener('click', () => {
                const value = button.dataset.filter;
                buttons.forEach((item) => item.classList.toggle('is-active', item === button));
                items.forEach((item) => {
                    item.hidden = value !== 'all' && item.dataset.category !== value;
                });
            });
        });
    });
}

export function initAnimations() {
    const reduce = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    initCatalogFilters();

    if (reduce) {
        fillProgress('journey-progress');
        fillProgress('process-progress');
        document.querySelectorAll('[data-journey-node]').forEach((node) => node.classList.add('is-active'));
        return;
    }

    const heroItems = document.querySelectorAll('[data-hero-item]');

    if (heroItems.length) {
        gsap.from(heroItems, {
            opacity: 0,
            y: 20,
            duration: 0.62,
            stagger: 0.09,
            ease: 'power2.out',
            clearProps: 'all',
        });
    }

    const heroVisual = document.querySelector('[data-hero-visual]');

    if (heroVisual) {
        gsap.from(heroVisual, {
            opacity: 0,
            scale: 0.97,
            duration: 0.8,
            delay: 0.18,
            ease: 'power2.out',
            clearProps: 'all',
        });
    }

    const trustHead = document.querySelector('.trust-rail-head');
    const trustCards = document.querySelectorAll('.trust-rail-cell');

    if (trustHead) {
        gsap.from(trustHead, {
            scrollTrigger: {
                trigger: '.trust-rail',
                start: 'top 86%',
                once: true,
            },
            opacity: 0,
            y: 18,
            duration: 0.55,
            ease: 'power2.out',
            clearProps: 'all',
        });
    }

    if (trustCards.length) {
        gsap.from(trustCards, {
            scrollTrigger: {
                trigger: '.trust-rail',
                start: 'top 86%',
                once: true,
            },
            opacity: 0,
            y: 18,
            duration: 0.58,
            stagger: 0.06,
            ease: 'power2.out',
            clearProps: 'all',
        });
    }

    gsap.from('.js-orbit-node', {
        opacity: 0.35,
        duration: 0.55,
        stagger: 0.08,
        delay: 0.28,
        ease: 'power2.out',
    });

    gsap.utils.toArray('[data-reveal]').forEach((el) => {
        gsap.from(el, {
            scrollTrigger: {
                trigger: el,
                start: 'top 84%',
                once: true,
            },
            y: 24,
            duration: 0.65,
            ease: 'power2.out',
            clearProps: 'transform',
        });
    });

    gsap.utils.toArray('[data-stagger]').forEach((group) => {
        const items = group.children;
        if (!items.length) {
            return;
        }

        gsap.from(items, {
            scrollTrigger: {
                trigger: group,
                start: 'top 84%',
                once: true,
            },
            y: 20,
            opacity: 0,
            duration: 0.5,
            stagger: 0.06,
            ease: 'power2.out',
            clearProps: 'all',
        });
    });

    const journey = document.getElementById('journey');
    const journeyProgress = document.getElementById('journey-progress');
    const journeyNodes = document.querySelectorAll('[data-journey-node]');
    if (journey && journeyProgress) {
        gsap.to(journeyProgress, {
            scaleX: 1,
            ease: 'none',
            scrollTrigger: {
                trigger: journey,
                start: 'top 75%',
                end: 'top 28%',
                scrub: 0.6,
                onUpdate: (self) => {
                    const count = journeyNodes.length;
                    const active = Math.min(count - 1, Math.floor(self.progress * count));
                    journeyNodes.forEach((node, index) => {
                        node.classList.toggle('is-active', index <= active);
                    });
                },
            },
        });
    }

    const process = document.getElementById('process');
    const processProgress = document.getElementById('process-progress');
    if (process && processProgress) {
        gsap.to(processProgress, {
            scaleX: 1,
            ease: 'none',
            scrollTrigger: {
                trigger: process,
                start: 'top 75%',
                end: 'top 40%',
                scrub: 0.6,
            },
        });
    }

    document.querySelectorAll('[data-ai-block]').forEach((block) => {
        const setActive = (on) => block.classList.toggle('is-active', on);
        block.addEventListener('mouseenter', () => setActive(true));
        block.addEventListener('mouseleave', () => setActive(false));
        block.addEventListener('focus', () => setActive(true));
        block.addEventListener('blur', () => setActive(false));
    });
}

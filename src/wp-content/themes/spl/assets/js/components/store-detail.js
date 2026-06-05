/**
 * Store Detail — Gallery Slider + Lightbox
 * Uses the theme's bundled Swiper (ES module)
 */
import { S as Swiper, N as Navigation, T as Thumbs, P as Pagination, f as FreeMode, a as Keyboard } from '../vendor-swiper.js';

document.addEventListener('DOMContentLoaded', () => {

    // ── Gallery Thumbs ──
    let thumbsSwiper = null;
    const thumbsEl = document.getElementById('sd-gallery-thumbs');
    if (thumbsEl) {
        thumbsSwiper = new Swiper(thumbsEl, {
            modules: [FreeMode],
            spaceBetween: 8,
            slidesPerView: 5,
            freeMode: true,
            watchSlidesProgress: true,
            breakpoints: {
                0:    { slidesPerView: 4, spaceBetween: 6 },
                640:  { slidesPerView: 5, spaceBetween: 8 },
                1024: { slidesPerView: 5, spaceBetween: 8 }
            }
        });
    }

    // ── Gallery Main ──
    const mainEl = document.getElementById('sd-gallery-main');
    let mainSwiper = null;
    if (mainEl) {
        const mainOpts = {
            modules: [Navigation, Thumbs, Keyboard],
            spaceBetween: 0,
            loop: false,
            navigation: {
                nextEl: mainEl.querySelector('.swiper-button-next'),
                prevEl: mainEl.querySelector('.swiper-button-prev'),
            },
            keyboard: { enabled: true },
        };
        if (thumbsSwiper) mainOpts.thumbs = { swiper: thumbsSwiper };
        mainSwiper = new Swiper(mainEl, mainOpts);
    }

    // ── Lightbox ──
    const lightbox   = document.getElementById('sd-lightbox');
    const lbSwiperEl = document.getElementById('sd-lightbox-swiper');
    const lbCounter  = document.getElementById('sd-lb-current');
    let lbSwiper     = null;

    function openLightbox(index) {
        if (!lightbox || !lbSwiperEl) return;
        lightbox.hidden = false;
        document.body.style.overflow = 'hidden';

        if (!lbSwiper) {
            lbSwiper = new Swiper(lbSwiperEl, {
                modules: [Navigation, Pagination, Keyboard],
                spaceBetween: 0,
                initialSlide: index || 0,
                navigation: {
                    nextEl: lbSwiperEl.querySelector('.swiper-button-next'),
                    prevEl: lbSwiperEl.querySelector('.swiper-button-prev'),
                },
                pagination: { el: lbSwiperEl.querySelector('.swiper-pagination'), clickable: true },
                keyboard: { enabled: true },
                on: {
                    slideChange: function() {
                        if (lbCounter) lbCounter.textContent = this.activeIndex + 1;
                    }
                }
            });
        } else {
            lbSwiper.slideTo(index || 0, 0);
        }
        if (lbCounter) lbCounter.textContent = (index || 0) + 1;
        setTimeout(() => lightbox.querySelector('.sd__lightbox-close')?.focus(), 100);
    }

    function closeLightbox() {
        if (!lightbox) return;
        lightbox.hidden = true;
        document.body.style.overflow = '';
    }

    // Click zoom buttons → open lightbox
    document.querySelectorAll('.sd__gallery-zoom').forEach(btn => {
        btn.addEventListener('click', () => openLightbox(parseInt(btn.dataset.index) || 0));
    });

    // Close button
    lightbox?.querySelector('.sd__lightbox-close')?.addEventListener('click', closeLightbox);

    // Click backdrop
    lightbox?.addEventListener('click', (e) => {
        if (e.target === lightbox) closeLightbox();
    });

    // Escape key
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && lightbox && !lightbox.hidden) closeLightbox();
    });
});

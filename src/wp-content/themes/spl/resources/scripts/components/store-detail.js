/**
 * Store Detail — Gallery Slider + Lightbox
 * Initializes Swiper instances for:
 *   1. Main gallery slider with thumbnail navigation
 *   2. Fullscreen lightbox popup with slider
 */
import Swiper from "swiper";
import {
  Navigation,
  Pagination,
  Thumbs,
  FreeMode,
  Keyboard,
} from "swiper/modules";

// ── Gallery Thumbs ──
const initGalleryThumbs = () => {
  const thumbsEl = document.getElementById("sd-gallery-thumbs");
  if (!thumbsEl) return null;

  return new Swiper(thumbsEl, {
    modules: [FreeMode],
    spaceBetween: 8,
    slidesPerView: 5,
    freeMode: true,
    watchSlidesProgress: true,
    breakpoints: {
      0: { slidesPerView: 4, spaceBetween: 6 },
      640: { slidesPerView: 5, spaceBetween: 8 },
      1024: { slidesPerView: 5, spaceBetween: 8 },
    },
  });
};

// ── Gallery Main ──
const initGalleryMain = (thumbsSwiper) => {
  const mainEl = document.getElementById("sd-gallery-main");
  if (!mainEl) return null;

  const opts = {
    modules: [Navigation, Thumbs, Keyboard],
    spaceBetween: 0,
    loop: false,
    navigation: {
      nextEl: mainEl.querySelector(".swiper-button-next"),
      prevEl: mainEl.querySelector(".swiper-button-prev"),
    },
    keyboard: { enabled: true },
  };

  if (thumbsSwiper) {
    opts.thumbs = { swiper: thumbsSwiper };
  }

  return new Swiper(mainEl, opts);
};

// ── Lightbox ──
const initLightbox = () => {
  const lightbox = document.getElementById("sd-lightbox");
  const lbSwiperEl = document.getElementById("sd-lightbox-swiper");
  const lbCounter = document.getElementById("sd-lb-current");

  if (!lightbox || !lbSwiperEl) return;

  let lbSwiper = null;

  const openLightbox = (index) => {
    lightbox.hidden = false;
    document.body.style.overflow = "hidden";

    if (!lbSwiper) {
      lbSwiper = new Swiper(lbSwiperEl, {
        modules: [Navigation, Pagination, Keyboard],
        spaceBetween: 0,
        initialSlide: index || 0,
        navigation: {
          nextEl: lbSwiperEl.querySelector(".swiper-button-next"),
          prevEl: lbSwiperEl.querySelector(".swiper-button-prev"),
        },
        pagination: {
          el: lbSwiperEl.querySelector(".swiper-pagination"),
          clickable: true,
        },
        keyboard: { enabled: true },
        on: {
          slideChange: function () {
            if (lbCounter) lbCounter.textContent = this.activeIndex + 1;
          },
        },
      });
    } else {
      lbSwiper.slideTo(index || 0, 0);
    }

    if (lbCounter) lbCounter.textContent = (index || 0) + 1;

    // Focus trap
    setTimeout(() => {
      lightbox.querySelector(".sd__lightbox-close")?.focus();
    }, 100);
  };

  const closeLightbox = () => {
    lightbox.hidden = true;
    document.body.style.overflow = "";
  };

  // Click zoom buttons → open lightbox
  document.querySelectorAll(".sd__gallery-zoom").forEach((btn) => {
    btn.addEventListener("click", () => {
      openLightbox(parseInt(btn.dataset.index) || 0);
    });
  });

  // Close button
  lightbox
    .querySelector(".sd__lightbox-close")
    ?.addEventListener("click", closeLightbox);

  // Click backdrop
  lightbox.addEventListener("click", (e) => {
    if (e.target === lightbox) closeLightbox();
  });

  // Escape key
  document.addEventListener("keydown", (e) => {
    if (e.key === "Escape" && !lightbox.hidden) closeLightbox();
  });
};

// ── Init ──
document.addEventListener("DOMContentLoaded", () => {
  const gallery = document.getElementById("sd-gallery");
  if (!gallery) return;

  const thumbsSwiper = initGalleryThumbs();
  initGalleryMain(thumbsSwiper);
  initLightbox();
});

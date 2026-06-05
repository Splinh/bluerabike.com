import { S as Swiper, f as freeMode, N as Navigation, T as Thumb, P as Pagination } from "../vendor-swiper.js";
const initGalleryThumbs = () => {
  const thumbsEl = document.querySelector(".sd__gallery-thumbs");
  if (!thumbsEl) return null;
  return new Swiper(thumbsEl, {
    modules: [freeMode],
    spaceBetween: 8,
    slidesPerView: 5,
    freeMode: true,
    watchSlidesProgress: true,
    observer: true,
    observeParents: true,
    breakpoints: {
      0: { slidesPerView: 4, spaceBetween: 6 },
      640: { slidesPerView: 5, spaceBetween: 8 },
      1024: { slidesPerView: 5, spaceBetween: 8 }
    }
  });
};
const initGalleryMain = (thumbsSwiper) => {
  const mainEl = document.querySelector(".sd__gallery-main");
  if (!mainEl) return null;
  const opts = {
    modules: [Navigation, Thumb],
    slidesPerView: 1,
    spaceBetween: 0,
    loop: false,
    observer: true,
    observeParents: true,
    grabCursor: true,
    navigation: {
      nextEl: mainEl.querySelector(".swiper-button-next"),
      prevEl: mainEl.querySelector(".swiper-button-prev")
    }
  };
  if (thumbsSwiper) {
    opts.thumbs = { swiper: thumbsSwiper };
  }
  return new Swiper(mainEl, opts);
};
const initLightbox = () => {
  var _a;
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
        modules: [Navigation, Pagination],
        slidesPerView: 1,
        spaceBetween: 0,
        initialSlide: index || 0,
        observer: true,
        observeParents: true,
        navigation: {
          nextEl: lbSwiperEl.querySelector(".swiper-button-next"),
          prevEl: lbSwiperEl.querySelector(".swiper-button-prev")
        },
        pagination: {
          el: lbSwiperEl.querySelector(".swiper-pagination"),
          clickable: true
        },
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
    setTimeout(() => {
      var _a2;
      (_a2 = lightbox.querySelector(".sd__lightbox-close")) == null ? void 0 : _a2.focus();
    }, 100);
  };
  const closeLightbox = () => {
    lightbox.hidden = true;
    document.body.style.overflow = "";
  };
  document.querySelectorAll(".sd__gallery-zoom").forEach((btn) => {
    btn.addEventListener("click", () => {
      openLightbox(parseInt(btn.dataset.index) || 0);
    });
  });
  (_a = lightbox.querySelector(".sd__lightbox-close")) == null ? void 0 : _a.addEventListener("click", closeLightbox);
  lightbox.addEventListener("click", (e) => {
    if (e.target === lightbox) closeLightbox();
  });
  document.addEventListener("keydown", (e) => {
    if (e.key === "Escape" && !lightbox.hidden) closeLightbox();
  });
};
document.addEventListener("DOMContentLoaded", () => {
  const gallery = document.getElementById("sd-gallery");
  if (!gallery) return;
  const thumbsSwiper = initGalleryThumbs();
  initGalleryMain(thumbsSwiper);
  initLightbox();
});
//# sourceMappingURL=store-detail.js.map

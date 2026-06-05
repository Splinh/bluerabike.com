import { S as Swiper, N as Navigation, P as Pagination, a as Scrollbar, A as Autoplay, T as Thumb, G as Grid, f as freeMode, E as EffectFade } from "../vendor-swiper.js";
const urlAlphabet = "useandom-26T198340PX75pxJACKVERYMINDBUSHWOLF_GQZbfghjklqvwyzrict";
let nanoid = (size = 21) => {
  let id = "";
  let bytes = crypto.getRandomValues(new Uint8Array(size |= 0));
  while (size--) {
    id += urlAlphabet[bytes[size] & 63];
  }
  return id;
};
function isEmpty(value) {
  if (value == null) return true;
  if (Array.isArray(value) || typeof value === "string")
    return value.length === 0;
  if (typeof value === "object") return Object.keys(value).length === 0;
  return false;
}
const initializeSwiper = (el, swiper_class, options) => {
  if (!(el instanceof Element)) {
    console.error("Error: The provided element is not a DOM element.");
    return;
  }
  const swiper = new Swiper(swiper_class, options);
  el.addEventListener("mouseover", () => {
    swiper.autoplay.stop();
  });
  el.addEventListener("mouseout", () => {
    if (options.autoplay) {
      swiper.autoplay.start();
    }
  });
  return swiper;
};
const generateClasses = () => {
  const rand = nanoid(9);
  return {
    rand,
    swiperClass: "swiper-" + rand,
    nextClass: "next-" + rand,
    prevClass: "prev-" + rand,
    paginationClass: "pagination-" + rand,
    scrollbarClass: "scrollbar-" + rand
  };
};
const getDefaultOptions = () => ({
  modules: [
    Navigation,
    Pagination,
    Scrollbar,
    Autoplay,
    Thumb,
    Grid,
    freeMode,
    EffectFade
  ],
  grabCursor: true,
  allowTouchMove: true,
  threshold: 5,
  hashNavigation: false,
  mousewheel: false,
  wrapperClass: "swiper-wrapper",
  slideClass: "swiper-slide",
  slideActiveClass: "swiper-slide-active"
});
const random = (min, max) => Math.floor(Math.random() * (max - min + 1)) + min;
const initializeSwipers = () => {
  const swiperElements = document.querySelectorAll(".w-swiper");
  swiperElements.forEach((el, index) => {
    var _a;
    const classes = generateClasses();
    el.classList.add(classes.swiperClass);
    let controls = (_a = el.closest(".swiper-section")) == null ? void 0 : _a.querySelector(".swiper-controls");
    if (!controls) {
      controls = document.createElement("div");
      controls.classList.add("swiper-controls");
      el.after(controls);
    }
    const swiperWrapper = el == null ? void 0 : el.querySelector(".swiper-wrapper");
    let options = JSON.parse(swiperWrapper.dataset.options) || {};
    if (isEmpty(options)) {
      options = {
        autoview: true,
        autoplay: true,
        navigation: true
      };
    }
    let swiperOptions = { ...getDefaultOptions() };
    if (options.autoview) {
      swiperOptions.slidesPerView = "auto";
      if (options.gap) {
        swiperOptions.spaceBetween = 10;
        swiperOptions.breakpoints = {
          0: options.mobile || {},
          768: options.tablet || { spaceBetween: 20 },
          1024: options.desktop || {}
        };
      } else if (options.smallgap) {
        swiperOptions.spaceBetween = parseInt(options.smallgap);
      }
    }
    if (options.mobile || options.tablet || options.desktop) {
      swiperOptions.breakpoints = {
        0: options.mobile || {},
        768: options.tablet || {},
        1024: options.desktop || {}
      };
    }
    if (options.observer) {
      swiperOptions.observer = true;
      swiperOptions.observeParents = true;
    }
    if (options.effect) {
      swiperOptions.effect = String(options.effect);
      if (swiperOptions.effect === "fade") {
        swiperOptions.fadeEffect = { crossFade: true };
      }
    }
    if (options.spaceBetween)
      swiperOptions.spaceBetween = parseInt(options.spaceBetween);
    if (options.slidesPerGroup)
      swiperOptions.slidesPerGroup = parseInt(options.slidesPerGroup);
    if (options.autoheight) swiperOptions.autoHeight = true;
    if (options.loop) swiperOptions.loop = true;
    if (options.parallax) swiperOptions.parallax = true;
    if (options.direction) swiperOptions.direction = String(options.direction);
    if (options.freemode) swiperOptions.freeMode = true;
    if (options.cssmode) swiperOptions.cssMode = true;
    if (options.centered) {
      swiperOptions.centeredSlides = true;
      swiperOptions.centeredSlidesBounds = true;
    }
    swiperOptions.speed = options.speed ? parseInt(options.speed) : random(300, 900);
    if (options.autoplay) {
      swiperOptions.autoplay = {
        disableOnInteraction: false,
        delay: options.delay ? parseInt(options.delay) : random(4e3, 6e3)
      };
      if (options.reverse) swiperOptions.reverseDirection = true;
    }
    if (options.navigation) {
      const section = el.closest(".swiper-section");
      let btnPrev = section == null ? void 0 : section.querySelector(".swiper-button-prev");
      let btnNext = section == null ? void 0 : section.querySelector(".swiper-button-next");
      if (btnPrev && btnNext) {
        btnPrev.classList.add(classes.prevClass);
        btnNext.classList.add(classes.nextClass);
        if (!btnPrev.hasAttribute("aria-label")) {
          btnPrev.setAttribute("aria-label", "Slide trước");
        }
        if (!btnNext.hasAttribute("aria-label")) {
          btnNext.setAttribute("aria-label", "Slide tiếp theo");
        }
      } else {
        btnPrev = document.createElement("button");
        btnNext = document.createElement("button");
        btnPrev.type = "button";
        btnNext.type = "button";
        btnPrev.setAttribute("aria-label", "Slide trước");
        btnNext.setAttribute("aria-label", "Slide tiếp theo");
        btnPrev.classList.add(
          "swiper-button",
          "swiper-button-prev",
          classes.prevClass
        );
        btnNext.classList.add(
          "swiper-button",
          "swiper-button-next",
          classes.nextClass
        );
        const svgPrev = `
      <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" aria-hidden="true" focusable="false">
        <path d="M9.4 233.4c-12.5 12.5-12.5 32.8 0 45.3l160 160c12.5 12.5 
        32.8 12.5 45.3 0s12.5-32.8 0-45.3L109.3 288 480 288c17.7 0 32-14.3 
        32-32s-14.3-32-32-32l-370.7 0 105.4-105.4c12.5-12.5 12.5-32.8 
        0-45.3s-32.8-12.5-45.3 0l-160 160z"/>
      </svg>`;
        const svgNext = `
      <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" aria-hidden="true" focusable="false">
        <path d="M502.6 278.6c12.5-12.5 12.5-32.8 0-45.3l-160-160c-12.5-12.5-
        32.8-12.5-45.3 0s-12.5 32.8 0 45.3L402.7 224 32 224c-17.7 0-32 
        14.3-32 32s14.3 32 32 32l370.7 0-105.4 105.4c-12.5 12.5-12.5 
        32.8 0 45.3s32.8 12.5 45.3 0l160-160z"/>
      </svg>`;
        btnPrev.innerHTML = svgPrev;
        btnNext.innerHTML = svgNext;
        controls.append(btnPrev, btnNext);
      }
      swiperOptions.navigation = {
        nextEl: "." + classes.nextClass,
        prevEl: "." + classes.prevClass
      };
    }
    if (options.pagination) {
      const section = el.closest(".swiper-section");
      let pagination = section == null ? void 0 : section.querySelector(".swiper-pagination");
      if (pagination) {
        pagination.classList.add(classes.paginationClass);
      } else {
        pagination = document.createElement("div");
        pagination.classList.add("swiper-pagination", classes.paginationClass);
        controls.appendChild(pagination);
      }
      const paginationType = options.pagination;
      swiperOptions.pagination = {
        el: "." + classes.paginationClass,
        clickable: true,
        ...paginationType === "bullets" && {
          dynamicBullets: true,
          type: "bullets"
        },
        ...paginationType === "fraction" && { type: "fraction" },
        ...paginationType === "progressbar" && { type: "progressbar" },
        ...paginationType === "custom" && {
          renderBullet: (index2, className) => `<span class="${className}">${index2 + 1}</span>`
        }
      };
    }
    if (options.scrollbar) {
      const section = el.closest(".swiper-section");
      let scrollbar = section == null ? void 0 : section.querySelector(".swiper-scrollbar");
      if (scrollbar) {
        scrollbar.classList.add(classes.scrollbarClass);
      } else {
        scrollbar = document.createElement("div");
        scrollbar.classList.add("swiper-scrollbar", classes.scrollbarClass);
        controls.appendChild(scrollbar);
      }
      swiperOptions.scrollbar = {
        el: "." + classes.scrollbarClass,
        hide: true,
        draggable: true
      };
    }
    if (options.marquee) {
      swiperOptions.centeredSlides = false;
      swiperOptions.autoplay = {
        delay: 1,
        disableOnInteraction: true
      };
      swiperOptions.loop = true;
      swiperOptions.speed = 6e3;
      swiperOptions.allowTouchMove = true;
    }
    if (options.rows) {
      swiperOptions.direction = "horizontal";
      swiperOptions.loop = false;
      swiperOptions.grid = {
        rows: parseInt(options.rows),
        fill: "row"
      };
    }
    initializeSwiper(el, "." + classes.swiperClass, swiperOptions);
  });
};
const spgSwipers = () => {
  const swiperElements = document.querySelectorAll(".swiper-product-gallery");
  swiperElements.forEach((el, index) => {
    const classes = generateClasses();
    el.classList.add(classes.swiperClass);
    const w_images = el == null ? void 0 : el.querySelector(".swiper-images");
    const w_thumbs = el == null ? void 0 : el.querySelector(".swiper-thumbs");
    let swiper_images = false;
    let swiper_thumbs = false;
    if (w_thumbs) {
      w_thumbs == null ? void 0 : w_thumbs.querySelector(".swiper-button-prev").classList.add("prev-thumbs-" + classes.rand);
      w_thumbs == null ? void 0 : w_thumbs.querySelector(".swiper-button-next").classList.add("next-thumbs-" + classes.rand);
      w_thumbs.classList.add("thumbs-" + classes.rand);
      let thumbs_options = { ...getDefaultOptions() };
      thumbs_options.breakpoints = {
        0: {
          spaceBetween: 5,
          slidesPerView: 4
        },
        768: {
          spaceBetween: 10,
          slidesPerView: 5
        },
        1024: {
          spaceBetween: 10,
          slidesPerView: 6
        }
      };
      thumbs_options.navigation = {
        prevEl: ".prev-thumbs-" + classes.rand,
        nextEl: ".next-thumbs-" + classes.rand
      };
      swiper_thumbs = initializeSwiper(
        w_thumbs,
        ".thumbs-" + classes.rand,
        thumbs_options
      );
    }
    if (w_images) {
      w_images == null ? void 0 : w_images.querySelector(".swiper-button-prev").classList.add("prev-images-" + classes.rand);
      w_images == null ? void 0 : w_images.querySelector(".swiper-button-next").classList.add("next-images-" + classes.rand);
      w_images.classList.add("images-" + classes.rand);
      let images_options = { ...getDefaultOptions() };
      images_options.slidesPerView = "auto";
      images_options.spaceBetween = 10;
      images_options.watchSlidesProgress = true;
      images_options.navigation = {
        prevEl: ".prev-images-" + classes.rand,
        nextEl: ".next-images-" + classes.rand
      };
      if (swiper_thumbs) {
        images_options.thumbs = {
          swiper: swiper_thumbs
        };
      }
      swiper_images = initializeSwiper(
        w_images,
        ".images-" + classes.rand,
        images_options
      );
    }
    let firstImage = w_images == null ? void 0 : w_images.querySelector(".swiper-images-first img");
    firstImage.removeAttribute("srcset");
    let firstImageSrc = firstImage.getAttribute("src");
    let imagePopupSrc = w_images == null ? void 0 : w_images.querySelector(
      ".swiper-images-first .image-popup"
    );
    let firstThumb = false;
    let firstThumbSrc = false;
    let dataLargeImage = false;
    if (swiper_thumbs) {
      firstThumb = w_thumbs == null ? void 0 : w_thumbs.querySelector(".swiper-thumbs-first img");
      firstThumb.removeAttribute("srcset");
      firstThumbSrc = firstThumb.getAttribute("src");
      dataLargeImage = firstThumb.getAttribute("data-large_image");
    }
    const variations_form = jQuery("form.variations_form");
    if (variations_form) {
      variations_form.on("found_variation", function(event, variation) {
        if (variation.image.src) {
          firstImage.setAttribute("src", variation.image.src);
          imagePopupSrc.setAttribute("data-src", variation.image.full_src);
          if (swiper_thumbs) {
            firstThumb.setAttribute(
              "src",
              variation.image.gallery_thumbnail_src
            );
          }
          swiper_images.slideTo(0);
        }
      });
      variations_form.on("reset_image", function() {
        firstImage.setAttribute("src", firstImageSrc);
        imagePopupSrc.setAttribute("data-src", dataLargeImage);
        if (swiper_thumbs) {
          firstThumb.setAttribute("src", firstThumbSrc);
        }
        swiper_images.slideTo(0);
      });
    }
  });
};
const initZoomHover = () => {
  const galleries = document.querySelectorAll(".swiper-product-gallery");
  galleries.forEach((gallery) => {
    const zoomContainers = gallery.querySelectorAll(".zoom-container");
    zoomContainers.forEach((container) => {
      const img = container.querySelector("img");
      if (!img) return;
      container.addEventListener("mousemove", (e) => {
        const rect = container.getBoundingClientRect();
        const x = (e.clientX - rect.left) / rect.width * 100;
        const y = (e.clientY - rect.top) / rect.height * 100;
        container.style.setProperty("--zoom-x", `${x}%`);
        container.style.setProperty("--zoom-y", `${y}%`);
      });
      container.addEventListener("mouseleave", () => {
        container.style.removeProperty("--zoom-x");
        container.style.removeProperty("--zoom-y");
      });
    });
  });
};
const initLightbox = () => {
  const lightbox = document.getElementById("gallery-lightbox");
  if (!lightbox) return;
  const overlay = lightbox.querySelector(".lightbox-overlay");
  const closeBtn = lightbox.querySelector(".lightbox-close");
  const zoomBtn = lightbox.querySelector(".lightbox-zoom");
  const prevBtn = lightbox.querySelector(".lightbox-prev");
  const nextBtn = lightbox.querySelector(".lightbox-next");
  const imageWrapper = lightbox.querySelector(".lightbox-image-wrapper");
  const lightboxImage = lightbox.querySelector(".lightbox-image");
  const counter = lightbox.querySelector(".lightbox-counter");
  const caption = lightbox.querySelector(".lightbox-caption");
  let mediaItems = [];
  let currentIndex = 0;
  const collectMedia = () => {
    mediaItems = [];
    const gallery = document.querySelector(".swiper-product-gallery");
    if (!gallery) return;
    const imagePopups = gallery.querySelectorAll(".image-popup");
    imagePopups.forEach((popup) => {
      mediaItems.push({
        src: popup.dataset.src || popup.href,
        caption: popup.dataset.caption || "",
        type: "image",
        index: parseInt(popup.dataset.index, 10) || 0
      });
    });
    const videoPopups = gallery.querySelectorAll(".video-popup");
    videoPopups.forEach((popup) => {
      mediaItems.push({
        src: popup.dataset.src || popup.href,
        caption: "Video sản phẩm",
        type: "video",
        index: parseInt(popup.dataset.index, 10) || 0
      });
    });
    const tiktokWrappers = gallery.querySelectorAll(".gallery-tiktok-wrapper");
    tiktokWrappers.forEach((wrapper) => {
      mediaItems.push({
        src: wrapper.dataset.videoEmbed || "",
        caption: "TikTok Video",
        type: "video",
        index: mediaItems.length
      });
    });
    mediaItems.sort((a, b) => a.index - b.index);
  };
  const openLightbox = (index) => {
    collectMedia();
    if (mediaItems.length === 0) return;
    currentIndex = index;
    updateLightboxContent();
    lightbox.classList.add("is-active");
    document.body.classList.add("lightbox-open");
  };
  const closeLightbox = () => {
    lightbox.classList.remove("is-active");
    document.body.classList.remove("lightbox-open");
    const videoWrapper = imageWrapper.querySelector(".lightbox-video-wrapper");
    if (videoWrapper) {
      videoWrapper.innerHTML = "";
    }
  };
  const updateLightboxContent = () => {
    if (mediaItems.length === 0) return;
    const item = mediaItems[currentIndex];
    if (counter) {
      counter.textContent = `${currentIndex + 1}/${mediaItems.length}`;
    }
    if (caption) {
      caption.textContent = item.caption || "";
    }
    const existingIframe = imageWrapper.querySelector("iframe");
    if (existingIframe) {
      existingIframe.remove();
    }
    if (item.type === "image") {
      lightboxImage.style.display = "block";
      lightboxImage.src = item.src;
      lightboxImage.alt = item.caption || "Product image";
      const videoWrapper = imageWrapper.querySelector(
        ".lightbox-video-wrapper"
      );
      if (videoWrapper) {
        videoWrapper.remove();
      }
      if (zoomBtn) zoomBtn.style.display = "";
    } else {
      lightboxImage.style.display = "none";
      let videoWrapper = imageWrapper.querySelector(".lightbox-video-wrapper");
      if (!videoWrapper) {
        videoWrapper = document.createElement("div");
        videoWrapper.className = "lightbox-video-wrapper";
        imageWrapper.appendChild(videoWrapper);
      }
      const isTikTok = item.src.includes("tiktok.com");
      const aspectClass = isTikTok ? "is-tiktok" : "is-youtube";
      videoWrapper.className = `lightbox-video-wrapper ${aspectClass}`;
      videoWrapper.innerHTML = `
        <iframe 
          src="${item.src}" 
          frameborder="0" 
          allowfullscreen 
          allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; fullscreen"
        ></iframe>
      `;
      if (zoomBtn) zoomBtn.style.display = "none";
    }
    imageWrapper.classList.remove("is-zoomed");
  };
  const goToPrev = () => {
    currentIndex = currentIndex > 0 ? currentIndex - 1 : mediaItems.length - 1;
    updateLightboxContent();
  };
  const goToNext = () => {
    currentIndex = currentIndex < mediaItems.length - 1 ? currentIndex + 1 : 0;
    updateLightboxContent();
  };
  const toggleZoom = () => {
    imageWrapper.classList.toggle("is-zoomed");
  };
  document.addEventListener("click", (e) => {
    const imagePopup = e.target.closest(".image-popup");
    if (imagePopup) {
      e.preventDefault();
      const index = parseInt(imagePopup.dataset.index, 10) || 0;
      openLightbox(index);
    }
    const videoPopup = e.target.closest(".video-popup");
    if (videoPopup) {
      e.preventDefault();
      const index = parseInt(videoPopup.dataset.index, 10) || 0;
      openLightbox(index);
    }
    const tiktokOverlay = e.target.closest(".gallery-tiktok-overlay");
    if (tiktokOverlay) {
      e.preventDefault();
      e.stopPropagation();
      const wrapper = tiktokOverlay.closest(".gallery-tiktok-wrapper");
      if (wrapper) {
        const gallery = document.querySelector(".swiper-product-gallery");
        const allPopups = gallery.querySelectorAll(
          ".image-popup, .video-popup"
        );
        const tiktokIndex = allPopups.length;
        openLightbox(tiktokIndex);
      }
    }
  });
  if (overlay) overlay.addEventListener("click", closeLightbox);
  if (closeBtn) closeBtn.addEventListener("click", closeLightbox);
  if (zoomBtn) zoomBtn.addEventListener("click", toggleZoom);
  if (prevBtn) prevBtn.addEventListener("click", goToPrev);
  if (nextBtn) nextBtn.addEventListener("click", goToNext);
  document.addEventListener("keydown", (e) => {
    if (!lightbox.classList.contains("is-active")) return;
    switch (e.key) {
      case "Escape":
        closeLightbox();
        break;
      case "ArrowLeft":
        goToPrev();
        break;
      case "ArrowRight":
        goToNext();
        break;
    }
  });
  if (lightboxImage) {
    lightboxImage.addEventListener("click", toggleZoom);
  }
  let touchStartX = 0;
  let touchEndX = 0;
  lightbox.addEventListener(
    "touchstart",
    (e) => {
      touchStartX = e.changedTouches[0].screenX;
    },
    { passive: true }
  );
  lightbox.addEventListener(
    "touchend",
    (e) => {
      touchEndX = e.changedTouches[0].screenX;
      const diff = touchStartX - touchEndX;
      if (Math.abs(diff) > 50) {
        if (diff > 0) {
          goToNext();
        } else {
          goToPrev();
        }
      }
    },
    { passive: true }
  );
};
document.addEventListener("DOMContentLoaded", initializeSwipers);
document.addEventListener("DOMContentLoaded", spgSwipers);
document.addEventListener("DOMContentLoaded", initZoomHover);
document.addEventListener("DOMContentLoaded", initLightbox);
//# sourceMappingURL=swiper.js.map

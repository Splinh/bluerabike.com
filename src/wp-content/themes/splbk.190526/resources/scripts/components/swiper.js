import { nanoid } from "nanoid";
import Swiper from "swiper";
import {
  Navigation,
  Pagination,
  Scrollbar,
  Autoplay,
  Thumbs,
  Grid,
  FreeMode,
  EffectFade,
} from "swiper/modules";

// Note: CSS is imported via swiper.scss, not here to avoid duplication

function isEmpty(value) {
  if (value == null) return true;
  if (Array.isArray(value) || typeof value === "string")
    return value.length === 0;
  if (typeof value === "object") return Object.keys(value).length === 0;
  return false;
}

// Initialize Swiper instances
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

// Generate unique class names
const generateClasses = () => {
  const rand = nanoid(9);
  return {
    rand: rand,
    swiperClass: "swiper-" + rand,
    nextClass: "next-" + rand,
    prevClass: "prev-" + rand,
    paginationClass: "pagination-" + rand,
    scrollbarClass: "scrollbar-" + rand,
  };
};

// Default Swiper options
const getDefaultOptions = () => ({
  modules: [
    Navigation,
    Pagination,
    Scrollbar,
    Autoplay,
    Thumbs,
    Grid,
    FreeMode,
    EffectFade,
  ],
  grabCursor: !0,
  allowTouchMove: !0,
  threshold: 5,
  hashNavigation: !1,
  mousewheel: !1,
  wrapperClass: "swiper-wrapper",
  slideClass: "swiper-slide",
  slideActiveClass: "swiper-slide-active",
});

// Utility to generate random integers
const random = (min, max) => Math.floor(Math.random() * (max - min + 1)) + min;

//
// swipers single
//
const initializeSwipers = () => {
  const swiperElements = document.querySelectorAll(".w-swiper");

  swiperElements.forEach((el, index) => {
    const classes = generateClasses();
    el.classList.add(classes.swiperClass);

    let controls = el
      .closest(".swiper-section")
      ?.querySelector(".swiper-controls");
    if (!controls) {
      controls = document.createElement("div");
      controls.classList.add("swiper-controls");
      el.after(controls);
    }

    const swiperWrapper = el?.querySelector(".swiper-wrapper");
    let options = JSON.parse(swiperWrapper.dataset.options) || {};

    if (isEmpty(options)) {
      options = {
        autoview: !0,
        autoplay: !0,
        navigation: !0,
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
          1024: options.desktop || {},
        };
      } else if (options.smallgap) {
        swiperOptions.spaceBetween = parseInt(options.smallgap);
      }
    }

    if (options.mobile || options.tablet || options.desktop) {
      swiperOptions.breakpoints = {
        0: options.mobile || {},
        768: options.tablet || {},
        1024: options.desktop || {},
      };
    }

    if (options.observer) {
      swiperOptions.observer = !0;
      swiperOptions.observeParents = !0;
    }

    if (options.effect) {
      swiperOptions.effect = String(options.effect);
      if (swiperOptions.effect === "fade") {
        swiperOptions.fadeEffect = { crossFade: !0 };
      }
    }

    if (options.spaceBetween)
      swiperOptions.spaceBetween = parseInt(options.spaceBetween);
    if (options.slidesPerGroup)
      swiperOptions.slidesPerGroup = parseInt(options.slidesPerGroup);
    if (options.autoheight) swiperOptions.autoHeight = !0;
    if (options.loop) swiperOptions.loop = !0;
    if (options.parallax) swiperOptions.parallax = !0;
    if (options.direction) swiperOptions.direction = String(options.direction);
    if (options.freemode) swiperOptions.freeMode = !0;
    if (options.cssmode) swiperOptions.cssMode = !0;

    if (options.centered) {
      swiperOptions.centeredSlides = !0;
      swiperOptions.centeredSlidesBounds = !0;
    }

    swiperOptions.speed = options.speed
      ? parseInt(options.speed)
      : random(300, 900);

    if (options.autoplay) {
      swiperOptions.autoplay = {
        disableOnInteraction: !1,
        delay: options.delay ? parseInt(options.delay) : random(4000, 6000),
      };
      if (options.reverse) swiperOptions.reverseDirection = !0;
    }

    // Navigation
    if (options.navigation) {
      const section = el.closest(".swiper-section");
      let btnPrev = section?.querySelector(".swiper-button-prev");
      let btnNext = section?.querySelector(".swiper-button-next");

      if (btnPrev && btnNext) {
        btnPrev.classList.add(classes.prevClass);
        btnNext.classList.add(classes.nextClass);
        // Ensure accessibility attributes on existing buttons
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

        // SVG icons with accessibility attributes
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
        prevEl: "." + classes.prevClass,
      };
    }

    // Pagination
    if (options.pagination) {
      const section = el.closest(".swiper-section");
      let pagination = section?.querySelector(".swiper-pagination");
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
        clickable: !0,
        ...(paginationType === "bullets" && {
          dynamicBullets: !0,
          type: "bullets",
        }),
        ...(paginationType === "fraction" && { type: "fraction" }),
        ...(paginationType === "progressbar" && { type: "progressbar" }),
        ...(paginationType === "custom" && {
          renderBullet: (index, className) =>
            `<span class="${className}">${index + 1}</span>`,
        }),
      };
    }

    // Scrollbar
    if (options.scrollbar) {
      const section = el.closest(".swiper-section");
      let scrollbar = section?.querySelector(".swiper-scrollbar");
      if (scrollbar) {
        scrollbar.classList.add(classes.scrollbarClass);
      } else {
        scrollbar = document.createElement("div");
        scrollbar.classList.add("swiper-scrollbar", classes.scrollbarClass);
        controls.appendChild(scrollbar);
      }

      swiperOptions.scrollbar = {
        el: "." + classes.scrollbarClass,
        hide: !0,
        draggable: !0,
      };
    }

    // Marquee
    if (options.marquee) {
      swiperOptions.centeredSlides = !1;
      swiperOptions.autoplay = {
        delay: 1,
        disableOnInteraction: !0,
      };
      swiperOptions.loop = !0;
      swiperOptions.speed = 6000;
      swiperOptions.allowTouchMove = !0;
    }

    // rows
    if (options.rows) {
      swiperOptions.direction = "horizontal";
      swiperOptions.loop = !1;
      swiperOptions.grid = {
        rows: parseInt(options.rows),
        fill: "row",
      };
    }

    initializeSwiper(el, "." + classes.swiperClass, swiperOptions);
  });
};

//
// Products slides
//
const spgSwipers = () => {
  const swiperElements = document.querySelectorAll(".swiper-product-gallery");

  swiperElements.forEach((el, index) => {
    const classes = generateClasses();
    el.classList.add(classes.swiperClass);

    const w_images = el?.querySelector(".swiper-images");
    const w_thumbs = el?.querySelector(".swiper-thumbs");

    let swiper_images = false;
    let swiper_thumbs = false;

    /** wpg thumbs */
    if (w_thumbs) {
      w_thumbs
        ?.querySelector(".swiper-button-prev")
        .classList.add("prev-thumbs-" + classes.rand);
      w_thumbs
        ?.querySelector(".swiper-button-next")
        .classList.add("next-thumbs-" + classes.rand);
      w_thumbs.classList.add("thumbs-" + classes.rand);

      let thumbs_options = { ...getDefaultOptions() };
      thumbs_options.breakpoints = {
        0: {
          spaceBetween: 5,
          slidesPerView: 4,
        },
        768: {
          spaceBetween: 10,
          slidesPerView: 5,
        },
        1024: {
          spaceBetween: 10,
          slidesPerView: 6,
        },
      };

      thumbs_options.navigation = {
        prevEl: ".prev-thumbs-" + classes.rand,
        nextEl: ".next-thumbs-" + classes.rand,
      };

      swiper_thumbs = initializeSwiper(
        w_thumbs,
        ".thumbs-" + classes.rand,
        thumbs_options
      );
    }

    /** wpg images */
    if (w_images) {
      w_images
        ?.querySelector(".swiper-button-prev")
        .classList.add("prev-images-" + classes.rand);
      w_images
        ?.querySelector(".swiper-button-next")
        .classList.add("next-images-" + classes.rand);
      w_images.classList.add("images-" + classes.rand);

      let images_options = { ...getDefaultOptions() };
      images_options.slidesPerView = "auto";
      images_options.spaceBetween = 10;
      images_options.watchSlidesProgress = !0;

      images_options.navigation = {
        prevEl: ".prev-images-" + classes.rand,
        nextEl: ".next-images-" + classes.rand,
      };

      if (swiper_thumbs) {
        images_options.thumbs = {
          swiper: swiper_thumbs,
        };
      }

      swiper_images = initializeSwiper(
        w_images,
        ".images-" + classes.rand,
        images_options
      );
    }

    /** Variation image */
    let firstImage = w_images?.querySelector(".swiper-images-first img");
    firstImage.removeAttribute("srcset");

    let firstImageSrc = firstImage.getAttribute("src");
    let imagePopupSrc = w_images?.querySelector(
      ".swiper-images-first .image-popup"
    );

    /** */
    let firstThumb = false;
    let firstThumbSrc = false;
    let dataLargeImage = false;

    if (swiper_thumbs) {
      firstThumb = w_thumbs?.querySelector(".swiper-thumbs-first img");
      firstThumb.removeAttribute("srcset");

      firstThumbSrc = firstThumb.getAttribute("src");
      dataLargeImage = firstThumb.getAttribute("data-large_image");
    }

    /** WC event */
    const variations_form = jQuery("form.variations_form");
    if (variations_form) {
      variations_form.on("found_variation", function (event, variation) {
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

      variations_form.on("reset_image", function () {
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

//
// Product Gallery - Hover Zoom Effect
//
const initZoomHover = () => {
  const galleries = document.querySelectorAll(".swiper-product-gallery");

  galleries.forEach((gallery) => {
    const zoomContainers = gallery.querySelectorAll(".zoom-container");

    zoomContainers.forEach((container) => {
      const img = container.querySelector("img");
      if (!img) return;

      container.addEventListener("mousemove", (e) => {
        const rect = container.getBoundingClientRect();
        const x = ((e.clientX - rect.left) / rect.width) * 100;
        const y = ((e.clientY - rect.top) / rect.height) * 100;
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

//
// Product Gallery - Lightbox Popup
//
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

  // Collect all gallery media (images + videos)
  const collectMedia = () => {
    mediaItems = [];
    const gallery = document.querySelector(".swiper-product-gallery");
    if (!gallery) return;

    // Collect images
    const imagePopups = gallery.querySelectorAll(".image-popup");
    imagePopups.forEach((popup) => {
      mediaItems.push({
        src: popup.dataset.src || popup.href,
        caption: popup.dataset.caption || "",
        type: "image",
        index: parseInt(popup.dataset.index, 10) || 0,
      });
    });

    // Collect videos (YouTube/Vimeo)
    const videoPopups = gallery.querySelectorAll(".video-popup");
    videoPopups.forEach((popup) => {
      mediaItems.push({
        src: popup.dataset.src || popup.href,
        caption: "Video sản phẩm",
        type: "video",
        index: parseInt(popup.dataset.index, 10) || 0,
      });
    });

    // Collect TikTok videos
    const tiktokWrappers = gallery.querySelectorAll(".gallery-tiktok-wrapper");
    tiktokWrappers.forEach((wrapper) => {
      mediaItems.push({
        src: wrapper.dataset.videoEmbed || "",
        caption: "TikTok Video",
        type: "video",
        index: mediaItems.length,
      });
    });

    // Sort by index
    mediaItems.sort((a, b) => a.index - b.index);
  };

  // Open lightbox
  const openLightbox = (index) => {
    collectMedia();
    if (mediaItems.length === 0) return;

    currentIndex = index;
    updateLightboxContent();
    lightbox.classList.add("is-active");
    document.body.classList.add("lightbox-open");
  };

  // Close lightbox
  const closeLightbox = () => {
    lightbox.classList.remove("is-active");
    document.body.classList.remove("lightbox-open");

    // Clear video iframe to stop playback
    const videoWrapper = imageWrapper.querySelector(".lightbox-video-wrapper");
    if (videoWrapper) {
      videoWrapper.innerHTML = "";
    }
  };

  // Update lightbox content
  const updateLightboxContent = () => {
    if (mediaItems.length === 0) return;

    const item = mediaItems[currentIndex];
    // Update counter
    if (counter) {
      counter.textContent = `${currentIndex + 1}/${mediaItems.length}`;
    }

    // Update caption
    if (caption) {
      caption.textContent = item.caption || "";
    }

    // Remove existing iframe if any
    const existingIframe = imageWrapper.querySelector("iframe");
    if (existingIframe) {
      existingIframe.remove();
    }

    // Handle image vs video
    if (item.type === "image") {
      lightboxImage.style.display = "block";
      lightboxImage.src = item.src;
      lightboxImage.alt = item.caption || "Product image";

      // Remove video wrapper if exists
      const videoWrapper = imageWrapper.querySelector(
        ".lightbox-video-wrapper"
      );
      if (videoWrapper) {
        videoWrapper.remove();
      }

      // Show zoom button for images
      if (zoomBtn) zoomBtn.style.display = "";
    } else {
      // Video - hide image, show iframe
      lightboxImage.style.display = "none";

      // Create or update video wrapper
      let videoWrapper = imageWrapper.querySelector(".lightbox-video-wrapper");
      if (!videoWrapper) {
        videoWrapper = document.createElement("div");
        videoWrapper.className = "lightbox-video-wrapper";
        imageWrapper.appendChild(videoWrapper);
      }

      // Determine if TikTok or standard video
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

      // Hide zoom button for video
      if (zoomBtn) zoomBtn.style.display = "none";
    }

    // Reset zoom state
    imageWrapper.classList.remove("is-zoomed");
  };

  // Navigate
  const goToPrev = () => {
    currentIndex = currentIndex > 0 ? currentIndex - 1 : mediaItems.length - 1;
    updateLightboxContent();
  };

  const goToNext = () => {
    currentIndex = currentIndex < mediaItems.length - 1 ? currentIndex + 1 : 0;
    updateLightboxContent();
  };

  // Toggle zoom
  const toggleZoom = () => {
    imageWrapper.classList.toggle("is-zoomed");
  };

  // Event listeners for images
  document.addEventListener("click", (e) => {
    const imagePopup = e.target.closest(".image-popup");
    if (imagePopup) {
      e.preventDefault();
      const index = parseInt(imagePopup.dataset.index, 10) || 0;
      openLightbox(index);
    }

    // Video popup (YouTube/Vimeo)
    const videoPopup = e.target.closest(".video-popup");
    if (videoPopup) {
      e.preventDefault();
      const index = parseInt(videoPopup.dataset.index, 10) || 0;
      openLightbox(index);
    }

    // TikTok gallery overlay click
    const tiktokOverlay = e.target.closest(".gallery-tiktok-overlay");
    if (tiktokOverlay) {
      e.preventDefault();
      e.stopPropagation();
      const wrapper = tiktokOverlay.closest(".gallery-tiktok-wrapper");
      if (wrapper) {
        // Find index of this TikTok video
        const gallery = document.querySelector(".swiper-product-gallery");
        const allPopups = gallery.querySelectorAll(
          ".image-popup, .video-popup"
        );
        const tiktokIndex = allPopups.length; // TikTok is after all other media
        openLightbox(tiktokIndex);
      }
    }
  });

  if (overlay) overlay.addEventListener("click", closeLightbox);
  if (closeBtn) closeBtn.addEventListener("click", closeLightbox);
  if (zoomBtn) zoomBtn.addEventListener("click", toggleZoom);
  if (prevBtn) prevBtn.addEventListener("click", goToPrev);
  if (nextBtn) nextBtn.addEventListener("click", goToNext);

  // Keyboard navigation
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

  // Click on image to toggle zoom
  if (lightboxImage) {
    lightboxImage.addEventListener("click", toggleZoom);
  }

  // Touch swipe support
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

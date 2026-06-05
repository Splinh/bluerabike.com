/**
 * Product Video Tabs - JavaScript
 *
 * @package Product_Video_Tabs
 * @version 1.0.1
 */

(function () {
  "use strict";

  // Wait for window load (after all scripts including Slick)
  window.addEventListener("load", function () {
    initVideoPopup();
    // Delay injection to ensure sliders are fully initialized
    setTimeout(function () {
      injectVideoIntoGalleryWithRetry();
    }, 500);
  });

  /**
   * Inject with retry mechanism for sliders that init late
   */
  function injectVideoIntoGalleryWithRetry(attempts) {
    attempts = attempts || 0;
    const maxAttempts = 5;

    const result = injectVideoIntoGallery();

    // Retry if container not found and we have attempts left
    if (!result && attempts < maxAttempts) {
      setTimeout(function () {
        injectVideoIntoGalleryWithRetry(attempts + 1);
      }, 300);
    }
  }

  /**
   * Inject video into product gallery (Bluera-style)
   * For WPGS plugin: inject into BOTH main slider and thumbnail slider
   * Video appears at END of gallery, plays inline in main slider when clicked
   */
  function injectVideoIntoGallery() {
    // Get video data from hidden container
    const videoDataContainer = document.getElementById("pvt-video-data");
    if (!videoDataContainer) return;

    const videos = JSON.parse(videoDataContainer.dataset.videos || "[]");
    if (!videos.length) return;

    // Check for WPGS plugin (has both .wpgs-for and .wpgs-nav)
    const wpgsMain = document.querySelector(".wpgs-for .slick-track");
    const wpgsNav = document.querySelector(".wpgs-nav .slick-track");

    if (wpgsMain && wpgsNav) {
      // WPGS Plugin detected - inject into BOTH sliders
      console.log("PVT: WPGS plugin detected, injecting into both sliders");
      injectWPGSVideo(videos, wpgsMain, wpgsNav);
      return true;
    }

    // Fallback for other themes
    const fallbackSelectors = [
      ".product-thumbnails .flickity-slider",
      ".product-thumbnails",
      ".flex-control-thumbs",
      ".woocommerce-product-gallery__wrapper",
    ];

    let thumbnailContainer = null;
    for (const selector of fallbackSelectors) {
      thumbnailContainer = document.querySelector(selector);
      if (thumbnailContainer) break;
    }

    if (!thumbnailContainer) {
      console.log("PVT: Gallery container not found, will retry...");
      return false;
    }

    // Fallback: inject thumbnails only
    videos.forEach(function (video) {
      const thumbElement = createVideoThumbnail(video, "thumbnail");
      thumbnailContainer.appendChild(thumbElement);
    });

    initInjectedVideoHandlers();
    console.log("PVT: Video thumbnail injected (fallback mode)");
    return true;
  }

  /**
   * Inject video into WPGS plugin (both main and thumbnail sliders)
   */
  function injectWPGSVideo(videos, mainTrack, navTrack) {
    videos.forEach(function (video, index) {
      // Video will be at index 0 (first position)
      const slideIndex = index;

      // Create main slider video slide (large, with TikTok embed)
      const mainSlide = createWPGSMainSlide(video, slideIndex);
      // PREPEND to show video FIRST
      if (mainTrack.firstChild) {
        mainTrack.insertBefore(mainSlide, mainTrack.firstChild);
      } else {
        mainTrack.appendChild(mainSlide);
      }

      // Create thumbnail slide (small, with TikTok icon)
      const thumbSlide = createWPGSThumbSlide(video, slideIndex);
      // PREPEND to show video FIRST
      if (navTrack.firstChild) {
        navTrack.insertBefore(thumbSlide, navTrack.firstChild);
      } else {
        navTrack.appendChild(thumbSlide);
      }

      // Add click handler to sync with Slick
      thumbSlide.addEventListener("click", function (e) {
        e.preventDefault();
        e.stopPropagation();

        // Use Slick's slickGoTo to navigate to this slide
        const $wpgsFor = jQuery(".wpgs-for");

        if ($wpgsFor.length && typeof $wpgsFor.slick === "function") {
          $wpgsFor.slick("slickGoTo", slideIndex);
        }
      });
    });

    // Re-index all slides after injection
    reindexSlickSlides(mainTrack);
    reindexSlickSlides(navTrack);

    console.log("PVT: WPGS video slides injected at FIRST position!");
  }

  /**
   * Re-index slick slides after DOM manipulation
   */
  function reindexSlickSlides(track) {
    const slides = track.querySelectorAll(".slick-slide");
    slides.forEach(function (slide, index) {
      slide.setAttribute("data-slick-index", index);
    });
  }

  /**
   * Create WPGS main slider slide (large video display)
   */
  function createWPGSMainSlide(video, index) {
    const slide = document.createElement("div");
    slide.className = "slick-slide pvt-video-main-slide";
    slide.setAttribute("data-slick-index", index);
    slide.setAttribute("tabindex", "-1");
    slide.style.width = "";

    if (video.type === "tiktok") {
      slide.innerHTML = `
        <div class="pvt-video-main-wrapper pvt-video-main-wrapper--tiktok">
          <iframe 
            src="${video.embed}" 
            allowfullscreen 
            allow="autoplay; encrypted-media"
            frameborder="0"
          ></iframe>
        </div>
      `;
    } else if (video.type === "youtube") {
      slide.innerHTML = `
        <div class="pvt-video-main-wrapper pvt-video-main-wrapper--youtube">
          <iframe 
            src="${video.embed}" 
            allowfullscreen 
            allow="autoplay; encrypted-media"
            frameborder="0"
          ></iframe>
        </div>
      `;
    }

    return slide;
  }

  /**
   * Create WPGS thumbnail slide (small video thumbnail)
   */
  function createWPGSThumbSlide(video, index) {
    const slide = document.createElement("div");
    slide.className =
      "slick-slide pvt-video-thumb-slide pvt-video-thumb-slide--" + video.type;
    slide.setAttribute("data-slick-index", index);
    slide.setAttribute("tabindex", "-1");
    slide.style.width = "";

    if (video.type === "tiktok") {
      slide.innerHTML = `
        <div class="pvt-video-thumb-inner">
          <svg class="pvt-video-thumb-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512" fill="#000">
            <path d="M448,209.91a210.06,210.06,0,0,1-122.77-39.25V349.38A162.55,162.55,0,1,1,185,188.31V278.2a74.62,74.62,0,1,0,52.23,71.18V0l88,0a121.18,121.18,0,0,0,1.86,22.17h0A122.18,122.18,0,0,0,381,102.39a121.43,121.43,0,0,0,67,20.14Z"/>
          </svg>
          <span class="pvt-video-thumb-play">▶</span>
        </div>
      `;
    } else if (video.type === "youtube") {
      slide.innerHTML = `
        <div class="pvt-video-thumb-inner">
          <img src="${video.thumbnail}" alt="Video" class="pvt-video-thumb-img">
          <span class="pvt-video-thumb-play">▶</span>
        </div>
      `;
    }

    return slide;
  }

  /**
   * Create video thumbnail element
   * Includes slick-slide class for WPGS compatibility
   */
  function createVideoThumbnail(video) {
    const wrapper = document.createElement("div");
    // Add slick-slide class for WPGS compatibility + our custom class
    wrapper.className =
      "slick-slide pvt-gallery-thumb-inline pvt-gallery-thumb-inline--" +
      video.type;
    // Set slick required styles
    wrapper.style.width = "";
    wrapper.setAttribute("data-slick-index", "-1");
    wrapper.setAttribute("tabindex", "-1");

    if (video.type === "youtube") {
      wrapper.innerHTML = `
        <a href="${video.embed}" class="pvt-video-thumb-link" data-video-type="youtube" data-video-embed="${video.embed}">
          <img src="${video.thumbnail}" alt="YouTube Video" loading="lazy" class="woocommerce-product-gallery__image">
          <span class="pvt-gallery-thumb-inline__play">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 384 512" width="14" height="14" fill="#fff">
              <path d="M73 39c-14.8-9.1-33.4-9.4-48.5-.9S0 62.6 0 80L0 432c0 17.4 9.4 33.4 24.5 41.9s33.7 8.1 48.5-.9L361 297c14.3-8.8 23-24.2 23-41s-8.7-32.2-23-41L73 39z"/>
            </svg>
          </span>
          <span class="pvt-gallery-thumb-inline__badge pvt-gallery-thumb-inline__badge--youtube">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 576 512" width="12" height="12" fill="#FF0000">
              <path d="M549.655 124.083c-6.281-23.65-24.787-42.276-48.284-48.597C458.781 64 288 64 288 64S117.22 64 74.629 75.486c-23.497 6.322-42.003 24.947-48.284 48.597-11.412 42.867-11.412 132.305-11.412 132.305s0 89.438 11.412 132.305c6.281 23.65 24.787 41.5 48.284 47.821C117.22 448 288 448 288 448s170.78 0 213.371-11.486c23.497-6.321 42.003-24.171 48.284-47.821 11.412-42.867 11.412-132.305 11.412-132.305s0-89.438-11.412-132.305z"/>
            </svg>
          </span>
        </a>
      `;
    } else if (video.type === "tiktok") {
      wrapper.innerHTML = `
        <div class="pvt-tiktok-player-wrapper" data-video-type="tiktok" data-video-embed="${video.embed}">
          <div class="pvt-tiktok-click-overlay"></div>
          <div class="pvt-gallery-thumb-inline__tiktok-icon">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512" width="20" height="20" fill="#000">
              <path d="M448,209.91a210.06,210.06,0,0,1-122.77-39.25V349.38A162.55,162.55,0,1,1,185,188.31V278.2a74.62,74.62,0,1,0,52.23,71.18V0l88,0a121.18,121.18,0,0,0,1.86,22.17h0A122.18,122.18,0,0,0,381,102.39a121.43,121.43,0,0,0,67,20.14Z"/>
            </svg>
          </div>
          <span class="pvt-gallery-thumb-inline__play">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 384 512" width="12" height="12" fill="#fff">
              <path d="M73 39c-14.8-9.1-33.4-9.4-48.5-.9S0 62.6 0 80L0 432c0 17.4 9.4 33.4 24.5 41.9s33.7 8.1 48.5-.9L361 297c14.3-8.8 23-24.2 23-41s-8.7-32.2-23-41L73 39z"/>
            </svg>
          </span>
        </div>
      `;
    }

    return wrapper;
  }

  /**
   * Initialize handlers for injected video elements
   */
  function initInjectedVideoHandlers() {
    const videoModal = document.getElementById("pvt-video-popup-modal");
    if (!videoModal) return;

    const iframeWrapper = videoModal.querySelector(
      ".pvt-video-popup-iframe-wrapper",
    );

    // YouTube links
    document
      .querySelectorAll(".pvt-gallery-thumb-inline .pvt-video-thumb-link")
      .forEach(function (link) {
        link.addEventListener("click", function (e) {
          e.preventDefault();
          e.stopPropagation();
          openVideoPopupGlobal(
            link.dataset.videoEmbed,
            link.dataset.videoType,
            videoModal,
            iframeWrapper,
          );
        });
      });

    // TikTok overlays
    document
      .querySelectorAll(".pvt-gallery-thumb-inline .pvt-tiktok-click-overlay")
      .forEach(function (overlay) {
        overlay.addEventListener("click", function (e) {
          e.preventDefault();
          e.stopPropagation();
          const wrapper = overlay.closest(".pvt-tiktok-player-wrapper");
          if (wrapper) {
            openVideoPopupGlobal(
              wrapper.dataset.videoEmbed,
              wrapper.dataset.videoType,
              videoModal,
              iframeWrapper,
            );
          }
        });
      });
  }

  /**
   * Global function to open video popup
   */
  function openVideoPopupGlobal(embedUrl, videoType, modal, wrapper) {
    wrapper.innerHTML = "";
    wrapper.className = "pvt-video-popup-iframe-wrapper";

    const iframe = document.createElement("iframe");
    iframe.src = embedUrl;
    iframe.setAttribute("frameborder", "0");
    iframe.setAttribute("allowfullscreen", "true");
    iframe.setAttribute(
      "allow",
      "accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; fullscreen",
    );

    if (videoType === "youtube") {
      wrapper.classList.add("is-youtube");
    } else if (videoType === "tiktok") {
      wrapper.classList.add("is-tiktok");
    }

    wrapper.appendChild(iframe);
    modal.classList.add("is-active");
    document.body.style.overflow = "hidden";
  }

  /**
   * Initialize Video Popup functionality
   */
  function initVideoPopup() {
    const videoModal = document.getElementById("pvt-video-popup-modal");
    if (!videoModal) return;

    const overlay = videoModal.querySelector(".pvt-video-popup-overlay");
    const closeBtn = videoModal.querySelector(".pvt-video-popup-close");
    const iframeWrapper = videoModal.querySelector(
      ".pvt-video-popup-iframe-wrapper",
    );

    // Open video popup - for YouTube thumbnails
    document.querySelectorAll(".pvt-video-thumb-link").forEach(function (link) {
      link.addEventListener("click", function (e) {
        e.preventDefault();
        openVideoPopup(link.dataset.videoEmbed, link.dataset.videoType);
      });
    });

    // Open video popup - for TikTok player iframes (via overlay)
    document
      .querySelectorAll(".pvt-tiktok-click-overlay")
      .forEach(function (tiktokOverlay) {
        tiktokOverlay.addEventListener("click", function (e) {
          e.preventDefault();
          e.stopPropagation();
          var wrapper = tiktokOverlay.closest(".pvt-tiktok-player-wrapper");
          if (wrapper) {
            openVideoPopup(
              wrapper.dataset.videoEmbed,
              wrapper.dataset.videoType,
            );
          }
        });
      });

    /**
     * Open Video Popup
     */
    function openVideoPopup(embedUrl, videoType) {
      iframeWrapper.innerHTML = "";
      iframeWrapper.className = "pvt-video-popup-iframe-wrapper";

      var iframe = document.createElement("iframe");
      iframe.src = embedUrl;
      iframe.setAttribute("frameborder", "0");
      iframe.setAttribute("allowfullscreen", "true");
      iframe.setAttribute(
        "allow",
        "accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; fullscreen",
      );

      if (videoType === "youtube") {
        iframeWrapper.classList.add("is-youtube");
      } else if (videoType === "tiktok") {
        iframeWrapper.classList.add("is-tiktok");
      }

      iframeWrapper.appendChild(iframe);
      videoModal.classList.add("is-active");
      document.body.style.overflow = "hidden";
    }

    /**
     * Close Video Popup
     */
    function closeVideoPopup() {
      videoModal.classList.remove("is-active");
      document.body.style.overflow = "";
      iframeWrapper.innerHTML = "";
    }

    if (overlay) {
      overlay.addEventListener("click", closeVideoPopup);
    }

    if (closeBtn) {
      closeBtn.addEventListener("click", closeVideoPopup);
    }

    document.addEventListener("keydown", function (e) {
      if (e.key === "Escape" && videoModal.classList.contains("is-active")) {
        closeVideoPopup();
      }
    });
  }
})();

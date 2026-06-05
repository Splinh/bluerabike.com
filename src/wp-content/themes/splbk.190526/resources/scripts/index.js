import $ from "jquery";
import device from "current-device";
import Foundation from "./3rd/_zf.js";

import "./utils/global.js";
import "./utils/back-to-top.js";
import "./utils/script-loader.js";
import { initMenu } from "./utils/menu.js";

import { initSocialShare } from "./components/social-share.js";

// Cookie Consent
import "./cookie-consent.js";

// Styles
import "../styles/3rd/_index.scss";

// DOMContentLoaded
document.addEventListener("DOMContentLoaded", () => {
  initMenu("#main-nav", ".main-nav");
  initSocialShare("[data-social-share]", {
    intents: ["facebook", "x", "print", "send-email", "copy-link", "web-share"],
  });

  //
  // YITH filter
  //
  const wrapper = document.querySelector(".yith-wcan-filters");
  if (wrapper) {
    const observer = new MutationObserver((mutations) => {
      for (let mutation of mutations) {
        if (mutation.type === "childList" && mutation.addedNodes.length > 0) {
          initMenu("#main-nav", ".main-nav");
          break;
        }
      }
    });
    observer.observe(wrapper, { childList: true, subtree: true });
  }

  //
  // toggle menu footer
  //
  // document.querySelectorAll("#footer-columns .footer-title").forEach((link) => {
  //   link.addEventListener("click", function (event) {
  //     event.preventDefault();
  //     this.classList.toggle("active");
  //   });
  // });

  //
  // Category menu - make entire button clickable (not just arrow)
  //
  document.querySelectorAll(".product-nav .pro-triger").forEach((li) => {
    const link = li.querySelector(":scope > a");
    const toggle = li.querySelector(":scope > .submenu-toggle");
    const submenu = li.querySelector(":scope > ul.sub-menu");

    if (link && submenu) {
      link.addEventListener("click", (e) => {
        e.preventDefault();
        e.stopPropagation();
        li.classList.toggle("is-active");
        submenu.style.display = li.classList.contains("is-active")
          ? "flex"
          : "none";
        if (toggle) {
          toggle.setAttribute(
            "aria-expanded",
            li.classList.contains("is-active"),
          );
        }
      });
    }
  });

  //
  // product attributes
  //
  const MIN_WIDTH = 250;
  const widgets = document.querySelectorAll(
    ".product-attributes .widget_layered_nav",
  );

  function adjustDropdown(widget) {
    const dropdown = widget.querySelector("ul");
    if (!dropdown) return;

    const toggleElem = widget.querySelector("span") || widget;
    const rect = toggleElem.getBoundingClientRect();
    const spaceRight = window.innerWidth - rect.right;

    if (spaceRight < MIN_WIDTH) {
      dropdown.classList.add("align-right");
    } else {
      dropdown.classList.remove("align-right");
    }
  }

  function closeAll() {
    widgets.forEach((el) => el.classList.remove("active"));
  }

  widgets.forEach((widget) => {
    widget.addEventListener("click", (e) => {
      if (e.target.closest("ul")) return;

      e.preventDefault();
      const isActive = widget.classList.contains("active");

      closeAll();

      if (!isActive) {
        widget.classList.add("active");
        adjustDropdown(widget);
      }
    });
  });

  window.addEventListener("resize", () => {
    const openWidget = document.querySelector(
      ".product-attributes .widget_layered_nav.active",
    );
    if (openWidget) adjustDropdown(openWidget);
  });
  //    tab sản phẩm
  document.querySelectorAll(".filter-tabs").forEach((group) => {
    const links = group.querySelectorAll(".tabs-nav a");
    const panels = group.querySelectorAll(".tabs-panel");
    links.forEach((link) => {
      link.addEventListener("click", (e) => {
        const href = link.getAttribute("href");
        // Skip real links (not tab anchors) - allow navigation
        if (!href || !href.startsWith("#") || href === "#") {
          return; // Let the browser handle the navigation
        }
        e.preventDefault();
        const target = group.querySelector(href);
        links.forEach((l) => l.classList.remove("current"));
        panels.forEach((p) => p.classList.remove("current"));
        link.classList.add("current");
        if (target) target.classList.add("current");
      });
    });
  });

  //
});
document.addEventListener("DOMContentLoaded", () => {
  const overlay = document.createElement("div");
  overlay.className = "lightbox";
  overlay.style.cssText = `
    display:none; position:fixed; inset:0; background:rgba(0,0,0,0.85);
    justify-content:center; align-items:center; z-index:9999;
  `;
  document.body.appendChild(overlay);

  document.body.addEventListener("click", (e) => {
    const link = e.target.closest(".play-video");
    if (link) {
      e.preventDefault();
      const yt = link.getAttribute("data-youtube");
      const id = yt.match(/[?&]v=([^&]+)/)?.[1];
      if (!id) return;
      overlay.innerHTML = `<iframe width="80%" height="60%" src="https://www.youtube.com/embed/${id}?autoplay=1" frameborder="0" allowfullscreen></iframe>`;
      overlay.style.display = "flex";
    }
  });

  overlay.addEventListener("click", () => {
    overlay.style.display = "none";
    overlay.innerHTML = "";
  });

  //  header fixed
  window.addEventListener("scroll", function () {
    const header = document.getElementById("header");
    const stickyPoint = header.offsetTop;

    if (window.scrollY > stickyPoint) {
      header.classList.add("sticky");
    } else {
      header.classList.remove("sticky");
    }
  });
});

document.addEventListener("DOMContentLoaded", function () {
  const openButtons = document.querySelectorAll("[data-popup]");
  const closeButtons = document.querySelectorAll("[data-popup-close]");

  openButtons.forEach((btn) => {
    btn.addEventListener("click", () => {
      const target = btn.getAttribute("data-popup");
      const popup = document.querySelector(target);
      if (popup) popup.classList.add("active");
    });
  });

  closeButtons.forEach((btn) => {
    btn.addEventListener("click", () => {
      btn.closest(".specs-popup").classList.remove("active");
    });
  });
});

document.addEventListener("DOMContentLoaded", function () {
  // tìm đúng UL của menu dọc
  var menu = document.querySelector("#product-nav ul.vertical-menu");

  if (menu) {
    menu.removeAttribute("role");
    menu.removeAttribute("aria-multiselectable");
    // nếu muốn thì gán thêm label cho riêng UL cũng được:
    menu.setAttribute("aria-label", "Danh mục sản phẩm");
  }
});

// ===============================================
// Mobile Product Popup Toggle
// ===============================================
document.addEventListener("DOMContentLoaded", function () {
  const mobileProductBtn = document.querySelector(".mobile-product-menu-btn");
  const mobileProductPopup = document.querySelector(".mobile-product-popup");
  const mobileProductClose = document.querySelector(
    ".mobile-product-popup__close",
  );
  const mobileProductOverlay = document.querySelector(
    ".mobile-product-popup__overlay",
  );

  if (mobileProductBtn && mobileProductPopup) {
    // Open popup
    mobileProductBtn.addEventListener("click", function () {
      mobileProductPopup.classList.add("is-open");
      mobileProductPopup.setAttribute("aria-hidden", "false");
      document.body.style.overflow = "hidden";
    });

    // Close popup - close button
    if (mobileProductClose) {
      mobileProductClose.addEventListener("click", closeMobileProductPopup);
    }

    // Close popup - overlay click
    if (mobileProductOverlay) {
      mobileProductOverlay.addEventListener("click", closeMobileProductPopup);
    }

    // Close popup - ESC key
    document.addEventListener("keydown", function (e) {
      if (
        e.key === "Escape" &&
        mobileProductPopup.classList.contains("is-open")
      ) {
        closeMobileProductPopup();
      }
    });

    function closeMobileProductPopup() {
      mobileProductPopup.classList.remove("is-open");
      mobileProductPopup.setAttribute("aria-hidden", "true");
      document.body.style.overflow = "";
    }
  }
});

// ===============================================
// Contact Link Toggle - PC only
// Khi click vào nút toggle, các icon liên hệ sẽ hiển thị với z-index cao hơn câu đối
// ===============================================
document.addEventListener("DOMContentLoaded", function () {
  const contactLinkContainer = document.querySelector(".add-this.contact-link");

  if (!contactLinkContainer) return;

  // Chỉ áp dụng cho PC (viewport > 768px)
  const isMobile = window.matchMedia("(max-width: 768px)").matches;
  if (isMobile) return;

  // SVG icons
  const envelopeIcon = `
    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
      <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path>
      <polyline points="22,6 12,13 2,6"></polyline>
    </svg>
  `;

  const closeIcon = `
    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
      <line x1="18" y1="6" x2="6" y2="18"></line>
      <line x1="6" y1="6" x2="18" y2="18"></line>
    </svg>
  `;

  // Tạo wrapper cho các items
  const items = contactLinkContainer.querySelectorAll(":scope > li");
  const itemsWrapper = document.createElement("div");
  itemsWrapper.className = "contact-items-wrapper";

  // Di chuyển các items vào wrapper
  items.forEach((item) => {
    itemsWrapper.appendChild(item);
  });

  // Tạo toggle button với icon bao thư mặc định
  const toggleBtn = document.createElement("button");
  toggleBtn.className = "contact-toggle-btn";
  toggleBtn.setAttribute("type", "button");
  toggleBtn.setAttribute("aria-label", "Hiển thị liên hệ");
  toggleBtn.innerHTML = envelopeIcon;

  // Thêm toggle button và wrapper vào container
  contactLinkContainer.insertBefore(toggleBtn, contactLinkContainer.firstChild);
  contactLinkContainer.appendChild(itemsWrapper);

  // Mặc định trạng thái collapsed (ẩn các items)
  contactLinkContainer.classList.add("is-collapsed");

  // Xử lý sự kiện click toggle
  toggleBtn.addEventListener("click", function () {
    const isCollapsed = contactLinkContainer.classList.contains("is-collapsed");

    if (isCollapsed) {
      // Expand - hiện các items với z-index cao
      contactLinkContainer.classList.remove("is-collapsed");
      contactLinkContainer.classList.add("is-expanded");
      toggleBtn.innerHTML = closeIcon; // Đổi sang icon X
      toggleBtn.setAttribute("aria-label", "Đóng");
    } else {
      // Collapse - ẩn các items
      contactLinkContainer.classList.add("is-collapsed");
      contactLinkContainer.classList.remove("is-expanded");
      toggleBtn.innerHTML = envelopeIcon; // Đổi sang icon bao thư
      toggleBtn.setAttribute("aria-label", "Hiển thị liên hệ");
    }
  });

  // Responsive: Lắng nghe thay đổi viewport
  window
    .matchMedia("(max-width: 768px)")
    .addEventListener("change", function (e) {
      if (e.matches) {
        // Mobile: Xóa toggle functionality, hiện tất cả
        contactLinkContainer.classList.remove("is-collapsed", "is-expanded");
        toggleBtn.style.display = "none";
      } else {
        // PC: Khôi phục toggle functionality
        contactLinkContainer.classList.add("is-collapsed");
        toggleBtn.innerHTML = envelopeIcon;
        toggleBtn.style.display = "";
      }
    });
});

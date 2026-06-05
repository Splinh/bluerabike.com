import { F as Foundation$1, T as Timer, a as Triggers, b as Touch, M as Move, c as Motion, d as MediaQuery, o as onImagesLoaded, N as Nest, B as Box, K as Keyboard, i as ignoreMousedisappear, e as onLoad, t as transitionend, R as RegExpEscape, G as GetYoDigits, r as rtl, $ as $$1, D as Dropdown, f as DropdownMenu, A as Accordion, g as AccordionMenu, O as OffCanvas, h as Tooltip, S as SmoothScroll, j as Sticky, k as Toggler, l as Abide } from "./vendor-foundation.js";
import { i as initSocialShare } from "./components/social-share.js";
/* empty css        */
var _typeof = typeof Symbol === "function" && typeof Symbol.iterator === "symbol" ? function(obj) {
  return typeof obj;
} : function(obj) {
  return obj && typeof Symbol === "function" && obj.constructor === Symbol && obj !== Symbol.prototype ? "symbol" : typeof obj;
};
var previousDevice = window.device;
var device = {};
var changeOrientationList = [];
window.device = device;
var documentElement = window.document.documentElement;
var userAgent = window.navigator.userAgent.toLowerCase();
var television = ["googletv", "viera", "smarttv", "internet.tv", "netcast", "nettv", "appletv", "boxee", "kylo", "roku", "dlnadoc", "pov_tv", "hbbtv", "ce-html"];
device.macos = function() {
  return find("mac");
};
device.ios = function() {
  return device.iphone() || device.ipod() || device.ipad();
};
device.iphone = function() {
  return !device.windows() && find("iphone");
};
device.ipod = function() {
  return find("ipod");
};
device.ipad = function() {
  var iPadOS13Up = navigator.platform === "MacIntel" && navigator.maxTouchPoints > 1;
  return find("ipad") || iPadOS13Up;
};
device.android = function() {
  return !device.windows() && find("android");
};
device.androidPhone = function() {
  return device.android() && find("mobile");
};
device.androidTablet = function() {
  return device.android() && !find("mobile");
};
device.blackberry = function() {
  return find("blackberry") || find("bb10");
};
device.blackberryPhone = function() {
  return device.blackberry() && !find("tablet");
};
device.blackberryTablet = function() {
  return device.blackberry() && find("tablet");
};
device.windows = function() {
  return find("windows");
};
device.windowsPhone = function() {
  return device.windows() && find("phone");
};
device.windowsTablet = function() {
  return device.windows() && find("touch") && !device.windowsPhone();
};
device.fxos = function() {
  return (find("(mobile") || find("(tablet")) && find(" rv:");
};
device.fxosPhone = function() {
  return device.fxos() && find("mobile");
};
device.fxosTablet = function() {
  return device.fxos() && find("tablet");
};
device.meego = function() {
  return find("meego");
};
device.cordova = function() {
  return window.cordova && location.protocol === "file:";
};
device.nodeWebkit = function() {
  return _typeof(window.process) === "object";
};
device.mobile = function() {
  return device.androidPhone() || device.iphone() || device.ipod() || device.windowsPhone() || device.blackberryPhone() || device.fxosPhone() || device.meego();
};
device.tablet = function() {
  return device.ipad() || device.androidTablet() || device.blackberryTablet() || device.windowsTablet() || device.fxosTablet();
};
device.desktop = function() {
  return !device.tablet() && !device.mobile();
};
device.television = function() {
  var i = 0;
  while (i < television.length) {
    if (find(television[i])) {
      return true;
    }
    i++;
  }
  return false;
};
device.portrait = function() {
  if (screen.orientation && Object.prototype.hasOwnProperty.call(window, "onorientationchange")) {
    return includes(screen.orientation.type, "portrait");
  }
  if (device.ios() && Object.prototype.hasOwnProperty.call(window, "orientation")) {
    return Math.abs(window.orientation) !== 90;
  }
  return window.innerHeight / window.innerWidth > 1;
};
device.landscape = function() {
  if (screen.orientation && Object.prototype.hasOwnProperty.call(window, "onorientationchange")) {
    return includes(screen.orientation.type, "landscape");
  }
  if (device.ios() && Object.prototype.hasOwnProperty.call(window, "orientation")) {
    return Math.abs(window.orientation) === 90;
  }
  return window.innerHeight / window.innerWidth < 1;
};
device.noConflict = function() {
  window.device = previousDevice;
  return this;
};
function includes(haystack, needle) {
  return haystack.indexOf(needle) !== -1;
}
function find(needle) {
  return includes(userAgent, needle);
}
function hasClass(className) {
  return documentElement.className.match(new RegExp(className, "i"));
}
function addClass(className) {
  var currentClassNames = null;
  if (!hasClass(className)) {
    currentClassNames = documentElement.className.replace(/^\s+|\s+$/g, "");
    documentElement.className = currentClassNames + " " + className;
  }
}
function removeClass(className) {
  if (hasClass(className)) {
    documentElement.className = documentElement.className.replace(" " + className, "");
  }
}
if (device.ios()) {
  if (device.ipad()) {
    addClass("ios ipad tablet");
  } else if (device.iphone()) {
    addClass("ios iphone mobile");
  } else if (device.ipod()) {
    addClass("ios ipod mobile");
  }
} else if (device.macos()) {
  addClass("macos desktop");
} else if (device.android()) {
  if (device.androidTablet()) {
    addClass("android tablet");
  } else {
    addClass("android mobile");
  }
} else if (device.blackberry()) {
  if (device.blackberryTablet()) {
    addClass("blackberry tablet");
  } else {
    addClass("blackberry mobile");
  }
} else if (device.windows()) {
  if (device.windowsTablet()) {
    addClass("windows tablet");
  } else if (device.windowsPhone()) {
    addClass("windows mobile");
  } else {
    addClass("windows desktop");
  }
} else if (device.fxos()) {
  if (device.fxosTablet()) {
    addClass("fxos tablet");
  } else {
    addClass("fxos mobile");
  }
} else if (device.meego()) {
  addClass("meego mobile");
} else if (device.nodeWebkit()) {
  addClass("node-webkit");
} else if (device.television()) {
  addClass("television");
} else if (device.desktop()) {
  addClass("desktop");
}
if (device.cordova()) {
  addClass("cordova");
}
function handleOrientation() {
  if (device.landscape()) {
    removeClass("portrait");
    addClass("landscape");
    walkOnChangeOrientationList("landscape");
  } else {
    removeClass("landscape");
    addClass("portrait");
    walkOnChangeOrientationList("portrait");
  }
  setOrientationCache();
}
function walkOnChangeOrientationList(newOrientation) {
  for (var index = 0; index < changeOrientationList.length; index++) {
    changeOrientationList[index](newOrientation);
  }
}
device.onChangeOrientation = function(cb) {
  if (typeof cb == "function") {
    changeOrientationList.push(cb);
  }
};
var orientationEvent = "resize";
if (Object.prototype.hasOwnProperty.call(window, "onorientationchange")) {
  orientationEvent = "orientationchange";
}
if (window.addEventListener) {
  window.addEventListener(orientationEvent, handleOrientation, false);
} else if (window.attachEvent) {
  window.attachEvent(orientationEvent, handleOrientation);
} else {
  window[orientationEvent] = handleOrientation;
}
handleOrientation();
function findMatch(arr) {
  for (var i = 0; i < arr.length; i++) {
    if (device[arr[i]]()) {
      return arr[i];
    }
  }
  return "unknown";
}
device.type = findMatch(["mobile", "tablet", "desktop"]);
device.os = findMatch(["ios", "iphone", "ipad", "ipod", "android", "blackberry", "macos", "windows", "fxos", "meego", "television"]);
function setOrientationCache() {
  device.orientation = findMatch(["portrait", "landscape"]);
}
setOrientationCache();
Object.assign(Foundation$1, {
  rtl,
  GetYoDigits,
  RegExpEscape,
  transitionend,
  onLoad,
  ignoreMousedisappear,
  Keyboard,
  Box,
  Nest,
  onImagesLoaded,
  MediaQuery,
  Motion,
  Move,
  Touch,
  Triggers,
  Timer
});
Touch.init($$1);
Triggers.init($$1, Foundation$1);
MediaQuery._init();
const plugins = [
  { plugin: Dropdown, name: "Dropdown" },
  { plugin: DropdownMenu, name: "DropdownMenu" },
  { plugin: Accordion, name: "Accordion" },
  { plugin: AccordionMenu, name: "AccordionMenu" },
  //{ plugin: ResponsiveMenu, name: 'ResponsiveMenu' },
  //{ plugin: ResponsiveToggle, name: 'ResponsiveToggle' },
  { plugin: OffCanvas, name: "OffCanvas" },
  //{ plugin: Reveal, name: 'Reveal' },
  { plugin: Tooltip, name: "Tooltip" },
  { plugin: SmoothScroll, name: "SmoothScroll" },
  //{ plugin: Magellan, name: 'Magellan' },
  { plugin: Sticky, name: "Sticky" },
  { plugin: Toggler, name: "Toggler" },
  //{ plugin: Equalizer, name: 'Equalizer' },
  //{ plugin: Interchange, name: 'Interchange' },
  { plugin: Abide, name: "Abide" }
];
plugins.forEach(({ plugin, name }) => {
  Foundation$1.plugin(plugin, name);
});
Foundation$1.addToJquery($$1);
function notEqualToValidator($el, required, parent) {
  if (!required) return true;
  let input1Value = $$1("#" + $el.attr("data-notEqualTo")).val(), input2Value = $el.val();
  return input1Value !== input2Value;
}
Foundation$1.Abide.defaults.validators["notEqualTo"] = notEqualToValidator;
$$1(() => $$1(document).foundation());
(function() {
  document.querySelectorAll('a._blank, a.blank, a[target="_blank"]').forEach((el) => {
    if (!el.hasAttribute("target") || el.getAttribute("target") !== "_blank") {
      el.setAttribute("target", "_blank");
    }
    const relValue = el == null ? void 0 : el.getAttribute("rel");
    if (!relValue || !relValue.includes("noopener") || !relValue.includes("nofollow")) {
      const newRelValue = (relValue ? relValue + " " : "") + "noopener noreferrer nofollow";
      el.setAttribute("rel", newRelValue);
    }
  });
  const images = document.querySelectorAll("img");
  images.forEach((img) => {
    img.addEventListener("error", function() {
      this.src = "data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMzAwIiBoZWlnaHQ9IjMwMCIgdmlld0JveD0iMCAwIDMwMCAzMDAiIGZpbGw9Im5vbmUiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyI+CjxyZWN0IHdpZHRoPSIzMDAiIGhlaWdodD0iMzAwIiBmaWxsPSIjRjNGNEY2Ii8+CjxwYXRoIGQ9Ik0xNTAgMTAwQzEyNy45MSAxMDAgMTEwIDExNy45MSAxMTAgMTQwQzExMCAxNjIuMDkgMTI3LjkxIDE4MCAxNTAgMTgwQzE3Mi4wOSAxODAgMTkwIDE2Mi4wOSAxOTAgMTQwQzE5MCAxMTcuOTEgMTcyLjA5IDEwMCAxNTAgMTAwWiIgZmlsbD0iI0Q5RERFMSIvPgo8L3N2Zz4K";
      this.alt = "Not found";
    });
  });
  const observer = new MutationObserver(() => {
    document.querySelectorAll('ul.sub-menu[role="menubar"]').forEach((menu) => {
      menu.setAttribute("role", "menu");
    });
    document.querySelectorAll('[aria-hidden="true"] a, [aria-hidden="true"] button').forEach((el) => {
      el.setAttribute("tabindex", "-1");
    });
  });
  observer.observe(document.body, { childList: true, subtree: true });
})();
class BackToTop {
  constructor(selector = ".back-to-top", smoothScrollEnabled = true, defaultScrollSpeed = 400) {
    this.buttonSelector = selector;
    this.smoothScrollEnabled = smoothScrollEnabled;
    this.defaultScrollSpeed = defaultScrollSpeed;
    this.init();
  }
  init() {
    if (!("querySelector" in document && "addEventListener" in window)) {
      return;
    }
    this.goTopBtn = document.querySelector(this.buttonSelector);
    if (!this.goTopBtn) {
      return;
    }
    this.scrollThreshold = parseInt(this.goTopBtn.getAttribute("data-scroll-start"), 10) || 300;
    window.addEventListener("scroll", this.trackScroll.bind(this));
    this.goTopBtn.addEventListener("click", this.scrollToTop.bind(this), false);
  }
  trackScroll() {
    const scrolled = window.scrollY;
    if (scrolled > this.scrollThreshold) {
      this.goTopBtn.classList.add("back-to-top__show");
    } else {
      this.goTopBtn.classList.remove("back-to-top__show");
    }
  }
  scrollToTop(event) {
    event.preventDefault();
    if (this.smoothScrollEnabled) {
      const duration = parseInt(this.goTopBtn.getAttribute("data-scroll-speed"), 10) || this.defaultScrollSpeed;
      this.smoothScroll(duration);
    } else {
      window.scrollTo(0, 0);
    }
  }
  smoothScroll(duration) {
    const startLocation = window.scrollY;
    const distance = -startLocation;
    let startTime = null;
    const animateScroll = (currentTime) => {
      if (!startTime) startTime = currentTime;
      const timeElapsed = currentTime - startTime;
      const run = this.easeInOutQuad(timeElapsed, startLocation, distance, duration);
      window.scrollTo(0, run);
      if (timeElapsed < duration) {
        requestAnimationFrame(animateScroll);
      }
    };
    requestAnimationFrame(animateScroll);
  }
  easeInOutQuad(t, b, c, d) {
    t /= d / 2;
    if (t < 1) return c / 2 * t * t + b;
    t--;
    return -c / 2 * (t * (t - 2) - 1) + b;
  }
}
setTimeout(() => {
  new BackToTop();
}, 100);
const scriptLoader = (timeout = 3e3, scriptSelector = 'script[data-type="lazy"]') => {
  const userInteractionEvents = ["mouseover", "keydown", "touchstart", "touchmove", "wheel"];
  const loadScriptsTimer = setTimeout(loadScripts, timeout);
  userInteractionEvents.forEach((event) => {
    window.addEventListener(event, triggerScriptLoader, { once: true, passive: true });
  });
  function triggerScriptLoader() {
    loadScripts();
    clearTimeout(loadScriptsTimer);
  }
  function loadScripts() {
    document.querySelectorAll(scriptSelector).forEach((elem) => {
      const dataSrc = elem.getAttribute("data-src");
      if (dataSrc) {
        elem.setAttribute("src", dataSrc);
        elem.removeAttribute("data-src");
        elem.removeAttribute("data-type");
      }
    });
  }
};
scriptLoader();
function initMenu(containerSelector, menuSelector) {
  const container = document.querySelector(containerSelector);
  const menu = document.querySelector(menuSelector);
  if (!container || !menu) return;
  function adjustMenu() {
    let more = menu.querySelector(".more");
    if (!more) {
      more = document.createElement("li");
      more.classList.add("more");
      more.innerHTML = '<a href="#"></a><ul class="sub-menu dropdown"></ul>';
      menu.appendChild(more);
    }
    const dropdown = more.querySelector(".dropdown");
    dropdown.innerHTML = "";
    more.style.display = "none";
    let items = [...menu.children].filter((li) => li !== more);
    items.forEach((li) => li.style.display = "block");
    if (menu.scrollWidth <= container.clientWidth) {
      removeOverflowHidden();
      return;
    }
    let hiddenItems = [];
    for (let i = items.length - 1; i >= 0; i--) {
      if (menu.scrollWidth > container.clientWidth + 80) {
        hiddenItems.unshift(items[i]);
        items[i].style.display = "none";
      } else {
        break;
      }
    }
    if (hiddenItems.length > 0) {
      hiddenItems.forEach((item) => {
        let clone = item.cloneNode(true);
        clone.style.display = "flex";
        dropdown.appendChild(clone);
      });
      more.style.display = "flex";
    }
    removeOverflowHidden();
  }
  function setOverflowHidden() {
    container.style.overflow = "hidden";
  }
  function removeOverflowHidden() {
    container.style.overflow = "visible";
  }
  function reinitializeFoundationDropdown() {
    if (typeof Foundation !== "undefined") {
      let mainNav = $(menuSelector);
      if (mainNav.length) {
        new Foundation.DropdownMenu(mainNav);
      }
    }
  }
  let resizeTimeout;
  function init() {
    setOverflowHidden();
    clearTimeout(resizeTimeout);
    resizeTimeout = setTimeout(() => {
      adjustMenu();
      reinitializeFoundationDropdown();
    }, 100);
  }
  init();
  window.addEventListener("resize", function() {
    init();
  });
}
(function() {
  const COOKIE_NAME = "cookie_consent_accepted";
  const COOKIE_EXPIRY_DAYS = 365;
  function setCookie(name, value, days) {
    const date = /* @__PURE__ */ new Date();
    date.setTime(date.getTime() + days * 24 * 60 * 60 * 1e3);
    const expires = "expires=" + date.toUTCString();
    document.cookie = name + "=" + value + ";" + expires + ";path=/;SameSite=Lax";
  }
  function getCookie(name) {
    const nameEQ = name + "=";
    const cookies = document.cookie.split(";");
    for (let i = 0; i < cookies.length; i++) {
      let cookie = cookies[i].trim();
      if (cookie.indexOf(nameEQ) === 0) {
        return cookie.substring(nameEQ.length, cookie.length);
      }
    }
    return null;
  }
  function hasConsent() {
    return getCookie(COOKIE_NAME) === "true";
  }
  function hideBanner(banner) {
    banner.classList.add("cookie-consent--hiding");
    setTimeout(function() {
      banner.style.display = "none";
      banner.remove();
    }, 300);
  }
  function showBanner(banner) {
    banner.style.display = "block";
  }
  function acceptCookies() {
    setCookie(COOKIE_NAME, "true", COOKIE_EXPIRY_DAYS);
    const banner = document.getElementById("cookie-consent-banner");
    if (banner) {
      hideBanner(banner);
    }
  }
  function init() {
    if (hasConsent()) {
      return;
    }
    const banner = document.getElementById("cookie-consent-banner");
    if (!banner) {
      return;
    }
    showBanner(banner);
    const acceptBtn = document.getElementById("cookie-consent-accept");
    if (acceptBtn) {
      acceptBtn.addEventListener("click", acceptCookies);
    }
  }
  if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", init);
  } else {
    init();
  }
})();
document.addEventListener("DOMContentLoaded", () => {
  initMenu("#main-nav", ".main-nav");
  initSocialShare("[data-social-share]", {
    intents: ["facebook", "x", "print", "send-email", "copy-link", "web-share"]
  });
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
  document.querySelectorAll(".product-nav .pro-triger").forEach((li) => {
    const link = li.querySelector(":scope > a");
    const toggle = li.querySelector(":scope > .submenu-toggle");
    const submenu = li.querySelector(":scope > ul.sub-menu");
    if (link && submenu) {
      link.addEventListener("click", (e) => {
        e.preventDefault();
        e.stopPropagation();
        li.classList.toggle("is-active");
        submenu.style.display = li.classList.contains("is-active") ? "flex" : "none";
        if (toggle) {
          toggle.setAttribute(
            "aria-expanded",
            li.classList.contains("is-active")
          );
        }
      });
    }
  });
  const MIN_WIDTH = 250;
  const widgets = document.querySelectorAll(
    ".product-attributes .widget_layered_nav"
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
      ".product-attributes .widget_layered_nav.active"
    );
    if (openWidget) adjustDropdown(openWidget);
  });
  document.querySelectorAll(".filter-tabs").forEach((group) => {
    const links = group.querySelectorAll(".tabs-nav a");
    const panels = group.querySelectorAll(".tabs-panel");
    links.forEach((link) => {
      link.addEventListener("click", (e) => {
        const href = link.getAttribute("href");
        if (!href || !href.startsWith("#") || href === "#") {
          return;
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
    var _a;
    const link = e.target.closest(".play-video");
    if (link) {
      e.preventDefault();
      const yt = link.getAttribute("data-youtube");
      const id = (_a = yt.match(/[?&]v=([^&]+)/)) == null ? void 0 : _a[1];
      if (!id) return;
      overlay.innerHTML = `<iframe width="80%" height="60%" src="https://www.youtube.com/embed/${id}?autoplay=1" frameborder="0" allowfullscreen></iframe>`;
      overlay.style.display = "flex";
    }
  });
  overlay.addEventListener("click", () => {
    overlay.style.display = "none";
    overlay.innerHTML = "";
  });
  window.addEventListener("scroll", function() {
    const header = document.getElementById("header");
    const stickyPoint = header.offsetTop;
    if (window.scrollY > stickyPoint) {
      header.classList.add("sticky");
    } else {
      header.classList.remove("sticky");
    }
  });
});
document.addEventListener("DOMContentLoaded", function() {
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
document.addEventListener("DOMContentLoaded", function() {
  var menu = document.querySelector("#product-nav ul.vertical-menu");
  if (menu) {
    menu.removeAttribute("role");
    menu.removeAttribute("aria-multiselectable");
    menu.setAttribute("aria-label", "Danh mục sản phẩm");
  }
});
document.addEventListener("DOMContentLoaded", function() {
  const mobileProductBtn = document.querySelector(".mobile-product-menu-btn");
  const mobileProductPopup = document.querySelector(".mobile-product-popup");
  const mobileProductClose = document.querySelector(
    ".mobile-product-popup__close"
  );
  const mobileProductOverlay = document.querySelector(
    ".mobile-product-popup__overlay"
  );
  if (mobileProductBtn && mobileProductPopup) {
    let closeMobileProductPopup = function() {
      mobileProductPopup.classList.remove("is-open");
      mobileProductPopup.setAttribute("aria-hidden", "true");
      document.body.style.overflow = "";
    };
    mobileProductBtn.addEventListener("click", function() {
      mobileProductPopup.classList.add("is-open");
      mobileProductPopup.setAttribute("aria-hidden", "false");
      document.body.style.overflow = "hidden";
    });
    if (mobileProductClose) {
      mobileProductClose.addEventListener("click", closeMobileProductPopup);
    }
    if (mobileProductOverlay) {
      mobileProductOverlay.addEventListener("click", closeMobileProductPopup);
    }
    document.addEventListener("keydown", function(e) {
      if (e.key === "Escape" && mobileProductPopup.classList.contains("is-open")) {
        closeMobileProductPopup();
      }
    });
  }
});
document.addEventListener("DOMContentLoaded", function() {
  const contactLinkContainer = document.querySelector(".add-this.contact-link");
  if (!contactLinkContainer) return;
  const isMobile = window.matchMedia("(max-width: 768px)").matches;
  if (isMobile) return;
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
  const items = contactLinkContainer.querySelectorAll(":scope > li");
  const itemsWrapper = document.createElement("div");
  itemsWrapper.className = "contact-items-wrapper";
  items.forEach((item) => {
    itemsWrapper.appendChild(item);
  });
  const toggleBtn = document.createElement("button");
  toggleBtn.className = "contact-toggle-btn";
  toggleBtn.setAttribute("type", "button");
  toggleBtn.setAttribute("aria-label", "Hiển thị liên hệ");
  toggleBtn.innerHTML = envelopeIcon;
  contactLinkContainer.insertBefore(toggleBtn, contactLinkContainer.firstChild);
  contactLinkContainer.appendChild(itemsWrapper);
  contactLinkContainer.classList.add("is-collapsed");
  toggleBtn.addEventListener("click", function() {
    const isCollapsed = contactLinkContainer.classList.contains("is-collapsed");
    if (isCollapsed) {
      contactLinkContainer.classList.remove("is-collapsed");
      contactLinkContainer.classList.add("is-expanded");
      toggleBtn.innerHTML = closeIcon;
      toggleBtn.setAttribute("aria-label", "Đóng");
    } else {
      contactLinkContainer.classList.add("is-collapsed");
      contactLinkContainer.classList.remove("is-expanded");
      toggleBtn.innerHTML = envelopeIcon;
      toggleBtn.setAttribute("aria-label", "Hiển thị liên hệ");
    }
  });
  window.matchMedia("(max-width: 768px)").addEventListener("change", function(e) {
    if (e.matches) {
      contactLinkContainer.classList.remove("is-collapsed", "is-expanded");
      toggleBtn.style.display = "none";
    } else {
      contactLinkContainer.classList.add("is-collapsed");
      toggleBtn.innerHTML = envelopeIcon;
      toggleBtn.style.display = "";
    }
  });
});
//# sourceMappingURL=index.js.map

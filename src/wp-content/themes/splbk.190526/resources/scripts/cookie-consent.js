/**
 * Cookie Consent Banner JavaScript
 *
 * Handles the display and acceptance of cookie consent
 */

(function () {
  "use strict";

  const COOKIE_NAME = "cookie_consent_accepted";
  const COOKIE_EXPIRY_DAYS = 365;

  /**
   * Set a cookie
   * @param {string} name - Cookie name
   * @param {string} value - Cookie value
   * @param {number} days - Days until expiration
   */
  function setCookie(name, value, days) {
    const date = new Date();
    date.setTime(date.getTime() + days * 24 * 60 * 60 * 1000);
    const expires = "expires=" + date.toUTCString();
    document.cookie =
      name + "=" + value + ";" + expires + ";path=/;SameSite=Lax";
  }

  /**
   * Get a cookie value
   * @param {string} name - Cookie name
   * @returns {string|null} Cookie value or null
   */
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

  /**
   * Check if consent was already given
   * @returns {boolean}
   */
  function hasConsent() {
    return getCookie(COOKIE_NAME) === "true";
  }

  /**
   * Hide the banner with animation
   * @param {HTMLElement} banner - The banner element
   */
  function hideBanner(banner) {
    banner.classList.add("cookie-consent--hiding");
    setTimeout(function () {
      banner.style.display = "none";
      banner.remove();
    }, 300);
  }

  /**
   * Show the banner
   * @param {HTMLElement} banner - The banner element
   */
  function showBanner(banner) {
    banner.style.display = "block";
  }

  /**
   * Handle accept button click
   */
  function acceptCookies() {
    setCookie(COOKIE_NAME, "true", COOKIE_EXPIRY_DAYS);
    const banner = document.getElementById("cookie-consent-banner");
    if (banner) {
      hideBanner(banner);
    }
  }

  /**
   * Initialize the cookie consent
   */
  function init() {
    // Check if already accepted
    if (hasConsent()) {
      return;
    }

    const banner = document.getElementById("cookie-consent-banner");
    if (!banner) {
      return;
    }

    // Show the banner
    showBanner(banner);

    // Bind accept button
    const acceptBtn = document.getElementById("cookie-consent-accept");
    if (acceptBtn) {
      acceptBtn.addEventListener("click", acceptCookies);
    }
  }

  // Initialize when DOM is ready
  if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", init);
  } else {
    init();
  }
})();

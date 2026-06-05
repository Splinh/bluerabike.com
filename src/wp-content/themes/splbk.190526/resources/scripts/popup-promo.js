/**
 * Promotional Popup JavaScript
 * Handles popup display, form submission, cookie management, and snow effect
 */

(function () {
  "use strict";

  // Configuration
  const COOKIE_NAME = "promo_popup_seen";

  // DOM Elements
  const popupOverlay = document.getElementById("promo-popup-overlay");
  if (!popupOverlay) return;

  const closeBtn = popupOverlay.querySelector(".promo-popup-close");
  const popupForm = document.getElementById("promo-popup-form");
  const effectContainer = popupOverlay.querySelector(".promo-popup-effect");

  // Get data attributes
  const cookieDays = parseInt(popupOverlay.dataset.cookieDays) || 7;
  const delay = parseInt(popupOverlay.dataset.delay) || 2000;
  const effectType = popupOverlay.dataset.effect || "none";

  /**
   * Cookie Management
   */
  function setCookie(name, value, days) {
    const date = new Date();
    date.setTime(date.getTime() + days * 24 * 60 * 60 * 1000);
    const expires = "expires=" + date.toUTCString();
    document.cookie = name + "=" + value + ";" + expires + ";path=/";
  }

  function getCookie(name) {
    const nameEQ = name + "=";
    const cookies = document.cookie.split(";");
    for (let i = 0; i < cookies.length; i++) {
      let c = cookies[i];
      while (c.charAt(0) === " ") c = c.substring(1, c.length);
      if (c.indexOf(nameEQ) === 0) return c.substring(nameEQ.length, c.length);
    }
    return null;
  }

  /**
   * Show Popup
   */
  function showPopup() {
    popupOverlay.classList.add("active");
    document.body.style.overflow = "hidden";

    // Initialize seasonal effect if enabled
    if (effectType !== "none" && effectContainer) {
      createSeasonalEffect();
    }
  }

  /**
   * Hide Popup
   */
  function hidePopup() {
    popupOverlay.classList.remove("active");
    document.body.style.overflow = "";
    // Don't save cookie - popup will show again on next page load
    // setCookie(COOKIE_NAME, "true", cookieDays);
  }

  /**
   * Create Seasonal Effect based on type
   */
  function createSeasonalEffect() {
    // Clear existing elements
    effectContainer.innerHTML = "";

    switch (effectType) {
      case "mai":
        createPetals("mai");
        break;
      case "dao":
        createPetals("dao");
        break;
      case "snow":
        createSnowflakes();
        break;
      default:
        break;
    }
  }

  /**
   * Create Flower Petals (Hoa Mai / Hoa Dao)
   */
  function createPetals(type) {
    const numberOfPetals = 50;

    for (let i = 0; i < numberOfPetals; i++) {
      const petal = document.createElement("div");
      petal.className = type === "mai" ? "petal-mai" : "petal-dao";

      // Random positioning and styling
      const size = Math.random() * 8 + 8; // 8-16px
      petal.style.left = Math.random() * 100 + "%";
      petal.style.width = size + "px";
      petal.style.height = size + "px";
      petal.style.animationDuration = Math.random() * 4 + 4 + "s"; // 4-8s
      petal.style.animationDelay = Math.random() * 3 + "s";
      petal.style.opacity = Math.random() * 0.3 + 0.6; // 0.6-0.9

      effectContainer.appendChild(petal);
    }
  }

  /**
   * Create Snow Effect
   */
  function createSnowflakes() {
    const numberOfFlakes = 50;
    const snowflakes = ["❅", "❆", "❄"];

    for (let i = 0; i < numberOfFlakes; i++) {
      const snowflake = document.createElement("div");
      snowflake.className = "snowflake";
      snowflake.textContent =
        snowflakes[Math.floor(Math.random() * snowflakes.length)];

      // Random positioning
      snowflake.style.left = Math.random() * 100 + "%";
      snowflake.style.fontSize = Math.random() * 10 + 10 + "px";
      snowflake.style.animationDuration = Math.random() * 3 + 2 + "s";
      snowflake.style.animationDelay = Math.random() * 2 + "s";

      effectContainer.appendChild(snowflake);
    }
  }

  /**
   * Form Submission Handler
   */
  function handleFormSubmit(e) {
    e.preventDefault();

    const formData = new FormData(popupForm);
    const name = formData.get("customer_name");
    const phone = formData.get("customer_phone");

    // Basic validation
    if (!name || !phone) {
      alert("Vui lòng điền đầy đủ thông tin!");
      return;
    }

    // Phone validation
    const phonePattern = /^[0-9]{10,11}$/;
    if (!phonePattern.test(phone)) {
      alert("Số điện thoại không hợp lệ!");
      return;
    }

    // Submit data
    submitFormData(name, phone);
  }

  /**
   * Submit Form Data via AJAX
   */
  function submitFormData(name, phone) {
    const submitBtn = popupForm.querySelector(".promo-form-submit");
    const originalText = submitBtn.textContent;

    // Disable button during submission
    submitBtn.disabled = true;
    submitBtn.textContent = "Đang gửi...";

    // Prepare data
    const data = {
      action: "submit_promo_form",
      name: name,
      phone: phone,
      nonce: window.hdConfig?.restToken || "",
    };

    // Send AJAX request
    fetch(window.hdConfig?.ajaxUrl || "/wp-admin/admin-ajax.php", {
      method: "POST",
      headers: {
        "Content-Type": "application/x-www-form-urlencoded",
      },
      body: new URLSearchParams(data),
    })
      .then((response) => response.json())
      .then((result) => {
        if (result.success) {
          alert("Cảm ơn bạn đã đăng ký! Chúng tôi sẽ liên hệ sớm.");
          popupForm.reset();
          hidePopup();
        } else {
          alert(result.data?.message || "Đã có lỗi xảy ra. Vui lòng thử lại!");
        }
      })
      .catch((error) => {
        console.error("Form submission error:", error);
        alert("Đã có lỗi xảy ra. Vui lòng thử lại!");
      })
      .finally(() => {
        submitBtn.disabled = false;
        submitBtn.textContent = originalText;
      });
  }

  /**
   * Event Listeners
   */

  // Close button
  if (closeBtn) {
    closeBtn.addEventListener("click", hidePopup);
  }

  // Click outside to close
  popupOverlay.addEventListener("click", function (e) {
    if (e.target === popupOverlay) {
      hidePopup();
    }
  });

  // ESC key to close
  document.addEventListener("keydown", function (e) {
    if (e.key === "Escape" && popupOverlay.classList.contains("active")) {
      hidePopup();
    }
  });

  // Form submission
  if (popupForm) {
    popupForm.addEventListener("submit", handleFormSubmit);
  }

  /**
   * Initialize Popup
   */
  function initPopup() {
    // Don't auto-show popup anymore - only show when user clicks the trigger button
    // Comment out automatic popup display:
    // if (getCookie(COOKIE_NAME)) {
    //   return;
    // }
    // setTimeout(() => {
    //   showPopup();
    // }, delay);

    // Add event listener for trigger button
    const triggerBtn = document.getElementById("promo-popup-trigger");
    if (triggerBtn) {
      triggerBtn.addEventListener("click", function (e) {
        e.preventDefault();
        showPopup();
      });
    }
  }

  // Initialize when DOM is ready
  if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", initPopup);
  } else {
    initPopup();
  }
})();

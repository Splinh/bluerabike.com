(function() {
  const popupOverlay = document.getElementById("promo-popup-overlay");
  if (!popupOverlay) return;
  const closeBtn = popupOverlay.querySelector(".promo-popup-close");
  const popupForm = document.getElementById("promo-popup-form");
  const effectContainer = popupOverlay.querySelector(".promo-popup-effect");
  parseInt(popupOverlay.dataset.cookieDays) || 7;
  parseInt(popupOverlay.dataset.delay) || 2e3;
  const effectType = popupOverlay.dataset.effect || "none";
  function showPopup() {
    popupOverlay.classList.add("active");
    document.body.style.overflow = "hidden";
    if (effectType !== "none" && effectContainer) {
      createSeasonalEffect();
    }
  }
  function hidePopup() {
    popupOverlay.classList.remove("active");
    document.body.style.overflow = "";
  }
  function createSeasonalEffect() {
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
    }
  }
  function createPetals(type) {
    const numberOfPetals = 50;
    for (let i = 0; i < numberOfPetals; i++) {
      const petal = document.createElement("div");
      petal.className = type === "mai" ? "petal-mai" : "petal-dao";
      const size = Math.random() * 8 + 8;
      petal.style.left = Math.random() * 100 + "%";
      petal.style.width = size + "px";
      petal.style.height = size + "px";
      petal.style.animationDuration = Math.random() * 4 + 4 + "s";
      petal.style.animationDelay = Math.random() * 3 + "s";
      petal.style.opacity = Math.random() * 0.3 + 0.6;
      effectContainer.appendChild(petal);
    }
  }
  function createSnowflakes() {
    const numberOfFlakes = 50;
    const snowflakes = ["❅", "❆", "❄"];
    for (let i = 0; i < numberOfFlakes; i++) {
      const snowflake = document.createElement("div");
      snowflake.className = "snowflake";
      snowflake.textContent = snowflakes[Math.floor(Math.random() * snowflakes.length)];
      snowflake.style.left = Math.random() * 100 + "%";
      snowflake.style.fontSize = Math.random() * 10 + 10 + "px";
      snowflake.style.animationDuration = Math.random() * 3 + 2 + "s";
      snowflake.style.animationDelay = Math.random() * 2 + "s";
      effectContainer.appendChild(snowflake);
    }
  }
  function handleFormSubmit(e) {
    e.preventDefault();
    const formData = new FormData(popupForm);
    const name = formData.get("customer_name");
    const phone = formData.get("customer_phone");
    if (!name || !phone) {
      alert("Vui lòng điền đầy đủ thông tin!");
      return;
    }
    const phonePattern = /^[0-9]{10,11}$/;
    if (!phonePattern.test(phone)) {
      alert("Số điện thoại không hợp lệ!");
      return;
    }
    submitFormData(name, phone);
  }
  function submitFormData(name, phone) {
    var _a, _b;
    const submitBtn = popupForm.querySelector(".promo-form-submit");
    const originalText = submitBtn.textContent;
    submitBtn.disabled = true;
    submitBtn.textContent = "Đang gửi...";
    const data = {
      action: "submit_promo_form",
      name,
      phone,
      nonce: ((_a = window.hdConfig) == null ? void 0 : _a.restToken) || ""
    };
    fetch(((_b = window.hdConfig) == null ? void 0 : _b.ajaxUrl) || "/wp-admin/admin-ajax.php", {
      method: "POST",
      headers: {
        "Content-Type": "application/x-www-form-urlencoded"
      },
      body: new URLSearchParams(data)
    }).then((response) => response.json()).then((result) => {
      var _a2;
      if (result.success) {
        alert("Cảm ơn bạn đã đăng ký! Chúng tôi sẽ liên hệ sớm.");
        popupForm.reset();
        hidePopup();
      } else {
        alert(((_a2 = result.data) == null ? void 0 : _a2.message) || "Đã có lỗi xảy ra. Vui lòng thử lại!");
      }
    }).catch((error) => {
      console.error("Form submission error:", error);
      alert("Đã có lỗi xảy ra. Vui lòng thử lại!");
    }).finally(() => {
      submitBtn.disabled = false;
      submitBtn.textContent = originalText;
    });
  }
  if (closeBtn) {
    closeBtn.addEventListener("click", hidePopup);
  }
  popupOverlay.addEventListener("click", function(e) {
    if (e.target === popupOverlay) {
      hidePopup();
    }
  });
  document.addEventListener("keydown", function(e) {
    if (e.key === "Escape" && popupOverlay.classList.contains("active")) {
      hidePopup();
    }
  });
  if (popupForm) {
    popupForm.addEventListener("submit", handleFormSubmit);
  }
  function initPopup() {
    const triggerBtn = document.getElementById("promo-popup-trigger");
    if (triggerBtn) {
      triggerBtn.addEventListener("click", function(e) {
        e.preventDefault();
        showPopup();
      });
    }
  }
  if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", initPopup);
  } else {
    initPopup();
  }
})();
//# sourceMappingURL=popup-promo.js.map

jQuery(function ($) {
  $(document.body).on(
    "added_to_cart",
    function (e, fragments, cart_hash, $button) {
      showNotification(window.hdConfig.lang.added_to_cart);
    }
  );

  /* variation price */
  let variations_form = $(".variations_form");
  if (variations_form.length > 0) {
    variations_form.each(function () {
      // when variation is found, do something
      $(this).on("found_variation", function (event, variation) {
        if (variation.price_html !== "") {
          $(".product-detail .single-price").html(variation.price_html);
        }
      });
    });

    // Auto-select first variation on page load
    variations_form.each(function () {
      const $form = $(this);
      const $selects = $form.find(".variations select");

      // Select first available option for each attribute
      $selects.each(function () {
        const $select = $(this);
        const $options = $select.find("option");

        // Find first option with value (skip empty "Choose an option")
        $options.each(function () {
          if ($(this).val() !== "") {
            $select.val($(this).val());
            return false; // break loop
          }
        });
      });

      // Trigger change to update variation
      $selects.first().trigger("change");
    });
  }

  /* Mini-cart */

  // Click +/- buttons
  $(".mini-cart-dropdown").on("click", ".plus, .minus", function (e) {
    const $btn = $(e.target);
    const $qtyInput = $btn.closest(".qty-control").find(".qty");
    let qty = parseInt($qtyInput.val(), 10) || 1;
    const min = parseInt($qtyInput.attr("min"), 10) || 1;

    const $cartItem = $btn.closest("[data-cart-item-key]");
    const cartItemKey = $cartItem.data("cart-item-key");

    if (!cartItemKey) return;

    if ($btn.hasClass("plus")) {
      qty++;
    } else if ($btn.hasClass("minus")) {
      qty = Math.max(qty - 1, min);
    }

    $qtyInput.val(qty);
    toggleMinusDisabled($qtyInput);
    updateMiniCartQty(cartItemKey, qty);
  });

  // Manual input change
  $(".mini-cart-dropdown").on("change", ".qty", function () {
    const $input = $(this);
    const qty = parseInt($input.val(), 10) || 0;
    const $cartItem = $input.closest("[data-cart-item-key]");
    const cartItemKey = $cartItem.data("cart-item-key");

    if (cartItemKey) {
      toggleMinusDisabled($input);
      updateMiniCartQty(cartItemKey, qty);
    }
  });

  // Disable minus if qty <= min
  function toggleMinusDisabled($input) {
    const min = parseInt($input.attr("min"), 10) || 1;
    const qty = parseInt($input.val(), 10) || 0;
    const $minus = $input.closest(".qty-control").find(".minus");

    if (qty <= min) {
      $minus.prop("disabled", true);
    } else {
      $minus.prop("disabled", false);
    }
  }

  // AJAX update mini-cart
  function updateMiniCartQty(cartItemKey, quantity) {
    $.post(
      window.hdConfig.ajaxUrl,
      {
        action: "update_mini_cart_qty",
        cart_item_key: cartItemKey,
        quantity: quantity,
      },
      function (data) {
        if (data && data.fragments) {
          $.each(data.fragments, function (selector, html) {
            const $content = $("<div>").html(html).find(selector).html();
            $(selector).html($content);

            // Re-apply disable state for minus buttons
            if (selector === ".mini-cart-dropdown") {
              $(selector)
                .find(".qty")
                .each(function () {
                  toggleMinusDisabled($(this));
                });
            }
          });

          $(document.body).trigger("wc_fragments_refreshed");
        }
      },
      "json"
    );
  }

  // Init once on page load
  $(".mini-cart-dropdown .qty").each(function () {
    toggleMinusDisabled($(this));
  });

  /* OTP login form */

  const loginform = document.querySelector("#loginform");
  if (loginform) {
    loginform.classList.add("otp-loginform");
    const submitBtn = loginform.querySelector('[type="submit"]');

    // Enforce numeric-only input for numeric inputmode elements.
    const inputEl = loginform.querySelector(
      'input.authcode[inputmode="numeric"]'
    );
    const expectedLength = Number(inputEl?.dataset.digits) || 0;
    if (inputEl) {
      inputEl.addEventListener("input", function () {
        let value = this.value.replace(/[^0-9 ]/g, "").trimStart();
        this.value = value;

        // Auto submit if it's the expected length.
        if (
          expectedLength &&
          value.replace(/ /g, "").length === expectedLength &&
          submitBtn &&
          !submitBtn.disabled
        ) {
          if (typeof loginform.requestSubmit === "function") {
            loginform.requestSubmit();
            submitBtn.disabled = true;
          }
        }
      });
    }

    // Countdown
    const timer = loginform.querySelector("#countdown");
    if (!timer) return;

    let remaining = Number(timer.dataset.time) || 0;
    const render = () => {
      const mm = String(Math.floor(remaining / 60)).padStart(2, "0");
      const ss = String(remaining % 60).padStart(2, "0");
      timer.textContent = `${mm}:${ss}`;
    };

    const start = () => {
      if (remaining <= 0) {
        render();
        if (submitBtn) submitBtn.disabled = true;
        return;
      }
      render();

      const id = setInterval(() => {
        remaining--;
        render();
        if (remaining <= 0) {
          clearInterval(id);
          if (submitBtn) submitBtn.disabled = true;
        }
      }, 1000);
    };

    start();
  }

  /* Mobile Filter Sidebar */
  const mobileFilterToggle = document.querySelector(".mobile-filter-toggle");
  const cellSidebar = document.querySelector(".cell-sidebar");
  const mobileOverlay = document.querySelector(".mobile-sidebar-overlay");

  if (mobileFilterToggle && cellSidebar) {
    // Open sidebar
    mobileFilterToggle.addEventListener("click", function () {
      cellSidebar.classList.add("active");
      if (mobileOverlay) mobileOverlay.classList.add("active");
      document.body.style.overflow = "hidden";
    });

    // Close on overlay click
    if (mobileOverlay) {
      mobileOverlay.addEventListener("click", closeSidebar);
    }

    // Close on ESC key
    document.addEventListener("keydown", function (e) {
      if (e.key === "Escape" && cellSidebar.classList.contains("active")) {
        closeSidebar();
      }
    });

    function closeSidebar() {
      cellSidebar.classList.remove("active");
      if (mobileOverlay) mobileOverlay.classList.remove("active");
      document.body.style.overflow = "";
    }
  }

  /* Orderby-trigger */

  const trigger = document.querySelector(".woocommerce-orderby-trigger");
  const select = document.querySelector("select.orderby");
  const form = select?.closest("form");

  if (trigger && select) {
    const ul = trigger.querySelector("ul");
    const lis = ul.querySelectorAll("li");
    trigger.addEventListener("click", function (e) {
      e.stopPropagation();
      trigger.classList.toggle("open");
    });

    document.addEventListener("click", function (e) {
      if (!trigger.contains(e.target)) {
        trigger.classList.remove("open");
      }
    });

    lis.forEach((li) => {
      li.addEventListener("click", function (e) {
        e.stopPropagation();

        const value = li.getAttribute("data-id");
        const option = select.querySelector(`option[value="${value}"]`);
        if (option) {
          select.value = value;
          if (form) {
            form.submit();
          } else {
            const changeEvent = new Event("change", { bubbles: true });
            select.dispatchEvent(changeEvent);
          }

          lis.forEach((item) => item.classList.remove("selected"));
          li.classList.add("selected");

          const span = trigger.querySelector("span");
          if (span) {
            span.textContent = li.textContent;
          }

          trigger.classList.remove("open");
        }
      });
    });
  }
});

//
// Notification system
function showNotification(message) {
  // Remove existing notifications
  const existingNotifications = document.querySelectorAll(".notification");
  existingNotifications.forEach((notif) => notif.remove());

  // Create notification element
  const notification = document.createElement("div");
  notification.className = "notification";
  notification.textContent = message;
  notification.style.cssText = `
        position: fixed;
        top: 50px;
        right: 20px;
        background: #fcc116;
        color: #3E3E3E;
        padding: 10px 20px;
        font-size: 15px;
        border-radius: 8px;
        box-shadow: 0 4px 15px rgba(0,0,0,0.2);
        z-index: 10000;
        font-weight: 700;
        transform: translateX(100%);
        transition: transform 0.3s ease;
    `;

  document.body.appendChild(notification);

  setTimeout(() => {
    notification.style.transform = "translateX(0)";
  }, 100);

  setTimeout(() => {
    notification.style.transform = "translateX(100%)";
    setTimeout(() => {
      notification.remove();
    }, 300);
  }, 3000);
}

// Shop Filter
import "./shop-filter";

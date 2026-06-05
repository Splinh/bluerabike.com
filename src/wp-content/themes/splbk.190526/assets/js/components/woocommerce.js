const ShopFilter = {
  container: null,
  productsWrapper: null,
  paginationWrapper: null,
  isLoading: false,
  debounceTimer: null,
  init() {
    this.container = document.querySelector("[data-filter-container]");
    this.productsWrapper = document.querySelector(".products");
    this.paginationWrapper = document.querySelector(".woocommerce-pagination");
    if (!this.container) return;
    this.initPriceSlider();
    this.bindEvents();
    this.initFromUrl();
  },
  initPriceSlider() {
    const wrapper = this.container.querySelector(".price-slider-wrapper");
    if (!wrapper) return;
    const minSlider = wrapper.querySelector(".min-slider");
    const maxSlider = wrapper.querySelector(".max-slider");
    const minValue = wrapper.querySelector(".min-value");
    const maxValue = wrapper.querySelector(".max-value");
    const sliderRange = wrapper.querySelector(".slider-range");
    const minHidden = wrapper.querySelector(".min-price");
    const maxHidden = wrapper.querySelector(".max-price");
    if (!minSlider || !maxSlider) return;
    const min = parseFloat(wrapper.dataset.min) || 0;
    const max = parseFloat(wrapper.dataset.max) || 1e6;
    const updateSlider = () => {
      let minVal = parseFloat(minSlider.value);
      let maxVal = parseFloat(maxSlider.value);
      if (minVal > maxVal - 1e4) {
        minVal = maxVal - 1e4;
        minSlider.value = minVal;
      }
      if (minHidden) minHidden.value = minVal;
      if (maxHidden) maxHidden.value = maxVal;
      if (minValue) minValue.textContent = this.formatPrice(minVal);
      if (maxValue) maxValue.textContent = this.formatPrice(maxVal);
      if (sliderRange) {
        const percentMin = (minVal - min) / (max - min) * 100;
        const percentMax = (maxVal - min) / (max - min) * 100;
        sliderRange.style.left = percentMin + "%";
        sliderRange.style.width = percentMax - percentMin + "%";
      }
    };
    const handleSliderChange = () => {
      updateSlider();
      clearTimeout(this.debounceTimer);
      this.debounceTimer = setTimeout(() => {
        this.applyFilters();
      }, 500);
    };
    minSlider.addEventListener("input", updateSlider);
    maxSlider.addEventListener("input", updateSlider);
    minSlider.addEventListener("change", handleSliderChange);
    maxSlider.addEventListener("change", handleSliderChange);
    updateSlider();
  },
  formatPrice(value) {
    return new Intl.NumberFormat("vi-VN").format(value) + "₫";
  },
  bindEvents() {
    this.container.querySelectorAll('.filter-category input[type="checkbox"]').forEach((checkbox) => {
      checkbox.addEventListener("change", (e) => {
        if (e.target.checked) {
          this.container.querySelectorAll('.filter-category input[type="checkbox"]').forEach((cb) => {
            if (cb !== e.target) cb.checked = false;
          });
        }
        this.applyFilters();
      });
    });
    this.container.querySelectorAll('.filter-attribute input[type="checkbox"]').forEach((checkbox) => {
      checkbox.addEventListener("change", () => this.applyFilters());
    });
    const clearBtn = this.container.querySelector(".filter-clear");
    if (clearBtn) {
      clearBtn.addEventListener("click", () => this.clearFilters());
    }
    const orderbySelect = document.querySelector(
      ".woocommerce-ordering select"
    );
    if (orderbySelect) {
      orderbySelect.addEventListener("change", () => this.applyFilters());
    }
    this.container.querySelectorAll(".show-more-btn").forEach((btn) => {
      btn.addEventListener("click", () => {
        const section = btn.closest(".filter-section");
        const hiddenItems = section.querySelectorAll(".hidden-item");
        const isExpanded = section.classList.contains("expanded");
        if (isExpanded) {
          section.classList.remove("expanded");
          hiddenItems.forEach((item) => item.style.display = "none");
          btn.innerHTML = `${btn.dataset.showText} <span class="more-count">(+${hiddenItems.length})</span>`;
        } else {
          section.classList.add("expanded");
          hiddenItems.forEach((item) => item.style.display = "");
          btn.textContent = btn.dataset.hideText;
        }
      });
    });
    document.addEventListener("click", (e) => {
      const pageLink = e.target.closest(".woocommerce-pagination a");
      if (pageLink) {
        e.preventDefault();
        const url = new URL(pageLink.href);
        const paged = url.searchParams.get("paged") || 1;
        this.applyFilters(parseInt(paged, 10));
      }
    });
  },
  initFromUrl() {
    const params = new URLSearchParams(window.location.search);
    const wrapper = this.container.querySelector(".price-slider-wrapper");
    if (wrapper) {
      const minPrice = params.get("min_price");
      const maxPrice = params.get("max_price");
      const minSlider = wrapper.querySelector(".min-slider");
      const maxSlider = wrapper.querySelector(".max-slider");
      if (minPrice && minSlider) minSlider.value = minPrice;
      if (maxPrice && maxSlider) maxSlider.value = maxPrice;
      if (minSlider) minSlider.dispatchEvent(new Event("input"));
    }
    this.container.querySelectorAll(".filter-attribute").forEach((section) => {
      const attrName = section.dataset.attribute;
      const filterValue = params.get("filter_" + attrName);
      if (filterValue) {
        const values = filterValue.split(",");
        section.querySelectorAll('input[type="checkbox"]').forEach((cb) => {
          cb.checked = values.includes(cb.value);
        });
      }
    });
  },
  getFilterData() {
    var _a;
    const data = {
      action: "spl_shop_filter",
      nonce: ((_a = window.splShopFilter) == null ? void 0 : _a.nonce) || "",
      min_price: 0,
      max_price: 0,
      category: 0,
      attributes: {},
      orderby: "menu_order",
      paged: 1,
      per_page: 12
    };
    const minInput = this.container.querySelector(".min-price");
    const maxInput = this.container.querySelector(".max-price");
    if (minInput) data.min_price = parseFloat(minInput.value) || 0;
    if (maxInput) data.max_price = parseFloat(maxInput.value) || 0;
    this.container.querySelectorAll(".filter-attribute").forEach((section) => {
      const attrName = section.dataset.attribute;
      const checked = section.querySelectorAll(
        'input[type="checkbox"]:checked'
      );
      if (checked.length > 0) {
        data.attributes[attrName] = Array.from(checked).map((cb) => cb.value);
      }
    });
    const categoryCheckbox = this.container.querySelector(
      '.filter-category input[type="checkbox"]:checked'
    );
    if (categoryCheckbox) {
      data.category = parseInt(categoryCheckbox.value, 10);
    } else {
      const categoryMatch = document.body.className.match(/term-(\d+)/);
      if (categoryMatch) {
        data.category = parseInt(categoryMatch[1], 10);
      }
    }
    const orderbySelect = document.querySelector(
      ".woocommerce-ordering select"
    );
    if (orderbySelect) {
      data.orderby = orderbySelect.value;
    }
    return data;
  },
  applyFilters(paged = 1) {
    var _a;
    if (this.isLoading) return;
    const data = this.getFilterData();
    data.paged = paged;
    this.setLoading(true);
    this.updateUrl(data);
    fetch(((_a = window.splShopFilter) == null ? void 0 : _a.ajaxUrl) || "/wp-admin/admin-ajax.php", {
      method: "POST",
      headers: {
        "Content-Type": "application/x-www-form-urlencoded"
      },
      body: this.buildFormData(data)
    }).then((response) => response.json()).then((result) => {
      if (result.success) {
        this.updateProducts(result.data.products);
        this.updatePagination(result.data.pagination);
        this.closeMobileSidebar();
        this.scrollToProducts();
      }
    }).catch((error) => {
      console.error("Shop Filter Error:", error);
    }).finally(() => {
      this.setLoading(false);
    });
  },
  buildFormData(data) {
    const params = new URLSearchParams();
    for (const [key, value] of Object.entries(data)) {
      if (key === "attributes") {
        for (const [attrName, attrValues] of Object.entries(value)) {
          attrValues.forEach((val) => {
            params.append(`attributes[${attrName}][]`, val);
          });
        }
      } else {
        params.append(key, value);
      }
    }
    return params.toString();
  },
  updateProducts(html) {
    if (this.productsWrapper) {
      this.productsWrapper.outerHTML = html;
      this.productsWrapper = document.querySelector(".products");
    } else {
      const content = document.querySelector(".cell-content");
      if (content) {
        const existingInfo = content.querySelector(".woocommerce-info");
        if (existingInfo) {
          existingInfo.outerHTML = html;
        } else {
          content.insertAdjacentHTML("beforeend", html);
        }
        this.productsWrapper = document.querySelector(".products");
      }
    }
  },
  updatePagination(html) {
    if (this.paginationWrapper) {
      this.paginationWrapper.outerHTML = html;
    } else {
      const products = document.querySelector(".products");
      if (products) {
        products.insertAdjacentHTML("afterend", html);
      }
    }
    this.paginationWrapper = document.querySelector(".woocommerce-pagination");
  },
  updateUrl(data) {
    const url = new URL(window.location.href);
    ["min_price", "max_price", "paged"].forEach(
      (key) => url.searchParams.delete(key)
    );
    Array.from(url.searchParams.keys()).filter((key) => key.startsWith("filter_")).forEach((key) => url.searchParams.delete(key));
    if (data.min_price > 0) url.searchParams.set("min_price", data.min_price);
    if (data.max_price > 0) url.searchParams.set("max_price", data.max_price);
    if (data.paged > 1) url.searchParams.set("paged", data.paged);
    for (const [attrName, attrValues] of Object.entries(data.attributes)) {
      if (attrValues.length > 0) {
        url.searchParams.set("filter_" + attrName, attrValues.join(","));
      }
    }
    window.history.pushState({}, "", url.toString());
  },
  clearFilters() {
    const wrapper = this.container.querySelector(".price-slider-wrapper");
    if (wrapper) {
      const minSlider = wrapper.querySelector(".min-slider");
      const maxSlider = wrapper.querySelector(".max-slider");
      if (minSlider) minSlider.value = wrapper.dataset.min || 0;
      if (maxSlider) maxSlider.value = wrapper.dataset.max || 1e6;
      if (minSlider) minSlider.dispatchEvent(new Event("input"));
    }
    this.container.querySelectorAll('input[type="checkbox"]').forEach((cb) => {
      cb.checked = false;
    });
    const orderbySelect = document.querySelector(
      ".woocommerce-ordering select"
    );
    if (orderbySelect) {
      orderbySelect.value = "menu_order";
    }
    this.applyFilters(1);
  },
  setLoading(loading) {
    this.isLoading = loading;
    if (loading) {
      document.body.classList.add("spl-filter-loading");
      if (this.productsWrapper) {
        this.productsWrapper.classList.add("loading");
      }
    } else {
      document.body.classList.remove("spl-filter-loading");
      if (this.productsWrapper) {
        this.productsWrapper.classList.remove("loading");
      }
    }
  },
  closeMobileSidebar() {
    if (window.innerWidth > 768) return;
    const cellSidebar = document.querySelector(".cell-sidebar");
    const mobileOverlay = document.querySelector(".mobile-sidebar-overlay");
    if (cellSidebar && cellSidebar.classList.contains("active")) {
      cellSidebar.classList.remove("active");
      if (mobileOverlay) mobileOverlay.classList.remove("active");
      document.body.style.overflow = "";
    }
  },
  scrollToProducts() {
    const target = document.querySelector(".cell-content");
    if (target) {
      target.scrollIntoView({ behavior: "smooth", block: "start" });
    }
  }
};
if (document.readyState === "loading") {
  document.addEventListener("DOMContentLoaded", () => ShopFilter.init());
} else {
  ShopFilter.init();
}
jQuery(function($) {
  $(document.body).on(
    "added_to_cart",
    function(e, fragments, cart_hash, $button) {
      showNotification(window.hdConfig.lang.added_to_cart);
    }
  );
  let variations_form = $(".variations_form");
  if (variations_form.length > 0) {
    variations_form.each(function() {
      $(this).on("found_variation", function(event, variation) {
        if (variation.price_html !== "") {
          $(".product-detail .single-price").html(variation.price_html);
        }
      });
    });
    variations_form.each(function() {
      const $form = $(this);
      const $selects = $form.find(".variations select");
      $selects.each(function() {
        const $select = $(this);
        const $options = $select.find("option");
        $options.each(function() {
          if ($(this).val() !== "") {
            $select.val($(this).val());
            return false;
          }
        });
      });
      $selects.first().trigger("change");
    });
  }
  $(".mini-cart-dropdown").on("click", ".plus, .minus", function(e) {
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
  $(".mini-cart-dropdown").on("change", ".qty", function() {
    const $input = $(this);
    const qty = parseInt($input.val(), 10) || 0;
    const $cartItem = $input.closest("[data-cart-item-key]");
    const cartItemKey = $cartItem.data("cart-item-key");
    if (cartItemKey) {
      toggleMinusDisabled($input);
      updateMiniCartQty(cartItemKey, qty);
    }
  });
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
  function updateMiniCartQty(cartItemKey, quantity) {
    $.post(
      window.hdConfig.ajaxUrl,
      {
        action: "update_mini_cart_qty",
        cart_item_key: cartItemKey,
        quantity
      },
      function(data) {
        if (data && data.fragments) {
          $.each(data.fragments, function(selector, html) {
            const $content = $("<div>").html(html).find(selector).html();
            $(selector).html($content);
            if (selector === ".mini-cart-dropdown") {
              $(selector).find(".qty").each(function() {
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
  $(".mini-cart-dropdown .qty").each(function() {
    toggleMinusDisabled($(this));
  });
  const loginform = document.querySelector("#loginform");
  if (loginform) {
    loginform.classList.add("otp-loginform");
    const submitBtn = loginform.querySelector('[type="submit"]');
    const inputEl = loginform.querySelector(
      'input.authcode[inputmode="numeric"]'
    );
    const expectedLength = Number(inputEl == null ? void 0 : inputEl.dataset.digits) || 0;
    if (inputEl) {
      inputEl.addEventListener("input", function() {
        let value = this.value.replace(/[^0-9 ]/g, "").trimStart();
        this.value = value;
        if (expectedLength && value.replace(/ /g, "").length === expectedLength && submitBtn && !submitBtn.disabled) {
          if (typeof loginform.requestSubmit === "function") {
            loginform.requestSubmit();
            submitBtn.disabled = true;
          }
        }
      });
    }
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
      }, 1e3);
    };
    start();
  }
  const mobileFilterToggle = document.querySelector(".mobile-filter-toggle");
  const cellSidebar = document.querySelector(".cell-sidebar");
  const mobileOverlay = document.querySelector(".mobile-sidebar-overlay");
  if (mobileFilterToggle && cellSidebar) {
    let closeSidebar2 = function() {
      cellSidebar.classList.remove("active");
      if (mobileOverlay) mobileOverlay.classList.remove("active");
      document.body.style.overflow = "";
    };
    var closeSidebar = closeSidebar2;
    mobileFilterToggle.addEventListener("click", function() {
      cellSidebar.classList.add("active");
      if (mobileOverlay) mobileOverlay.classList.add("active");
      document.body.style.overflow = "hidden";
    });
    if (mobileOverlay) {
      mobileOverlay.addEventListener("click", closeSidebar2);
    }
    document.addEventListener("keydown", function(e) {
      if (e.key === "Escape" && cellSidebar.classList.contains("active")) {
        closeSidebar2();
      }
    });
  }
  const trigger = document.querySelector(".woocommerce-orderby-trigger");
  const select = document.querySelector("select.orderby");
  const form = select == null ? void 0 : select.closest("form");
  if (trigger && select) {
    const ul = trigger.querySelector("ul");
    const lis = ul.querySelectorAll("li");
    trigger.addEventListener("click", function(e) {
      e.stopPropagation();
      trigger.classList.toggle("open");
    });
    document.addEventListener("click", function(e) {
      if (!trigger.contains(e.target)) {
        trigger.classList.remove("open");
      }
    });
    lis.forEach((li) => {
      li.addEventListener("click", function(e) {
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
function showNotification(message) {
  const existingNotifications = document.querySelectorAll(".notification");
  existingNotifications.forEach((notif) => notif.remove());
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
  }, 3e3);
}
//# sourceMappingURL=woocommerce.js.map

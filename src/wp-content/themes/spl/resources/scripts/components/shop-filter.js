/**
 * SPL Shop Filter - Custom AJAX Product Filter
 * Replaces YITH Ajax Product Filter
 */
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
    const max = parseFloat(wrapper.dataset.max) || 1000000;

    const updateSlider = () => {
      let minVal = parseFloat(minSlider.value);
      let maxVal = parseFloat(maxSlider.value);

      // Prevent overlap
      if (minVal > maxVal - 10000) {
        minVal = maxVal - 10000;
        minSlider.value = minVal;
      }

      // Update hidden inputs
      if (minHidden) minHidden.value = minVal;
      if (maxHidden) maxHidden.value = maxVal;

      // Update display
      if (minValue) minValue.textContent = this.formatPrice(minVal);
      if (maxValue) maxValue.textContent = this.formatPrice(maxVal);

      // Update range highlight
      if (sliderRange) {
        const percentMin = ((minVal - min) / (max - min)) * 100;
        const percentMax = ((maxVal - min) / (max - min)) * 100;
        sliderRange.style.left = percentMin + "%";
        sliderRange.style.width = percentMax - percentMin + "%";
      }
    };

    const handleSliderChange = () => {
      updateSlider();
      // Debounce AJAX call
      clearTimeout(this.debounceTimer);
      this.debounceTimer = setTimeout(() => {
        this.applyFilters();
      }, 500);
    };

    minSlider.addEventListener("input", updateSlider);
    maxSlider.addEventListener("input", updateSlider);
    minSlider.addEventListener("change", handleSliderChange);
    maxSlider.addEventListener("change", handleSliderChange);

    // Initialize slider position
    updateSlider();
  },

  formatPrice(value) {
    return new Intl.NumberFormat("vi-VN").format(value) + "₫";
  },

  bindEvents() {
    // Category checkboxes
    this.container
      .querySelectorAll('.filter-category input[type="checkbox"]')
      .forEach((checkbox) => {
        checkbox.addEventListener("change", (e) => {
          // Allow only single category selection
          if (e.target.checked) {
            this.container
              .querySelectorAll('.filter-category input[type="checkbox"]')
              .forEach((cb) => {
                if (cb !== e.target) cb.checked = false;
              });
          }
          this.applyFilters();
        });
      });

    // Attribute checkboxes
    this.container
      .querySelectorAll('.filter-attribute input[type="checkbox"]')
      .forEach((checkbox) => {
        checkbox.addEventListener("change", () => this.applyFilters());
      });

    // Clear filters
    const clearBtn = this.container.querySelector(".filter-clear");
    if (clearBtn) {
      clearBtn.addEventListener("click", () => this.clearFilters());
    }

    // Orderby select (WooCommerce default)
    const orderbySelect = document.querySelector(
      ".woocommerce-ordering select"
    );
    if (orderbySelect) {
      orderbySelect.addEventListener("change", () => this.applyFilters());
    }

    // Show more toggle buttons
    this.container.querySelectorAll(".show-more-btn").forEach((btn) => {
      btn.addEventListener("click", () => {
        const section = btn.closest(".filter-section");
        const hiddenItems = section.querySelectorAll(".hidden-item");
        const isExpanded = section.classList.contains("expanded");

        if (isExpanded) {
          section.classList.remove("expanded");
          hiddenItems.forEach((item) => (item.style.display = "none"));
          btn.innerHTML = `${btn.dataset.showText} <span class="more-count">(+${hiddenItems.length})</span>`;
        } else {
          section.classList.add("expanded");
          hiddenItems.forEach((item) => (item.style.display = ""));
          btn.textContent = btn.dataset.hideText;
        }
      });
    });

    // Pagination clicks
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

    // Set price sliders
    if (wrapper) {
      const minPrice = params.get("min_price");
      const maxPrice = params.get("max_price");
      const minSlider = wrapper.querySelector(".min-slider");
      const maxSlider = wrapper.querySelector(".max-slider");

      if (minPrice && minSlider) minSlider.value = minPrice;
      if (maxPrice && maxSlider) maxSlider.value = maxPrice;

      // Trigger update
      if (minSlider) minSlider.dispatchEvent(new Event("input"));
    }

    // Set attribute checkboxes
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
    const data = {
      action: "spl_shop_filter",
      nonce: window.splShopFilter?.nonce || "",
      min_price: 0,
      max_price: 0,
      category: 0,
      attributes: {},
      orderby: "menu_order",
      paged: 1,
      per_page: 12,
    };

    // Price from hidden inputs
    const minInput = this.container.querySelector(".min-price");
    const maxInput = this.container.querySelector(".max-price");
    if (minInput) data.min_price = parseFloat(minInput.value) || 0;
    if (maxInput) data.max_price = parseFloat(maxInput.value) || 0;

    // Attributes
    this.container.querySelectorAll(".filter-attribute").forEach((section) => {
      const attrName = section.dataset.attribute;
      const checked = section.querySelectorAll(
        'input[type="checkbox"]:checked'
      );
      if (checked.length > 0) {
        data.attributes[attrName] = Array.from(checked).map((cb) => cb.value);
      }
    });

    // Category from checkbox or body class
    const categoryCheckbox = this.container.querySelector(
      '.filter-category input[type="checkbox"]:checked'
    );
    if (categoryCheckbox) {
      data.category = parseInt(categoryCheckbox.value, 10);
    } else {
      // Fallback to body class for category pages
      const categoryMatch = document.body.className.match(/term-(\d+)/);
      if (categoryMatch) {
        data.category = parseInt(categoryMatch[1], 10);
      }
    }

    // Orderby
    const orderbySelect = document.querySelector(
      ".woocommerce-ordering select"
    );
    if (orderbySelect) {
      data.orderby = orderbySelect.value;
    }

    return data;
  },

  applyFilters(paged = 1) {
    if (this.isLoading) return;

    const data = this.getFilterData();
    data.paged = paged;

    this.setLoading(true);
    this.updateUrl(data);

    fetch(window.splShopFilter?.ajaxUrl || "/wp-admin/admin-ajax.php", {
      method: "POST",
      headers: {
        "Content-Type": "application/x-www-form-urlencoded",
      },
      body: this.buildFormData(data),
    })
      .then((response) => response.json())
      .then((result) => {
        if (result.success) {
          this.updateProducts(result.data.products);
          this.updatePagination(result.data.pagination);
          this.closeMobileSidebar();
          this.scrollToProducts();
        }
      })
      .catch((error) => {
        console.error("Shop Filter Error:", error);
      })
      .finally(() => {
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

    // Clear old filter params
    ["min_price", "max_price", "paged"].forEach((key) =>
      url.searchParams.delete(key)
    );
    Array.from(url.searchParams.keys())
      .filter((key) => key.startsWith("filter_"))
      .forEach((key) => url.searchParams.delete(key));

    // Set new params
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
    // Reset price slider
    const wrapper = this.container.querySelector(".price-slider-wrapper");
    if (wrapper) {
      const minSlider = wrapper.querySelector(".min-slider");
      const maxSlider = wrapper.querySelector(".max-slider");
      if (minSlider) minSlider.value = wrapper.dataset.min || 0;
      if (maxSlider) maxSlider.value = wrapper.dataset.max || 1000000;
      if (minSlider) minSlider.dispatchEvent(new Event("input"));
    }

    // Uncheck all checkboxes
    this.container.querySelectorAll('input[type="checkbox"]').forEach((cb) => {
      cb.checked = false;
    });

    // Reset orderby
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
    // Only on mobile (check viewport width)
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
  },
};

// Initialize when DOM is ready
if (document.readyState === "loading") {
  document.addEventListener("DOMContentLoaded", () => ShopFilter.init());
} else {
  ShopFilter.init();
}

export default ShopFilter;

/**
 * AIEbike Lucky Wheel - Frontend JavaScript
 *
 * Xử lý xác thực số khung và auto-generate email
 */

(function ($) {
  "use strict";

  // Configuration
  const config = window.aiebikeWheelConfig || {};
  const AJAX_URL = config.ajaxUrl || "/wp-admin/admin-ajax.php";
  const NONCE = config.nonce || "";
  const ENABLE_FRAME = config.enableFrameValidation !== "no";
  const FRAME_LABEL = config.frameFieldLabel || "Số khung hoặc số động cơ xe";
  const AUTO_EMAIL_DOMAIN = config.autoEmailDomain || "aiebike.store";
  const i18n = config.i18n || {};

  // State
  let frameNumberValidated = false;
  let validatedFrameNumber = "";

  /**
   * Inject frame number field into Lucky Wheel form
   */
  function injectFrameField() {
    if (!ENABLE_FRAME) return;

    const fieldsContainer = document.querySelector(
      ".wc-lucky-wheel-shortcode-wheel-fields-container",
    );
    if (!fieldsContainer || document.getElementById("aiebike_frame_number"))
      return;

    const frameFieldHTML = `
            <div class="wc-lucky-wheel-shortcode-wheel-field-wrap aiebike-frame-field-wrap">
                <span class="aiebike-frame-error"></span>
                <input type="text" 
                       id="aiebike_frame_number" 
                       class="wc-lucky-wheel-shortcode-wheel-field"
                       placeholder="🔑 ${FRAME_LABEL} *">
                <small class="aiebike-frame-note">
                    ⚠️ ${i18n.note || "Mỗi xe chỉ được quay 1 lần."}
                </small>
            </div>
        `;

    fieldsContainer.insertAdjacentHTML("afterbegin", frameFieldHTML);
  }

  /**
   * Validate frame number via AJAX
   */
  function validateFrameNumber(frameNumber, mobile) {
    return new Promise((resolve, reject) => {
      const formData = new FormData();
      formData.append("action", "aiebike_check_frame_number");
      formData.append("nonce", NONCE);
      formData.append("frame_number", frameNumber);
      formData.append("mobile", mobile || "");

      fetch(AJAX_URL, {
        method: "POST",
        body: formData,
      })
        .then((response) => response.json())
        .then((data) => {
          if (data.success) {
            resolve(data.data);
          } else {
            reject(data.data);
          }
        })
        .catch((error) =>
          reject({ message: "Lỗi kết nối, vui lòng thử lại." }),
        );
    });
  }

  /**
   * Save frame number after successful spin
   */
  function saveFrameNumber(
    frameNumber,
    customerName,
    customerPhone,
    customerEmail,
    prizeWon,
  ) {
    const formData = new FormData();
    formData.append("action", "aiebike_save_frame_number");
    formData.append("nonce", NONCE);
    formData.append("frame_number", frameNumber);
    formData.append("customer_name", customerName);
    formData.append("customer_phone", customerPhone);
    formData.append("customer_email", customerEmail);
    formData.append("prize_won", prizeWon);

    fetch(AJAX_URL, {
      method: "POST",
      body: formData,
    })
      .then((response) => response.json())
      .then((data) => console.log("Frame number saved:", data))
      .catch((error) => console.error("Error saving frame number:", error));
  }

  /**
   * Auto-generate email from phone number
   */
  function autoGenerateEmail(emailField, mobileField) {
    if (!emailField || !mobileField) return;

    const email = emailField.value.trim();
    const mobile = mobileField.value.trim().replace(/\s|-|\(|\)/g, "");

    if (!email && mobile && mobile.length >= 9) {
      emailField.value = mobile + "@" + AUTO_EMAIL_DOMAIN;
      console.log("Auto-generated email:", emailField.value);
    }
  }

  /**
   * Setup real-time email generation on phone input
   */
  function setupRealtimeEmailGeneration() {
    const emailField = document.querySelector(
      ".wc-lucky-wheel-shortcode-wheel-field-email",
    );
    const mobileField = document.querySelector(
      ".wc-lucky-wheel-shortcode-wheel-field-mobile",
    );

    if (!emailField || !mobileField) return;

    // Auto-generate email when user types phone number
    mobileField.addEventListener("input", function () {
      autoGenerateEmail(emailField, mobileField);
    });

    // Also generate on blur (when user leaves the field)
    mobileField.addEventListener("blur", function () {
      autoGenerateEmail(emailField, mobileField);
    });
  }

  /**
   * Intercept spin button click
   */
  function interceptSpinButton() {
    const spinButton = document.querySelector(
      ".wc-lucky-wheel-shortcode-wheel-button-wrap, .wc-lucky-wheel-shortcode-wheel-button",
    );
    const frameField = document.getElementById("aiebike_frame_number");
    const frameWrap = document.querySelector(".aiebike-frame-field-wrap");
    const errorSpan = document.querySelector(".aiebike-frame-error");
    const emailField = document.querySelector(
      ".wc-lucky-wheel-shortcode-wheel-field-email",
    );
    const mobileField = document.querySelector(
      ".wc-lucky-wheel-shortcode-wheel-field-mobile",
    );
    const nameField = document.querySelector(
      ".wc-lucky-wheel-shortcode-wheel-field-name",
    );

    if (!spinButton) return;

    // Setup real-time email generation
    setupRealtimeEmailGeneration();

    // Intercept click with capture phase to run BEFORE plugin's validation
    spinButton.addEventListener(
      "click",
      function (e) {
        // CRITICAL: Auto-generate email FIRST, before any validation
        autoGenerateEmail(emailField, mobileField);

        // Skip frame validation if disabled
        if (!ENABLE_FRAME || !frameField) return;

        const frameNumber = frameField.value
          .trim()
          .toUpperCase()
          .replace(/\s+/g, "");

        // Clear previous errors
        if (errorSpan) {
          errorSpan.textContent = "";
          errorSpan.style.color = "#dc3545";
        }
        if (frameWrap) {
          frameWrap.classList.remove("error", "validated");
        }

        // Validate frame number
        if (!frameNumber) {
          e.stopImmediatePropagation();
          e.preventDefault();
          if (errorSpan)
            errorSpan.textContent =
              "⚠️ " +
              (i18n.frameRequired ||
                "Vui lòng nhập số khung hoặc số động cơ xe!");
          if (frameWrap) frameWrap.classList.add("error");
          frameField.focus();
          return false;
        }

        if (!frameNumberValidated || validatedFrameNumber !== frameNumber) {
          e.stopImmediatePropagation();
          e.preventDefault();

          // Use async validation
          (async function () {
            try {
              if (errorSpan)
                errorSpan.textContent =
                  "⏳ " + (i18n.checking || "Đang kiểm tra...");

              // Pass mobile number for transient storage
              const mobile = mobileField ? mobileField.value.trim() : "";
              const result = await validateFrameNumber(frameNumber, mobile);
              frameNumberValidated = true;
              validatedFrameNumber = frameNumber;

              if (frameWrap) frameWrap.classList.add("validated");
              if (errorSpan) {
                errorSpan.textContent =
                  "✅ " + (i18n.valid || "Số khung hợp lệ!");
                errorSpan.style.color = "#28a745";
              }

              // Trigger click again after validation (faster response)
              setTimeout(() => {
                spinButton.click();
              }, 100);
            } catch (error) {
              frameNumberValidated = false;
              if (frameWrap) frameWrap.classList.add("error");
              if (errorSpan) errorSpan.textContent = "❌ " + error.message;
            }
          })();
          return false;
        }

        // If validated, save after spin completion
        setTimeout(() => {
          const resultObserver = new MutationObserver((mutations, obs) => {
            const resultDiv = document.querySelector(
              ".wc-lucky-wheel-shortcode--frontend-result",
            );
            if (resultDiv) {
              // Extract prize name from wheel configuration
              let prizeWon = "Unknown";

              // Try to get prize from wheel shortcode args
              const wheelContainer = document.querySelector(
                ".wc-lucky-wheel-shortcode-container",
              );

              if (wheelContainer) {
                try {
                  const shortcodeArgs =
                    jQuery(wheelContainer).data("shortcode_args");
                  if (shortcodeArgs && shortcodeArgs.label) {
                    // Get the prize label from the result text
                    // Result format usually contains the label wrapped in <strong> tags
                    const resultText = resultDiv.innerHTML;
                    const strongMatch = resultText.match(
                      /<strong>([^<]+)<\/strong>/,
                    );

                    if (strongMatch && strongMatch[1]) {
                      prizeWon = strongMatch[1].trim();
                    } else {
                      // Fallback: try to extract from plain text
                      const textContent = resultDiv.textContent.trim();
                      // Look for common patterns like "Chúc mừng! Bạn đã trúng [PRIZE]"
                      const patterns = [
                        /trúng\s+(.+?)(?:\s*!|$)/i,
                        /nhận\s+(.+?)(?:\s*!|$)/i,
                        /được\s+(.+?)(?:\s*!|$)/i,
                      ];

                      for (const pattern of patterns) {
                        const match = textContent.match(pattern);
                        if (match && match[1]) {
                          prizeWon = match[1].trim();
                          break;
                        }
                      }
                    }
                  }
                } catch (e) {
                  console.error("Error extracting prize:", e);
                }
              }

              // Fallback to result text if still unknown
              if (prizeWon === "Unknown") {
                prizeWon = resultDiv.textContent.substring(0, 100) || "Unknown";
              }

              saveFrameNumber(
                validatedFrameNumber,
                nameField ? nameField.value : "",
                mobileField ? mobileField.value : "",
                emailField ? emailField.value : "",
                prizeWon,
              );
              obs.disconnect();
            }
          });

          const container = document.querySelector(
            ".wc-lucky-wheel-shortcode-wheel-fields-container",
          );
          if (container) {
            resultObserver.observe(container, {
              childList: true,
              subtree: true,
            });
          }
        }, 100);
      },
      true,
    );
  }

  /**
   * Initialize
   */
  function init() {
    injectFrameField();
    setTimeout(interceptSpinButton, 100);
  }

  // Run when DOM ready
  $(document).ready(function () {
    setTimeout(init, 300);
  });

  // Re-init when popup opens
  $(document).on(
    "click",
    ".woocommerce-lucky-wheel-popup-icon, .wlwl_wheel_icon",
    function () {
      setTimeout(init, 1000);
    },
  );
})(jQuery);

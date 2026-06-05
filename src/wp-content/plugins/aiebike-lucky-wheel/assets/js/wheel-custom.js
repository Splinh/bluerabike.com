/**
 * AIEbike Lucky Wheel - Custom Wheel with Image Support
 *
 * Hỗ trợ vòng quay tùy chỉnh bằng hình ảnh
 * Nếu có customWheelImage từ ACF, sẽ thay thế canvas bằng ảnh
 */

(function ($) {
  "use strict";

  const CONFIG = window.aiebikeWheelConfig || {};
  const CUSTOM_WHEEL_IMAGE = CONFIG.customWheelImage || "";
  // Rotation offset to align custom image with canvas (in degrees)
  // Adjust this value if the wheel prizes don't align correctly
  const ROTATION_OFFSET = CONFIG.wheelRotationOffset || 90;

  /**
   * Replace canvas with custom wheel image
   */
  function replaceWheelWithImage() {
    if (!CUSTOM_WHEEL_IMAGE) {
      console.log(
        "AIEbike Wheel: No custom wheel image, using default canvas.",
      );
      return;
    }

    const container = document.querySelector(
      ".wc-lucky-wheel-shortcode-container",
    );
    if (!container) {
      console.log("AIEbike Wheel: Container not found.");
      return;
    }

    // Try both wrapper class names (for different versions of plugin)
    let wheelWrapper = container.querySelector(
      ".wc-lucky-wheel-shortcode-wheel-wrapper",
    );
    if (!wheelWrapper) {
      wheelWrapper = container.querySelector(
        ".wc-lucky-wheel-shortcode-wheel-container",
      );
    }
    if (!wheelWrapper) {
      console.log("AIEbike Wheel: Wheel wrapper/container not found.");
      return;
    }

    const canvas = wheelWrapper.querySelector(
      ".wc-lucky-wheel-shortcode-wheel-canvas-1",
    );
    if (!canvas) {
      console.log("AIEbike Wheel: Canvas not found.");
      return;
    }

    // Check if custom wheel image already added
    if (wheelWrapper.querySelector(".aiebike-custom-wheel-image")) {
      console.log("AIEbike Wheel: Custom image already exists.");
      return;
    }

    // Get canvas size
    let canvasSize = canvas.offsetWidth || canvas.width || 400;

    // Create custom wheel image element
    const customWheel = document.createElement("img");
    customWheel.src = CUSTOM_WHEEL_IMAGE;
    customWheel.className = "aiebike-custom-wheel-image";
    customWheel.alt = "Vòng quay may mắn";
    customWheel.style.cssText = `
      position: absolute;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      object-fit: contain;
      pointer-events: none;
      z-index: 1;
    `;

    // Hide original canvas
    canvas.style.opacity = "0";

    // Get the actual parent of canvas (may be nested inside wrapper)
    const canvasParent = canvas.parentElement;

    // Ensure parent has relative positioning
    canvasParent.style.position = "relative";

    // Insert custom image before canvas (using actual parent)
    canvasParent.insertBefore(customWheel, canvas);

    // Copy rotation from canvas to custom image
    syncRotation(canvas, customWheel);

    console.log("AIEbike Wheel: Custom wheel image applied successfully.");
  }

  /**
   * Add rotation offset to a CSS transform string
   * @param {string} transform - Original transform string (e.g. "rotate(360deg)")
   * @param {number} offset - Offset in degrees to add
   * @returns {string} Modified transform string
   */
  function addRotationOffset(transform, offset) {
    if (!transform || transform === "none") {
      return `rotate(${offset}deg)`;
    }

    // Extract rotation value from transform string
    const rotateMatch = transform.match(/rotate\(([^)]+)\)/);
    if (rotateMatch) {
      const currentRotation = parseFloat(rotateMatch[1]);
      const newRotation = currentRotation + offset;
      return transform.replace(/rotate\([^)]+\)/, `rotate(${newRotation}deg)`);
    }

    // If no rotate found, add it
    return `${transform} rotate(${offset}deg)`;
  }

  /**
   * Sync rotation between canvas and custom image
   * Only syncs when spin animation starts to avoid backwards rotation
   */
  function syncRotation(canvas, customWheel) {
    if (!canvas || !customWheel) return;

    let lastTransform = "";
    let isSpinning = false;

    // Watch for style changes using MutationObserver
    const observer = new MutationObserver(function () {
      const transform = canvas.style.transform || "";
      const transition = canvas.style.transition || "";

      // Detect spin start: when transition is set with a duration
      const hasTransition =
        transition.includes("transform") &&
        (transition.includes("s") || transition.includes("ms"));

      if (hasTransition && transform !== lastTransform) {
        // Spin is starting - copy both transition and transform with offset
        customWheel.style.transition = transition;
        // Apply offset to the rotation
        const offsetTransform = addRotationOffset(transform, ROTATION_OFFSET);
        customWheel.style.transform = offsetTransform;
        lastTransform = transform;
        isSpinning = true;
        console.log("AIEbike Wheel: Spin started, syncing animation.");
      } else if (!hasTransition && isSpinning) {
        // Spin ended - set final position without transition
        setTimeout(function () {
          const finalTransform = canvas.style.transform || "";
          customWheel.style.transition = "none";
          const offsetFinalTransform = addRotationOffset(
            finalTransform,
            ROTATION_OFFSET,
          );
          customWheel.style.transform = offsetFinalTransform;
          lastTransform = finalTransform;
          isSpinning = false;
          console.log("AIEbike Wheel: Spin ended, final position set.");
        }, 100);
      }

      // Keep canvas hidden
      canvas.style.opacity = "0";
    });

    observer.observe(canvas, {
      attributes: true,
      attributeFilter: ["style"],
    });

    // Initial sync without animation, with offset applied
    const initialTransform = canvas.style.transform || "";
    if (initialTransform) {
      customWheel.style.transition = "none";
      const offsetInitialTransform = addRotationOffset(
        initialTransform,
        ROTATION_OFFSET,
      );
      customWheel.style.transform = offsetInitialTransform;
    } else {
      // Apply offset even if no initial transform
      customWheel.style.transition = "none";
      customWheel.style.transform = `rotate(${ROTATION_OFFSET}deg)`;
      lastTransform = initialTransform;
    }
    canvas.style.opacity = "0";
  }

  /**
   * Add CSS for custom wheel
   */
  function addCustomStyles() {
    if (document.getElementById("aiebike-custom-wheel-styles")) return;

    const style = document.createElement("style");
    style.id = "aiebike-custom-wheel-styles";
    style.textContent = `
      .wc-lucky-wheel-shortcode-wheel-wrapper,
      .wc-lucky-wheel-shortcode-wheel-container {
        position: relative !important;
      }
      .aiebike-custom-wheel-image {
        will-change: transform;
      }
      /* Make canvas transparent when using custom image */
      .wc-lucky-wheel-shortcode-wheel-wrapper:has(.aiebike-custom-wheel-image) 
      .wc-lucky-wheel-shortcode-wheel-canvas-1,
      .wc-lucky-wheel-shortcode-wheel-container:has(.aiebike-custom-wheel-image) 
      .wc-lucky-wheel-shortcode-wheel-canvas-1 {
        opacity: 0 !important;
      }
    `;
    document.head.appendChild(style);
  }

  // Initialize
  $(document).ready(function () {
    addCustomStyles();

    // Wait for WooCommerce Lucky Wheel to initialize
    setTimeout(replaceWheelWithImage, 500);
    setTimeout(replaceWheelWithImage, 1500);
    setTimeout(replaceWheelWithImage, 3000);
  });

  // Re-check on window resize
  $(window).on("resize", function () {
    setTimeout(replaceWheelWithImage, 500);
  });
})(jQuery);

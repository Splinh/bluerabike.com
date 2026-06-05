/**
 * Global Seasonal Effect JavaScript
 * Creates realistic falling petals/snowflakes across the entire website
 * Inspired by thegioididong.com flower effect
 */

(function () {
  "use strict";

  const effectContainer = document.getElementById("global-seasonal-effect");
  if (!effectContainer) return;

  const effectType = effectContainer.dataset.effect || "none";
  if (effectType === "none") return;

  // Detect mobile for reduced effects
  const isMobile = window.innerWidth <= 768;

  // Preload images for smooth animation
  const flowerImages = {
    mai: null,
    dao: null,
  };

  /**
   * Initialize effect on page load
   */
  function init() {
    preloadImages().then(() => {
      createSeasonalEffect();
    });
  }

  /**
   * Preload flower sprite images
   */
  function preloadImages() {
    return new Promise((resolve) => {
      const themeUrl =
        effectContainer.dataset.themeUrl || "/wp-content/themes/spl/assets";

      // Load hoa mai
      const maiImg = new Image();
      maiImg.src = `${themeUrl}/images/seasonal/hoa-mai.png`;
      maiImg.onload = () => {
        flowerImages.mai = maiImg;
        checkAllLoaded();
      };
      maiImg.onerror = () => checkAllLoaded();

      // Load hoa dao
      const daoImg = new Image();
      daoImg.src = `${themeUrl}/images/seasonal/hoa-dao.png`;
      daoImg.onload = () => {
        flowerImages.dao = daoImg;
        checkAllLoaded();
      };
      daoImg.onerror = () => checkAllLoaded();

      let loadedCount = 0;
      function checkAllLoaded() {
        loadedCount++;
        if (loadedCount >= 2) resolve();
      }

      // Fallback if images don't load
      setTimeout(resolve, 3000);
    });
  }

  /**
   * Create Seasonal Effect based on type
   */
  function createSeasonalEffect() {
    switch (effectType) {
      case "mai":
        createRealisticPetals("mai");
        break;
      case "dao":
        createRealisticPetals("dao");
        break;
      case "snow":
        createSnowflakes();
        break;
      default:
        break;
    }
  }

  /**
   * Create Realistic Flower Petals using sprite images
   * Animation mimics thegioididong.com style - slow, gentle, realistic
   */
  function createRealisticPetals(type) {
    // Config differs by type: mai = fewer, slower; dao = original
    const isMai = type === "mai";
    const config = {
      // Hoa mai: fewer flowers (10 vs 20), hoa dao: original count
      initialCount: isMobile ? (isMai ? 4 : 8) : isMai ? 10 : 20,
      maxCount: isMobile ? (isMai ? 6 : 12) : isMai ? 15 : 30,
      // Hoa mai: spawn less frequently (2500ms vs 1200ms)
      spawnInterval: isMobile ? (isMai ? 3000 : 2000) : isMai ? 2500 : 1200,
      // Hoa mai: slower falling (25-40s vs 15-25s)
      minDuration: isMai ? 25 : 15,
      maxDuration: isMai ? 40 : 25,
      // Size range (same for both)
      minSize: 20,
      maxSize: 45,
    };

    const themeUrl =
      effectContainer.dataset.themeUrl || "/wp-content/themes/spl/assets";
    const imageSrc =
      type === "mai"
        ? `${themeUrl}/images/seasonal/hoa-mai.png`
        : `${themeUrl}/images/seasonal/hoa-dao.png`;

    // Sprite positions for individual flowers (6 flowers in grid)
    // Row 1: 3 single petals, Row 2: 2 full flowers + 1 petal
    const spritePositions = [
      { x: 0, y: 0, w: 170, h: 170 }, // Top-left petal
      { x: 170, y: 0, w: 170, h: 170 }, // Top-center petal
      { x: 340, y: 0, w: 170, h: 170 }, // Top-right petal
      { x: 0, y: 170, w: 200, h: 200 }, // Bottom-left full flower
      { x: 200, y: 170, w: 200, h: 200 }, // Bottom-center full flower
      { x: 400, y: 170, w: 110, h: 170 }, // Bottom-right petal
    ];

    /**
     * Create a single petal element
     */
    function createPetal() {
      const petal = document.createElement("div");
      petal.className = `flower-petal flower-${type}`;

      // Random sprite selection
      const sprite =
        spritePositions[Math.floor(Math.random() * spritePositions.length)];

      // Random size
      const size =
        Math.random() * (config.maxSize - config.minSize) + config.minSize;
      const scale = size / sprite.w;

      // Random horizontal position
      const startX = Math.random() * 100;

      // Random animation duration (slow like thegioididong)
      const duration =
        Math.random() * (config.maxDuration - config.minDuration) +
        config.minDuration;

      // Random sway pattern
      const swayAmount = Math.random() * 80 + 40; // 40-120px sway
      const swayDirection = Math.random() > 0.5 ? 1 : -1;

      // Random rotation
      const rotateStart = Math.random() * 360;
      const rotateAmount = (Math.random() * 360 + 180) * swayDirection;

      // Set styles
      petal.style.cssText = `
        position: absolute;
        top: -${size}px;
        left: ${startX}%;
        width: ${sprite.w * scale}px;
        height: ${sprite.h * scale}px;
        background-image: url('${imageSrc}');
        background-position: -${sprite.x * scale}px -${sprite.y * scale}px;
        background-size: ${512 * scale}px ${340 * scale}px;
        opacity: 0;
        pointer-events: none;
        will-change: transform, opacity;
        animation: 
          petalFall${Date.now() + Math.random()} ${duration}s ease-in-out forwards,
          petalSway${Date.now() + Math.random()} ${duration / 3}s ease-in-out infinite;
      `;

      // Create unique keyframes for this petal
      const styleTag = document.createElement("style");
      const fallKeyframes = `
        @keyframes petalFall${Date.now() + Math.random()} {
          0% {
            transform: translateY(0) translateX(0) rotate(${rotateStart}deg) rotateY(0deg);
            opacity: 0;
          }
          5% {
            opacity: 0.9;
          }
          25% {
            transform: translateY(25vh) translateX(${swayAmount * swayDirection}px) rotate(${rotateStart + rotateAmount * 0.25}deg) rotateY(45deg);
          }
          50% {
            transform: translateY(50vh) translateX(${-swayAmount * swayDirection * 0.5}px) rotate(${rotateStart + rotateAmount * 0.5}deg) rotateY(90deg);
          }
          75% {
            transform: translateY(75vh) translateX(${swayAmount * swayDirection * 0.7}px) rotate(${rotateStart + rotateAmount * 0.75}deg) rotateY(135deg);
          }
          95% {
            opacity: 0.7;
          }
          100% {
            transform: translateY(105vh) translateX(${-swayAmount * 0.3}px) rotate(${rotateStart + rotateAmount}deg) rotateY(180deg);
            opacity: 0;
          }
        }
      `;

      styleTag.textContent = fallKeyframes;
      document.head.appendChild(styleTag);

      effectContainer.appendChild(petal);

      // Cleanup after animation
      const cleanupTime = (duration + 1) * 1000;
      setTimeout(() => {
        petal.remove();
        styleTag.remove();
      }, cleanupTime);
    }

    // Create initial petals with staggered start
    for (let i = 0; i < config.initialCount; i++) {
      setTimeout(() => createPetal(), i * 300);
    }

    // Continuously spawn new petals
    setInterval(() => {
      if (effectContainer.children.length < config.maxCount) {
        createPetal();
      }
    }, config.spawnInterval);
  }

  /**
   * Create Snow Effect
   */
  function createSnowflakes() {
    const numberOfFlakes = isMobile ? 15 : 40;
    const maxFlakes = isMobile ? 20 : 60;
    const intervalMs = isMobile ? 800 : 400;
    const snowflakes = ["❅", "❆", "❄"];

    for (let i = 0; i < numberOfFlakes; i++) {
      const snowflake = document.createElement("div");
      snowflake.className = "snowflake";
      snowflake.textContent =
        snowflakes[Math.floor(Math.random() * snowflakes.length)];

      snowflake.style.left = Math.random() * 100 + "%";
      snowflake.style.fontSize = Math.random() * 10 + 10 + "px";
      snowflake.style.animationDuration = Math.random() * 4 + 8 + "s"; // Slower: 8-12s
      snowflake.style.animationDelay = Math.random() * 5 + "s";

      effectContainer.appendChild(snowflake);
    }

    // Continuously create new snowflakes
    setInterval(() => {
      if (effectContainer.children.length < maxFlakes) {
        const snowflake = document.createElement("div");
        snowflake.className = "snowflake";
        snowflake.textContent =
          snowflakes[Math.floor(Math.random() * snowflakes.length)];
        snowflake.style.left = Math.random() * 100 + "%";
        snowflake.style.fontSize = Math.random() * 10 + 10 + "px";
        snowflake.style.animationDuration = Math.random() * 4 + 8 + "s";
        effectContainer.appendChild(snowflake);

        // Remove after animation
        setTimeout(() => {
          snowflake.remove();
        }, 12000);
      }
    }, intervalMs);
  }

  // Initialize when DOM is ready
  if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", init);
  } else {
    init();
  }
})();

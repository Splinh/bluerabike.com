import * as path from "path";
import { viteStaticCopy } from "vite-plugin-static-copy";
import { sharedConfig } from "../../../../vite.config.shared";

// THEME
const dir = path.resolve(__dirname).replace(/\\/g, "/");
const resources = `${dir}/resources`;
const assets = `${dir}/assets`;

// COPY
const directoriesToCopy = [
  { src: `${resources}/img`, dest: "" },
  { src: `${resources}/images`, dest: "" },
];

// SASS
const sassFiles = [
  // (sections) - individual section styles for lazy loading
  "sections/section-banner",
  "sections/section-broadcast-banner",
  "sections/section-cta",
  "sections/section-product-cat",
  "sections/section-products",
  "sections/section-latest-news",
  "sections/section-map",
  "sections/section-doitac",
  "sections/section-slide-shadow",
  "sections/section-tabs",
  "sections/section-products-new",
  "sections/section-form",
  "sections/section-store-detail",

  // (components)
  "components/home",
  "components/cohoi",
  "components/landing-cohoi",
  "components/about",
  "components/swiper",
  "components/woocommerce",

  // (entries)
  "editor-style",
  "admin",
  "index",
  "popup-promo",
  "seasonal-effect",
];

// JS
const jsFiles = [
  // (components)
  "components/home",
  "components/preload-polyfill",
  "components/social-share",
  "components/swiper",
  "components/woocommerce",
  "components/store-detail",

  // (entries)
  "admin",
  "index",
  "popup-promo",
  "seasonal-effect",
];

export default {
  ...sharedConfig,
  plugins: [
    ...sharedConfig.plugins,
    viteStaticCopy({
      targets: directoriesToCopy,
    }),
  ],
  resolve: {
    alias: {
      jquery: `${resources}/scripts/jquery-global.cjs`,
    },
  },
  build: {
    ...sharedConfig.build,
    outDir: `${assets}`,
    assetsDir: "",
    rollupOptions: {
      input: Object.fromEntries([
        ...sassFiles.map((file) => [
          `css/${file}`,
          `${resources}/styles/${file}.scss`,
        ]),
        ...jsFiles.map((file) => [
          `${file}`,
          `${resources}/scripts/${file}.js`,
        ]),
      ]),
      output: {
        entryFileNames: `js/[name].js`,
        chunkFileNames: `js/[name].js`,
        manualChunks(id) {
          // CSS from styles/3rd into vendor
          if (id.includes("styles/3rd")) {
            return "_vendor";
          }
          // Split Foundation into separate chunk for better caching
          if (id.includes("foundation-sites")) {
            return "vendor-foundation";
          }
          // Split Swiper core into separate chunk
          if (id.includes("node_modules/swiper")) {
            return "vendor-swiper";
          }
        },
        assetFileNames: (assetInfo) => {
          const name = assetInfo.name || "";

          if (name.endsWith(".css")) {
            const cssMap = {
              _vendor: "css/_vendor.css",
              index: "css/index.css",
            };

            const matched = Object.keys(cssMap).find((key) =>
              name.includes(key),
            );
            if (matched) return cssMap[matched];

            return `[name].css`;
          }

          if (/\.(woff2?|ttf|otf|eot)$/i.test(name)) {
            return `fonts/[name].[ext]`;
          }

          return `img/[name].[ext]`;
        },
      },
    },
  },
};

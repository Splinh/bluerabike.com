// 3rd/fx.js

import Events from './fx/utils/events.js';

/**
 * Lazy import configuration
 * Maps module keys to: selector (for detection) and loader function
 */
const moduleConfig = {
    smoothScroll: {
        selector: '[data-fx-scroll]',
        loader: () => import('./fx/smoothscroll/fx-smoothscroll.js')
    },
    tabs: {
        selector: '[data-fx-tabs]',
        loader: () => import('./fx/tabs/fx-tabs.js')
    },
    offCanvas: {
        selector: '[data-fx-off-canvas], [data-open], [data-close]',
        loader: () => import('./fx/offcanvas/fx-offcanvas.js')
    },
    dropdown: {
        selector: '[data-fx-dropdown], [data-fx-dropdown-toggle]',
        loader: () => import('./fx/dropdown/fx-dropdown.js')
    },
    dropdownMenu: {
        selector: '[data-fx-dropdown-menu]',
        loader: () => import('./fx/dropdown/fx-dropdown-menu.js')
    },
    accordion: {
        selector: '[data-fx-accordion]',
        loader: () => import('./fx/accordion/fx-accordion.js')
    },
    accordionMenu: {
        selector: '[data-fx-accordion-menu]',
        loader: () => import('./fx/accordion/fx-accordion-menu.js')
    },
};

// Cache loaded modules
const loadedModules = new Map();

/**
 * Load a module lazily
 *
 * @param {string} key - Module key
 * @returns {Promise<object|null>}
 */
const loadModule = async (key) => {
    if (loadedModules.has(key)) {
        return loadedModules.get(key);
    }

    const config = moduleConfig[key];
    if (!config) return null;

    try {
        const module = await config.loader();
        const m = module.default;
        loadedModules.set(key, m);
        return m;
    } catch (e) {
        console.error(`[FX] Failed to load module: ${key}`, e);
        return null;
    }
};

/**
 * Check if module is needed in DOM
 *
 * @param {string} key - Module key
 * @param {Document|Element} root - Root element to search
 * @returns {boolean}
 */
const isModuleNeeded = (key, root = document) => {
    const config = moduleConfig[key];
    return config ? root.querySelector(config.selector) !== null : false;
};

const FX = {
    /**
     * Initialize all needed modules (lazy loading)
     * Only loads modules that have matching elements in DOM
     */
    async init({root = document} = {}) {
        const promises = Object.keys(moduleConfig)
            .filter(key => isModuleNeeded(key, root))
            .map(async key => {
                const m = await loadModule(key);
                m?.initAll?.(root);
            });

        await Promise.all(promises);
    },

    /**
     * Destroy specific module
     */
    destroy: new Proxy({}, {
        get(_, key) {
            return async (root = document) => {
                const m = loadedModules.get(key);
                m?.destroyAll?.(root);
            };
        }
    }),

    /**
     * Reinitialize specific module
     */
    reinit: new Proxy({}, {
        get(_, key) {
            return async (root = document) => {
                let m = loadedModules.get(key);

                // Load if not yet loaded
                if (!m) {
                    m = await loadModule(key);
                }

                if (m) {
                    m.destroyAll?.(root);
                    m.initAll?.(root);
                }
            };
        }
    }),

    /**
     * Force load a specific module (even if not in DOM)
     */
    async load(key) {
        return await loadModule(key);
    },

    /**
     * Check which modules are loaded
     */
    get loaded() {
        return [...loadedModules.keys()];
    },

    // Event system
    on: Events.on.bind(Events),
    off: Events.off.bind(Events),
    emit: Events.emit.bind(Events),
};

export default FX;

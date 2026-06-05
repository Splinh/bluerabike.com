// dropdown/fx-dropdown-menu.js

import {$$, on, off} from "../utils/dom.js";
import Events from "../utils/events.js";
import {createWeakStore} from "../utils/weak.js";

const ROOT = "[data-fx-dropdown-menu]";
const OPEN = "is-active";

const HOVER_OPEN_DELAY = 80;
const HOVER_CLOSE_DELAY = 120;

const FxDropdownMenu = {
    _hoverHandlers: createWeakStore(),
    _clickHandlers: createWeakStore(),
    _observers: createWeakStore(),
    _resizeHandlers: createWeakStore(),

    initAll(root = document) {
        $$(ROOT, root).forEach(menu => {
            this.initMenu(menu);
            if (menu.dataset.autohide === "true") this.initAutoHide(menu);
        });
    },

    initMenu(menu) {
        const useHover = menu.dataset.hover === "true";
        menu.setAttribute("role", "menubar");

        menu.querySelectorAll("li").forEach(li => {
            const sub = li.querySelector(":scope > ul");
            const btn = li.querySelector(":scope > a, :scope > button");

            // Leaf
            if (!sub || !btn) {
                li.setAttribute("role", "none");
                btn?.setAttribute("role", "menuitem");
                return;
            }

            // Classes
            li.classList.add("is-dropdown-submenu-parent", "is-dropdown-submenu-item");
            sub.classList.add("is-dropdown-submenu");
            if (!li.closest(".is-dropdown-submenu")) sub.classList.add("first-sub");

            // ARIA
            li.setAttribute("role", "none");
            btn.setAttribute("role", "menuitem");
            btn.setAttribute("aria-haspopup", "true");
            btn.setAttribute("aria-expanded", "false");
            sub.setAttribute("role", "menu");

            // Init auto position (first run)
            this.applyAutoPosition(li, sub);

            // HOVER
            if (useHover) {
                let openTimer = null;
                let closeTimer = null;

                const enter = () => {
                    clearTimeout(closeTimer);
                    openTimer = setTimeout(() => this.open(li, btn, sub), HOVER_OPEN_DELAY);
                };

                const leave = () => {
                    clearTimeout(openTimer);
                    closeTimer = setTimeout(() => this.close(li, btn, sub), HOVER_CLOSE_DELAY);
                };

                this._hoverHandlers.set(li, {enter, leave});
                on(li, "mouseenter", enter);
                on(li, "mouseleave", leave);

                return;
            }

            // CLICK
            const handler = e => {
                e.preventDefault();
                li.classList.contains(OPEN) ? this.close(li, btn, sub) : this.open(li, btn, sub);
                const isOpen = li.classList.contains(OPEN);
                btn.setAttribute("aria-expanded", isOpen);

                Events.emit("fx:dropdownmenu:toggle", {li, sub, isOpen});
            };

            this._clickHandlers.set(btn, handler);
            on(btn, "click", handler);
        });
    },

    open(li, btn, sub) {
        li.classList.add(OPEN);
        sub.style.visibility = "hidden";
        sub.style.display = "block";

        requestAnimationFrame(() => {
            this.applyAutoPosition(li, sub);

            sub.style.visibility = "";
            sub.style.display = "";

            btn.setAttribute("aria-expanded", "true");
            sub.setAttribute("aria-hidden", "false");

            Events.emit("fx:dropdownmenu:open", {li, sub});
        });
    },

    close(li, btn, sub) {
        li.classList.remove(OPEN, "opens-left", "opens-right");
        btn.setAttribute("aria-expanded", "false");
        sub.setAttribute("aria-hidden", "true");

        Events.emit("fx:dropdownmenu:close", {li, sub});
    },

    applyAutoPosition(li, sub) {
        li.classList.remove("opens-left", "opens-right");
        const rect = sub.getBoundingClientRect();
        rect.right > window.innerWidth ? li.classList.add("opens-left") : li.classList.add("opens-right");
    },

    initAutoHide(menu) {
        const container = menu.parentElement;
        if (!container) return;

        let more = menu.querySelector(".fx-more");
        if (!more) {
            more = document.createElement("li");
            more.classList.add("fx-more", "has-dropdown");
            more.innerHTML = `
                <a href="#" role="menuitem" aria-haspopup="true" aria-expanded="false">More</a>
                <ul class="submenu vertical menu is-dropdown-submenu"></ul>
            `;
            menu.appendChild(more);
        }

        const dropdown = more.querySelector(".submenu.menu");

        const adjustMenu = () => {
            dropdown.innerHTML = "";
            more.style.display = "none";

            const items = [...menu.children].filter(li => li !== more);
            items.forEach(li => (li.style.display = "block"));
            container.style.overflow = "hidden";

            if (menu.scrollWidth <= container.clientWidth) {
                container.style.overflow = "visible";
                this.reinitMenu(menu);
                return;
            }

            const hidden = [];
            for (let i = items.length - 1; i >= 0; i--) {
                if (menu.scrollWidth > container.clientWidth) {
                    hidden.unshift(items[i]);
                    items[i].style.display = "none";
                } else break;
            }

            if (hidden.length) {
                hidden.forEach(li => {
                    const clone = li.cloneNode(true);
                    clone.style.display = "block";
                    dropdown.appendChild(clone);
                });
                more.style.display = "block";
            }

            container.style.overflow = "visible";
            this.reinitMenu(menu);
        };

        adjustMenu();

        document.fonts?.ready?.then(adjustMenu);

        if (window.ResizeObserver) {
            const ro = new ResizeObserver(adjustMenu);
            ro.observe(container);
            this._observers.set(menu, ro);
        }

        // resize optimizations
        let rsz;
        const onResize = () => {
            clearTimeout(rsz);
            rsz = setTimeout(adjustMenu, 200);
        };

        this._resizeHandlers.set(menu, onResize);
        window.addEventListener("resize", onResize);
    },

    reinitMenu(menu) {
        this.destroyAll(menu);
        this.initMenu(menu);
    },

    destroyAll(root = document) {
        $$(ROOT, root).forEach(menu => {

            this._observers.cleanup(menu, ro => ro.disconnect());
            this._resizeHandlers.cleanup(menu, h => window.removeEventListener("resize", h));

            menu.querySelectorAll("li").forEach(li => {
                const hover_handler = this._hoverHandlers.get(li);
                if (hover_handler) {
                    off(li, "mouseenter", hover_handler.enter);
                    off(li, "mouseleave", hover_handler.leave);
                    this._hoverHandlers.delete(li);
                }

                const btn = li.querySelector(":scope > a, :scope > button");
                const click_handler = this._clickHandlers.get(btn);
                if (click_handler) {
                    off(btn, "click", click_handler);
                    this._clickHandlers.delete(btn);
                }
            });
        });
    }
};

export default FxDropdownMenu;

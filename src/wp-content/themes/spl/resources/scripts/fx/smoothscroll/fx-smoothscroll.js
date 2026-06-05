// smoothscroll/fx-smoothscroll.js

import {$$, on, off} from "../utils/dom.js";
import Events from "../utils/events.js";
import {createWeakStore} from '../utils/weak.js';

const SELECTOR = "[data-fx-scroll]";
const handlers = createWeakStore();

const FxSmoothScroll = {
    activeAnimation: null,

    smoothScrollTo(targetY, {offset = 0, onStart, onUpdate, onEnd} = {}) {
        if (this.activeAnimation) {
            cancelAnimationFrame(this.activeAnimation);
            this.activeAnimation = null;
        }

        const startY = window.scrollY;
        let currentY = startY;
        let velocity = 0;

        const maxSpeed = 50;
        const minSpeed = 0.4;
        const decelFactor = 0.12;
        const nearFactor = 0.18;
        const NEAR_DISTANCE = 30;

        const finalTarget = targetY - offset;
        onStart?.({startY, targetY: finalTarget});

        const animate = () => {
            const dist = finalTarget - currentY;
            const abs = Math.abs(dist);

            if (abs < 0.8) {
                window.scrollTo(0, finalTarget);
                this.activeAnimation = null;
                onEnd?.({finalY: finalTarget});
                return;
            }

            velocity = dist * decelFactor;
            if (abs < NEAR_DISTANCE) velocity *= nearFactor;

            velocity = Math.max(-maxSpeed, Math.min(maxSpeed, velocity));

            if (velocity > 0 && velocity < minSpeed) velocity = minSpeed;
            if (velocity < 0 && velocity > -minSpeed) velocity = -minSpeed;

            currentY += velocity;
            window.scrollTo(0, currentY);

            onUpdate?.({y: currentY, velocity, dist});
            this.activeAnimation = requestAnimationFrame(animate);
        };

        this.activeAnimation = requestAnimationFrame(animate);
    },

    initAll(root = document) {
        $$(SELECTOR, root).forEach(a => {
            const handler = e => {
                const href = a.getAttribute("href");
                if (!href?.startsWith("#")) return;

                e.preventDefault();

                const target = document.getElementById(href.slice(1));
                if (!target) return;

                const offset = parseInt(
                    a.dataset.fxOffset ??
                    a.closest("[data-fx-offset]")?.dataset.fxOffset ??
                    document.body.dataset.fxOffset ??
                    0,
                    10
                );

                const targetY = target.getBoundingClientRect().top + window.scrollY;

                FxSmoothScroll.smoothScrollTo(targetY, {
                    offset,
                    onStart: () => Events.emit("fx:smoothscroll:start", {link: a, target}),
                    onEnd: () => {
                        target.setAttribute("tabindex", "-1");
                        target.focus({preventScroll: true});
                        Events.emit("fx:smoothscroll:goto", {link: a, target});
                    }
                });
            };

            handlers.set(a, handler);
            on(a, "click", handler);
        });
    },

    destroyAll(root = document) {
        if (this.activeAnimation) {
            cancelAnimationFrame(this.activeAnimation);
            this.activeAnimation = null;
        }

        $$(SELECTOR, root).forEach(a => {
            const handler = handlers.get(a);
            if (handler) {
                off(a, "click", handler);
                handlers.delete(a);
            }
        });
    },
};

export default FxSmoothScroll;

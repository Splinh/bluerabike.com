// offcanvas/swipe.js

import {on, off} from '../utils/dom.js';
import {createWeakStore} from '../utils/weak.js';

const THRESHOLD = 80;
const swipes = createWeakStore();

export const bindSwipe = (panel, overlay, onClose) => {
    if (swipes.has(panel)) return;

    let startX = 0;
    let currX = 0;
    let dragging = false;
    const right = panel.classList.contains('position-right');

    const tStart = e => {
        if (!panel.classList.contains('is-open')) return;
        dragging = true;
        startX = e.touches[0].clientX;
        panel.style.transition = 'none';
    };

    const tMove = e => {
        if (!dragging) return;
        currX = e.touches[0].clientX;
        const dx = currX - startX;

        if ((right && dx < 0) || (!right && dx > 0)) {
            const w = panel.offsetWidth || 320;
            const d = Math.min(Math.abs(dx), w);
            panel.style.transform = `translate(${right ? d : -d}px)`;
            overlay.style.opacity = 1 - d / w;
        }
    };

    const tEnd = () => {
        if (!dragging) return;
        dragging = false;

        panel.style.transition = '';
        panel.style.transform = '';
        overlay.style.opacity = '';

        if (Math.abs(currX - startX) > THRESHOLD) {
            onClose();
        }
    };

    swipes.set(panel, {tStart, tMove, tEnd});

    on(panel, 'touchstart', tStart, {passive: true});
    on(document, 'touchmove', tMove, {passive: true});
    on(document, 'touchend', tEnd);
};

export const unbindSwipe = panel => {
    const h = swipes.get(panel);
    if (!h) return;

    off(panel, 'touchstart', h.tStart);
    off(document, 'touchmove', h.tMove);
    off(document, 'touchend', h.tEnd);

    swipes.delete(panel);
};

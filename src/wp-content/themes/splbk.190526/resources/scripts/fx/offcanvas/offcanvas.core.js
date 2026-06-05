// offcanvas/offcanvas.core.js

import Events from '../utils/events.js';
import {trigger} from '../utils/dom.js';
import {lockScroll, unlockScroll} from './overlay.js';
import {bindSwipe, unbindSwipe} from './swipe.js';

export const isOpen = panel => panel.classList.contains('is-open');

export const openOffCanvas = (panel, overlay) => {
    if (isOpen(panel)) return;

    panel.classList.add('is-open');
    panel.classList.remove('is-closed');
    overlay.classList.add('is-visible', 'is-closable');

    if (panel.dataset.contentScroll === 'false') lockScroll();
    bindSwipe(panel, overlay, () => closeOffCanvas(panel, overlay));

    Events.emit('fx:offcanvas:open', {el: panel});
    trigger(panel, 'fx.offcanvas.opened', {el: panel});
};

export const closeOffCanvas = (panel, overlay) => {
    if (!isOpen(panel)) return;

    panel.classList.remove('is-open');
    panel.classList.add('is-closed');
    overlay.classList.remove('is-visible');

    unlockScroll();
    unbindSwipe(panel);

    Events.emit('fx:offcanvas:close', {el: panel});
    trigger(panel, 'fx.offcanvas.closed', {el: panel});
};

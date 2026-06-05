// utils/dom.js

/* --------------------------------------------------
 * Query helpers
 * -------------------------------------------------- */
export const $ = (selector, root = document) => root.querySelector(selector);
export const $$ = (selector, root = document) => Array.from(root.querySelectorAll(selector));

/* --------------------------------------------------
 * Event helpers
 * -------------------------------------------------- */
const splitEvents = ev => ev.split(' ').filter(Boolean);
const isCollection = el => NodeList.prototype.isPrototypeOf(el) || Array.isArray(el);

export const on = (el, ev, handler, opts) => {
    if (!el) return;
    const events = splitEvents(ev);
    const bind = target => events.forEach(e => target.addEventListener(e, handler, opts));
    isCollection(el) ? el.forEach(bind) : bind(el);
};
export const off = (el, ev, handler, opts) => {
    if (!el) return;
    const events = splitEvents(ev);
    const unbind = target => events.forEach(e => target.removeEventListener(e, handler, opts));
    isCollection(el) ? el.forEach(unbind) : unbind(el);
};
export const delegate = (root, selector, ev, handler, opts) => {
    const wrapper = e => {
        const target = e.target.closest(selector);
        if (target && root.contains(target)) {
            handler.call(target, e, target);
        }
    };
    on(root, ev, wrapper, opts);
    return wrapper;
};

/* --------------------------------------------------
 * DOM helpers
 * -------------------------------------------------- */
export const closest = (el, selector) => el ? el.closest(selector) : null;
export const append = (parent, child) => parent && child && parent.appendChild(child);
export const isVisible = el => !!(el && (el.offsetWidth || el.offsetHeight || el.getClientRects().length));
export const ready = fn => document.readyState !== 'loading' ? fn() : document.addEventListener('DOMContentLoaded', fn);

/* --------------------------------------------------
 * Class helpers
 * -------------------------------------------------- */
const splitClasses = cls => cls.split(' ').filter(Boolean);

export const hasClass = (el, cls) => !!(el && el.classList.contains(cls));
export const addClass = (el, cls) => el && el.classList.add(...splitClasses(cls));
export const removeClass = (el, cls) => el && el.classList.remove(...splitClasses(cls));
export const toggleClass = (el, cls, force) => el && splitClasses(cls).forEach(c => el.classList.toggle(c, force));

/* --------------------------------------------------
 * Style & data
 * -------------------------------------------------- */
export const css = (el, styles = {}) => el && Object.assign(el.style, styles);
export const data = (el, key, val) => {
    if (!el) return null;
    if (val === undefined) return el.dataset[key];
    el.dataset[key] = val;
};

/* --------------------------------------------------
 * Element creation & events
 * -------------------------------------------------- */
export const trigger = (el, name, detail = {}) => el && el.dispatchEvent(new CustomEvent(name, {detail}));
export const create = (tag, attrs = {}) => {
    const el = document.createElement(tag);
    Object.entries(attrs).forEach(([k, v]) => {
        if (k === 'class') el.className = v;
        else if (k === 'html') el.innerHTML = v;
        else if (k === 'text') el.textContent = v;
        else el.setAttribute(k, v);
    });

    return el;
};

/* --------------------------------------------------
 * Utils
 * -------------------------------------------------- */
export const uid = (prefix = 'fx') => `${prefix}-${Math.random().toString(36).slice(2, 8)}`;

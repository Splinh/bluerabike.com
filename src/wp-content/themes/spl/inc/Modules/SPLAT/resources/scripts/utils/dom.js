export const $ = (selector, root = document) => root.querySelector(selector);

export const $$ = (selector, root = document) => Array.from(root.querySelectorAll(selector));

export const on = (el, ev, handler) => {
	const targets = typeof el === 'string' ? $$(el) : el instanceof NodeList || Array.isArray(el) ? el : [el];
	targets.forEach((target) => {
		if (!target) return;
		ev.split(' ')
			.filter(Boolean)
			.forEach((eventName) => target.addEventListener(eventName, handler));
	});
};


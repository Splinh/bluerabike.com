// utils/weak.js

/**
 * WeakMap wrapper for JS projects with strict tooling.
 *
 * @returns {*|boolean|undefined|{has(*): *, get(*): any|undefined, set(*, *): void, delete(*): void}}
 */
export const createWeakStore = () => {
    const map = new WeakMap();

    const isObject = v => v !== null && typeof v === 'object';

    return {
        has(key) {
            return isObject(key) && map.has(key);
        },
        get(key) {
            return isObject(key) ? map.get(key) : undefined;
        },
        set(key, value) {
            if (isObject(key)) map.set(key, value);
        },
        delete(key) {
            if (isObject(key)) map.delete(key);
        },
        cleanup(key, fn) {
            if (!isObject(key)) return;
            const value = map.get(key);
            if (value) {
                fn(value);
                map.delete(key);
            }
        }
    };
};

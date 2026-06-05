// utils/events.js

class EventBus {
    #listeners = new Map();

    on(event, cb) {
        if (!this.#listeners.has(event)) {
            this.#listeners.set(event, new Set());
        }
        this.#listeners.get(event).add(cb);
        return cb;
    }

    once(event, cb) {
        const wrapper = payload => {
            cb(payload);
            this.off(event, wrapper);
        };
        this.on(event, wrapper);
    }

    off(event, cb) {
        if (!this.#listeners.has(event)) return;
        if (!cb) {
            this.#listeners.delete(event);
            return;
        }

        const set = this.#listeners.get(event);
        set.delete(cb);
        if (set.size === 0) {
            this.#listeners.delete(event);
        }
    }

    emit(event, payload = {}) {
        if (!this.#listeners.has(event)) return;
        this.#listeners.get(event).forEach(cb => {
            try {
                cb(payload);
            } catch (e) {
                console.error(`[EventBus:${event}]`, e);
            }
        });
    }

    clear() {
        this.#listeners.clear();
    }
}

export default new EventBus();

// useVueStateMachine.ts
import { defineComponent, ref } from 'vue';

type TransitionTarget = string | ((args: { from: string; event: string; payload?: unknown }) => string | void);

export interface StateMachineConfig {
    initial: string;
    states?: Record<string, unknown>;
    transitions: Record<string, Record<string, TransitionTarget>>;
    events?: string[];
}

export function useVueStateMachine(config: StateMachineConfig) {
    const current = ref<string>(config.initial);

    // Transition history
    const history: string[] = [config.initial];
    let future: string[] = [];

    const go = (next: string) => {
        if (!next || next === current.value) return;
        current.value = next;
        history.push(next);
        future = [];
    };

    const transition = (event: string, payload?: unknown) => {
        const from = current.value;
        const table = config.transitions[from] || {};
        const t = table[event];

        if (!t) return;

        const target = typeof t === 'function' ? t({ from, event, payload }) : t;

        if (typeof target === 'string') {
            go(target);
        }
    };

    const back = () => {
        if (history.length <= 1) return;
        const now = history.pop()!; // remove latest
        future.unshift(now);
        current.value = history[history.length - 1];
    };

    const next = () => {
        if (future.length === 0) return;
        const n = future.shift()!;
        history.push(n);
        current.value = n;
    };

    // fire: build the object based on given events
    const known = new Set(config.events ?? []);
    const fireBase: Record<string, (payload?: unknown) => void> = {};
    known.forEach((e) => (fireBase[e] = (p?: unknown) => transition(e, p)));

    const fire = new Proxy(fireBase, {
        get(target, prop) {
            const key = String(prop);
            if (!(key in target)) {
                // on-the-fly build event method
                target[key] = (p?: unknown) => transition(key, p);
            }
            return target[key];
        },
    }) as Record<string, (payload?: unknown) => void>;

    /**
     * StateMachine: Wrapper component
     *  Slot-props { current, fire, back, next }
     *  Use the slot methods to navigate
     */
    const StateMachine = defineComponent({
        name: 'StateMachineWrapper',
        setup(_, { slots }) {
            return () =>
                slots.default?.({
                    current: current.value,
                    fire,
                    back,
                    next,
                });
        },
    });

    return { current, back, next, fire, StateMachine };
}

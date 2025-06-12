import { Step, StepResponse } from '@/types';
import { useStorage } from '@vueuse/core';
import { computed, ref } from 'vue';

// the list of all steps, set in initialize()
const steps = ref<Step[]>([]);

// your “current” step — starts null and is set once you call initialize()
const current = ref<Step | null>(null);

export function useStepNavigation(survey_run_id: string) {
    // persist responses
    const storage = useStorage<StepResponse[]>(`survey_run_${survey_run_id}_responses`, [], localStorage);

    // -- helper functions --
    function getLastResponse(): StepResponse | undefined {
        if (storage.value.length === 0) return undefined;
        // return the response with the highest self_order
        return storage.value.reduce((max, cur) => (cur.self_order > max.self_order ? cur : max));
    }

    function getDefaultStep(): Step {
        if (steps.value.length === 0) {
            throw new Error('useStepNavigation: no steps available — did you forget to call initialize()?');
        }

        const last = getLastResponse();
        if (last) {
            const next = steps.value.find((s) => s.id === last.next_step_id);
            if (next) {
                return next;
            }
        }

        // fallback to the very first step
        return steps.value[0];
    }

    // -- public API --

    /**
     * Must be called before using `current` or `progress`.
     * Pass in the array of steps for this run.
     */
    function initialize(_steps: Step[]) {
        steps.value = _steps;
        // now that we have steps, pick the default
        current.value = getDefaultStep();
    }

    /**
     * Switch to a specific step (e.g. after clicking “next”).
     */
    function setCurrent(step: Step) {
        current.value = step;
    }

    function getNextStepId(step: Step): number | null {
        const next = steps.value.find((s) => s.id === step.id + 1);

        return next ? next.id : null;
    }

    function getNext(): Step {

    }

    function getPrevious(): Step {

    }


    function handleStepSubmit(step_response: StepResponse) {
        // push the response or replace the existing one
        const existingIndex = storage.value.findIndex((r) => r.self_step_id === step_response.self_step_id);
        if (existingIndex >= 0) {
            storage.value[existingIndex] = step_response;
        } else {
            storage.value.push(step_response);
        }

        setCurrent(steps.value.find((s) => s.id === step_response.next_step_id) || getDefaultStep());
    }

    function goToPreviousStep(step: Step) {
        const current = steps.value.find((s) => s.id === step.id);

        return steps.value.find((s) => s.order === current.order - 1) || getDefaultStep();
    }

    /**
     * 0–100 progress through the steps array.
     */
    const progress = computed(() => {
        if (!current.value || steps.value.length === 0) {
            return 0;
        }
        const idx = steps.value.findIndex((s) => s.id === current.value!.id);
        return idx >= 0 ? (idx / steps.value.length) * 100 : 0;
    });

    return {
        initialize,
        current,
        progress,
        handleStepSubmit,
        getNextStepId,
        goToPreviousStep,
        setCurrent,
    };
}

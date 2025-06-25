import { Step, StepResponse } from '@/types';
import { useStorage } from '@vueuse/core';
import { computed } from 'vue';

/**
 * Composable for navigating survey steps with persistence in localStorage.
 * Ensures `steps` and `currentStep` are always initialized and never null.
 */
export function useStepNavigation(surveyRunId: string) {
    /* ------------------------------------------------------------------
     * Internal utilities
     * ------------------------------------------------------------------ */

    /**
     * Make sure every step has a numeric `order` property and sort them.
     * If no valid `order` is provided, fall back to the array index.
     */
    function normaliseSteps(raw: Step[]): Step[] {
        return raw
            .map((step, idx) => ({
                ...step,
                order:
                    typeof (step as any).order === 'number'
                        ? (step as any).order
                        : Number((step as any).order) || idx,
            }))
            .sort((a, b) => (a as any).order - (b as any).order);
    }

    /* ------------------------------------------------------------------
     * Persistent state (localStorage‑backed)
     * ------------------------------------------------------------------ */

    const stepsStorage = useStorage<Step[]>(
        `survey_${surveyRunId}_steps`,
        [],
        localStorage,
    );

    const currentStepIdStorage = useStorage<number | null>(
        `survey_${surveyRunId}_current_step`,
        null,
        localStorage,
    );

    const responsesStorage = useStorage<StepResponse[]>(
        `survey_${surveyRunId}_responses`,
        [],
        localStorage,
    );

    /* ------------------------------------------------------------------
     * Helpers
     * ------------------------------------------------------------------ */

    /** Retrieve the last saved response (highest self_order). */
    function getLastResponse(): StepResponse | undefined {
        return responsesStorage.value.reduce<StepResponse | undefined>(
            (prev, cur) => {
                if (!prev || cur.order > prev.order) return cur;
                return prev;
            },
            undefined,
        );
    }

    /** Determine the default step: either next after last response, the step with order 0, or the one with lowest order. */
    function getDefaultStep(): Step {
        if (stepsStorage.value.length === 0) {
            throw new Error(
                `No steps available for survey ${surveyRunId}. Did you forget to call setSurvey()?`,
            );
        }

        const last = getLastResponse();
        if (last) {
            const next = stepsStorage.value.find(
                (s) => s.id === last.next_step_id,
            );
            if (next) return next;
        }

        // Look for a step with order 0
        const zeroOrderStep = stepsStorage.value.find((s) => s.order === 0);
        if (zeroOrderStep) return zeroOrderStep;

        // If no step with order 0, get the one with the lowest order
        return stepsStorage.value.sort((a, b) => a.order - b.order)[0];
    }

    /** Get the current Step object; throws if not set. */
    function getCurrent(): Step {
        const id = currentStepIdStorage.value;
        if (id === null) {
            throw new Error(
                'Current step is not set. Did you forget to call setSurvey()?',
            );
        }
        console.log(id);
        console.log(stepsStorage.value);
        const step = stepsStorage.value.find((s) => parseInt(s.id) === parseInt(id));
        if (!step) {
            throw new Error(`Current step ID ${id} is invalid`);
        }
        return step;
    }

    /* ------------------------------------------------------------------
     * Public API
     * ------------------------------------------------------------------ */

    /** Initialize survey steps and set the current step. */
    function setSurvey(surveySteps: Step[]) {
        if (!surveySteps || surveySteps.length === 0) {
            throw new Error('setSurvey requires a non‑empty array of steps');
        }
        stepsStorage.value = normaliseSteps(surveySteps);

        // Keep an already‑saved current step if it exists
        if (
            currentStepIdStorage.value === null ||
            !stepsStorage.value.some((s) => s.id === currentStepIdStorage.value)
        ) {
            const defaultStep = getDefaultStep();
            currentStepIdStorage.value = defaultStep.id;
        }
    }

    /** Reactive current step computed property. */
    const currentStep = computed(() => {
        try {
            return getCurrent();
        } catch {
            return null;
        }
    });

    /** -----------------------------------------------------------------
     *  Progress (0‑100)
     *  -----------------------------------------------------------------
     *  Uses the step’s *position* in the sorted list instead of counting
     *  “completed” orders, so duplicate `order` values can’t inflate the
     *  percentage or make it overshoot 100%.
     * ------------------------------------------------------------------ */
    const progress = computed(() => {
        const steps = stepsStorage.value;
        const currentId = getCurrent()?.id;

        // Nothing to report yet
        if (!steps.length || currentId === null) return 0;

        // Work with a consistently ordered array
        const ordered = [...steps].sort((a, b) => a.order - b.order);
        const idx = ordered.findIndex((s) => s.id === currentId);

        console.log(ordered);

        if (idx === -1) {
            console.warn(
                `⚠️ Progress: step ID ${currentId} not found — returning 0 %`,
            );
            return 0;
        }

        const pct = ((idx + 1) / ordered.length) * 100;
        return Number(pct.toFixed(1)); // 1‑dp precision
    });

    /** Submit a response and advance the current step to the next step in order. */
    function handleStepSubmit(response: StepResponse) {
        const idx = responsesStorage.value.findIndex(
            (r) => r.self_step_id === response.self_step_id,
        );
        if (idx >= 0) {
            responsesStorage.value[idx] = response;
        } else {
            responsesStorage.value.push(response);
        }

        const current = getCurrent();
        const nextId = getNextStepId();

        console.log('🔄 Step navigation debug:', {
            currentStep: current.id,
            currentOrder: (current as any).order,
            nextStepFound: nextId ?? 'none',
            totalSteps: stepsStorage.value.length,
        });

        if (nextId !== null) {
            currentStepIdStorage.value = nextId;
            const step = stepsStorage.value.find((s) => s.id === nextId)!;
            console.log(
                '✅ Advanced to next step:',
                step.id,
                (step as any).title,
            );
        } else {
            console.log('🏁 Reached end of survey - staying on current step');
        }
    }

    /**
     * Move to a specific step by object or ID.
     */
    function goToStep(stepOrId: Step | number) {
        const id = typeof stepOrId === 'number' ? stepOrId : stepOrId.id;

        // Verify step exists before setting it as current
        const targetStep = stepsStorage.value.find((s) => s.id === id);
        if (!targetStep) {
            console.error(
                `❌ Cannot navigate: Step ID ${id} not found in steps array`,
            );
            throw new Error(`Step ID ${id} not found`);
        }

        // Update the current step ID
        console.log(`🔄 Navigating to step ID ${id} (${targetStep.title})`);
        currentStepIdStorage.value = id;

        // Verify the update was successful
        if (currentStepIdStorage.value !== id) {
            console.error(
                `❌ Failed to update current step ID: expected ${id}, got ${currentStepIdStorage.value}`,
            );
        }
    }

    /**
     * Move to the previous step (by `order`). If already at the first
     * step, throw an error indicating no previous step exists.
     *
     * @throws Error when there is no previous step available
     */
    function goToPrevious() {
        const current = getCurrent();

        // Find the step with the highest order that is still < current.order
        const prev = stepsStorage.value
            .filter((s) => s.order < current.order)
            .sort((a, b) => b.order - a.order)[0];

        if (!prev) {
            // If there's no previous step, throw an error
            const errorMsg = `No previous step available. Currently at step ${current.id} (${current.title}) with order ${current.order}`;
            console.error('⚠️ ' + errorMsg);
            throw new Error(errorMsg);
        }

        // Navigate to the previous step
        console.log(`⬅️ Going to previous step: ${prev.id} (${prev.title})`);
        goToStep(prev.id);
    }

    /**
     * Return the ID of the next step (by `order`) or `null` if we're at the end.
     * This is resilient to sparse / non‑sequential order values (e.g. 10, 20, 30).
     */
    function getNextStepId(): number | null {
        const current = getCurrent();
        const next = stepsStorage.value
            .filter((s) => s.order > current.order)
            .sort((a, b) => a.order - b.order)[0];
        return next ? next.id : null;
    }

    return {
        // Configuration
        setSurvey,
        // State getters
        getCurrent,
        currentStep,
        progress,
        // Navigation
        goToStep,
        getNextStepId,
        goToPrevious,
        // Submission
        handleStepSubmit,
        // Debug access to responses
        responses: responsesStorage,
    };
}

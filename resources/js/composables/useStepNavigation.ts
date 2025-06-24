import { Step, StepResponse } from '@/types';
import { useStorage } from '@vueuse/core';
import { computed } from 'vue';

/**
 * Composable for navigating survey steps with persistence in localStorage.
 * Ensures `steps` and `currentStep` are always initialized and never null.
 */
export function useStepNavigation(surveyRunId: string) {
    // Persisted list of steps for this survey run
    const stepsStorage = useStorage<Step[]>(
        `survey_${surveyRunId}_steps`,
        [],
        localStorage
    );

    // Persisted current step ID for this survey run
    const currentStepIdStorage = useStorage<number | null>(
        `survey_${surveyRunId}_current_step`,
        null,
        localStorage
    );

    // Persisted responses for this survey run
    const responsesStorage = useStorage<StepResponse[]>(
        `survey_${surveyRunId}_responses`,
        [],
        localStorage
    );

    /**
     * Retrieve the last saved response (highest self_order).
     */
    function getLastResponse(): StepResponse | undefined {
        return responsesStorage.value.reduce<StepResponse | undefined>((prev, cur) => {
            if (!prev || cur.order > prev.order) return cur;
            return prev;
        }, undefined);
    }

    /**
     * Determine the default step: either next after last response or first in list.
     */
    function getDefaultStep(): Step {
        if (stepsStorage.value.length === 0) {
            throw new Error(
                `No steps available for survey ${surveyRunId}. Did you forget to call setSurvey()?`
            );
        }

        const last = getLastResponse();
        if (last) {
            const next = stepsStorage.value.find(s => s.id === last.next_step_id);
            if (next) return next;
        }

        return stepsStorage.value[0];
    }

    /**
     * Initialize survey steps and set the current step.
     * @param surveySteps Array of steps for this survey
     */
    function setSurvey(surveySteps: Step[]) {
        if (!surveySteps || surveySteps.length === 0) {
            throw new Error('setSurvey requires a non-empty array of steps');
        }
        stepsStorage.value = surveySteps;
        const defaultStep = getDefaultStep();
        currentStepIdStorage.value = defaultStep.id;
    }

    /**
     * Get current Step object; throws if not set.
     */
    function getCurrent(): Step {
        const id = currentStepIdStorage.value;
        if (id === null) {
            throw new Error(
                'Current step is not set. Did you forget to call setSurvey()?'
            );
        }
        const step = stepsStorage.value.find(s => s.id === id);
        if (!step) {
            throw new Error(`Current step ID ${id} is invalid`);
        }
        return step;
    }

    /**
     * Compute progress percentage (0-100).
     */
    const progress = computed(() => {
        const all = stepsStorage.value;
        const current = getCurrent();
        const idx = all.findIndex(s => s.id === current.id);
        return (idx / all.length) * 100;
    });

    /**
     * Submit a response and advance current step to the response's next_step_id.
     */
    function handleStepSubmit(response: StepResponse) {
        const idx = responsesStorage.value.findIndex(
            r => r.self_step_id === response.self_step_id
        );
        if (idx >= 0) {
            responsesStorage.value[idx] = response;
        } else {
            responsesStorage.value.push(response);
        }

        // Advance to next or default
        const nextId = response.next_step_id;
        const nextStep = stepsStorage.value.find(s => s.id === nextId);
        currentStepIdStorage.value = nextStep
            ? nextStep.id
            : getDefaultStep().id;
    }

    /**
     * Move to a specific step by object or ID.
     */
    function goToStep(stepOrId: Step | number) {
        const id = typeof stepOrId === 'number' ? stepOrId : stepOrId.id;
        if (!stepsStorage.value.find(s => s.id === id)) {
            throw new Error(`Step ID ${id} not found`);
        }
        currentStepIdStorage.value = id;
    }

    /**
     * Move to the previous step, or default if at start.
     */
    function goToPrevious() {
        const current = getCurrent();
        const prev = stepsStorage.value.find(s => s.order === current.order - 1);
        goToStep(prev ? prev.id : getDefaultStep().id);
    }

    /**
     * Get the ID of the next step, or null if at end.
     */
    function getNextStepId(): number | null {
        const current = getCurrent();
        const next = stepsStorage.value.find(s => s.order === current.order + 1);
        return next ? next.id : null;
    }

    return {
        // Configuration
        setSurvey,
        // State getters
        getCurrent,
        progress,
        // Navigation
        goToStep,
        getNextStepId,
        goToPrevious,
        // Submission
        handleStepSubmit
    };
}

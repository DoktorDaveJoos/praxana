// composables/useStepComponent.ts
import type { Step } from '@/types'
import DateStep from '@/components/surveys/steps/DateStep.vue';
import DialogStep from '@/components/surveys/steps/DialogStep.vue';
import MultipleChoice from '@/components/surveys/steps/MultipleChoice.vue';
import NumberStep from '@/components/surveys/steps/NumberStep.vue';
import ScaleStep from '@/components/surveys/steps/ScaleStep.vue';
import SingleChoice from '@/components/surveys/steps/SingleChoice.vue';
import TextStep from '@/components/surveys/steps/TextStep.vue';

// Map step_type → component definition
const componentMap: Record<string, any> = {
    single_choice: SingleChoice,
    multiple_choice: MultipleChoice,
    text: TextStep,
    date: DateStep,
    scale: ScaleStep,
    number: NumberStep,
};

export function useStepComponent() {
    const mapComponent = (step: Step) => {
        if (step.step_type === 'dialog') {
            return DialogStep
        }
        return componentMap[step.question_type]
    }

    return {
        mapComponent,
    }
}


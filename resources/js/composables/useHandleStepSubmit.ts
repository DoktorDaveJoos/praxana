import { Step, StepResponse } from '@/types';

const emit = defineEmits<{
    (e: 'submit', values: StepResponse<string>): void;
}>();

const map = {
    multiple_choice: 'text',
    single_choice: 'text',
    text: 'text',
    date: 'date',
    number: 'number',
    scale: 'number',
};
const mapQuestionType = (question_type: string) => {
    return map[question_type] || 'text';
};

export function handleStepSubmit(step: Step, value: string, choice_id: number | null, next_step_id: number | null) {
    emit('submit', {
        value: value,
        choice_id: choice_id,
        self_step_id: step.id,
        next_step_id: next_step_id,
        type: mapQuestionType(step.question_type),
    });
}

export function useHandleStepSubmit() {
    return { handleStepSubmit };
}

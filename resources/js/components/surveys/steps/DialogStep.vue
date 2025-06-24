<script setup lang="ts">
import { ref, computed } from 'vue';
import { StepResponse, Step } from '@/types';
import { useStepNavigation } from '@/composables/useStepNavigation';

const props = defineProps<{
    survey_run_id: string;
    steps: Step[];
}>();

const emit = defineEmits<{
    (e: 'submit', response: StepResponse): void;
}>();

// Setup navigation composable
const {
    setSurvey,
    getCurrent,
    handleStepSubmit,
    getNextStepId,
} = useStepNavigation(props.survey_run_id);

// Must initialize survey steps before using
setSurvey(props.steps);

// Reactive current step
const currentStep = computed(() => getCurrent());

// Placeholder for user answer; actual input is rendered via default slot
const answer = ref<string>('');

/**
 * Called when the user submits the dialog step
 */
function onSubmit() {
    const nextId = getNextStepId();
    const response: StepResponse = {
        self_step_id: currentStep.value.id,
        next_step_id: nextId ?? currentStep.value.id,
        type: 'dialog',
        value: answer.value,
    };

    // Persist and advance
    handleStepSubmit(response);
    // Emit to parent in case additional handling is needed
    emit('submit', response);
}
</script>

<template>
    <form class="w-full space-y-6" @submit.prevent="onSubmit">
        <!--
          Default slot should render question & input:
          <DialogStep :steps="steps" @submit="...">
            <template #default="{ step, answer }">
              <label>{{ step.question }}</label>
              <input v-model="answer" />
            </template>
        -->
        <slot :step="currentStep" :answer.sync="answer" />

        <!-- Actions (e.g. buttons) -->
        <slot name="actions" :submit="onSubmit" />
    </form>
</template>

<style scoped>
/* Your styles here */
</style>

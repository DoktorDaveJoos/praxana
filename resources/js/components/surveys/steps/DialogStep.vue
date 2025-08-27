<script setup lang="ts">
import { Step, StepResponse } from '@/types';

defineProps<{
    step: Step;
}>();

const emits = defineEmits<{
    (e: 'submit', data: StepResponse): void;
}>();

/**
 * Called when the user submits the dialog step
 * For dialog steps, we just emit an acknowledgment value
 */
const onSubmit = () => {
    emits('submit', { value: 'acknowledged', type: 'dialog' });
};
</script>

<template>
    <form class="space-y-6" @submit.prevent="onSubmit">
        <!-- Dialog steps typically don't need form inputs, just display content -->
        <!-- The step content is already shown in StepWrapper's CardDescription -->

        <!-- Actions (e.g. buttons) -->
        <slot name="actions" />
    </form>
</template>

<style scoped>
/* Your styles here */
</style>

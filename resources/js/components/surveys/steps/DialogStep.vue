<script setup lang="ts">
import { Step } from '@/types';

defineProps<{
    step: Step;
}>();

const emit = defineEmits<{
    (e: 'submit', value: string, type: string): void;
}>();

/**
 * Called when the user submits the dialog step
 * For dialog steps, we just emit an acknowledgment value
 */
function onSubmit() {
    emit('submit', 'acknowledged', 'dialog');
}
</script>

<template>
    <div class="w-full space-y-6">
        <!-- Dialog steps typically don't need form inputs, just display content -->
        <!-- The step content is already shown in StepWrapper's CardDescription -->

        <!-- Actions (e.g. buttons) -->
        <slot name="actions" :submit="onSubmit" />
    </div>
</template>

<style scoped>
/* Your styles here */
</style>

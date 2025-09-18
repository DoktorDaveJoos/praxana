<script setup lang="ts">
import { useStepComponent } from '@/composables/useStepComponent'
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import type { Step, StepResponse } from '@/types';
import { GraduationCap } from 'lucide-vue-next';

defineProps<{
    step: Step;
}>();

const emits = defineEmits<{
    (e: 'next', data: StepResponse): void;
    (e: 'prev'): void;
}>();

const { mapComponent } = useStepComponent();

const isQuestion = (step: Step) => step.step_type !== 'info';

</script>

<template>
    <Card class="w-full">
        <CardHeader>
            <CardTitle class="flex items-center">
                <GraduationCap v-if="!isQuestion(step)" class="text-foreground mr-4 h-6 w-6" />
                {{ step.title }}
            </CardTitle>

            <CardDescription>
                {{ step.content }}
            </CardDescription>
        </CardHeader>
        <CardContent>
            <component :is="mapComponent(step)" :step="step" @submit="emits('next', $event)" :key="step.id">
                <template #actions>
                    <div class="mt-6 flex justify-between">
                        <Button :disabled="!step.previous_step_id" variant="outline" @click="emits('prev')" type="button"> Zurück </Button>
                        <Button type="submit"> Weiter</Button>
                    </div>
                </template>
            </component>
        </CardContent>
    </Card>
</template>

<style scoped></style>

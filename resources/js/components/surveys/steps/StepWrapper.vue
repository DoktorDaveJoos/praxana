<script setup lang="ts">
import DateStep from '@/components/surveys/steps/DateStep.vue';
import DialogStep from '@/components/surveys/steps/DialogStep.vue';
import MultipleChoice from '@/components/surveys/steps/MultipleChoice.vue';
import NumberStep from '@/components/surveys/steps/NumberStep.vue';
import ScaleStep from '@/components/surveys/steps/ScaleStep.vue';
import SingleChoice from '@/components/surveys/steps/SingleChoice.vue';
import TextStep from '@/components/surveys/steps/TextStep.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { SharedData, type Step } from '@/types';
import { GraduationCap } from 'lucide-vue-next';


defineProps<{
    step: Step;
}>();

const emits = defineEmits<{
    (e: 'next'): void;
    (e: 'prev'): void;
}>();

// Map step_type → component definition
const componentMap: Record<string, any> = {
    single_choice: SingleChoice,
    multiple_choice: MultipleChoice,
    text: TextStep,
    date: DateStep,
    scale: ScaleStep,
    number: NumberStep,
};

const mapComponent = (step: Step) => {
    if (step && step.step_type === 'dialog') {
        return DialogStep; // Special case for dialog steps
    }
    return componentMap[step.question_type];
};

const isQuestion = (step: Step) => step.question_type !== 'dialog';
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
            <component :is="mapComponent(step)" :step="step"></component>

            <div class="mt-6 flex justify-between">
                <Button :disabled="!step.previous_step_id" variant="outline" @click="emits('prev')"  type="button">
                    Zurück
                </Button>
                <Button :disabled="!step.next_step_id" @click="emits('next')"  type="button">
                    Weiter
                </Button>
            </div>
        </CardContent>
    </Card>
</template>

<style scoped></style>

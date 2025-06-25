<script setup lang="ts">
import DateStep from '@/components/surveys/steps/DateStep.vue';
import MultipleChoice from '@/components/surveys/steps/MultipleChoice.vue';
import NumberStep from '@/components/surveys/steps/NumberStep.vue';
import ScaleStep from '@/components/surveys/steps/ScaleStep.vue';
import SingleChoice from '@/components/surveys/steps/SingleChoice.vue';
import TextStep from '@/components/surveys/steps/TextStep.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Skeleton } from '@/components/ui/skeleton';
import { useStepNavigation } from '@/composables/useStepNavigation';
import { type Step, type StepResponse } from '@/types';
import { GraduationCap } from 'lucide-vue-next';
import DialogStep from '@/components/surveys/steps/DialogStep.vue';
import { onMounted } from 'vue';

const props = defineProps<{
    surveyRunId: string;
    steps: Step[];
}>();

const {
    setSurvey,
    currentStep,
    goToPrevious,
    handleStepSubmit,
    getNextStepId,
    responses
} = useStepNavigation(props.surveyRunId);

// Initialize the survey steps
onMounted(() => {
    console.log('🚀 Initializing survey with steps:', props.steps);
    console.log('📋 Survey Run ID:', props.surveyRunId);
    setSurvey(props.steps);
    console.log('✅ Survey initialization complete');
});

// Handle step submission from child components
function onStepSubmit(value: string, type: string = 'text') {
    if (!currentStep.value) return;

    const nextId = getNextStepId();
    const response: StepResponse = {
        value,
        order: currentStep.value.order,
        self_step_id: currentStep.value.id,
        next_step_id: nextId,
        type
    };

    // Debug logging: Log the current response being submitted
    console.log('🔄 Submitting step response:', {
        stepId: response.self_step_id,
        stepTitle: currentStep.value.title,
        value: response.value,
        type: response.type,
        order: response.order,
        nextStepId: response.next_step_id
    });

    handleStepSubmit(response);

    // Debug logging: Log all current responses after submission
    console.log('📊 All survey responses after submission:', responses.value);
}

// Handle previous step navigation
function onPrevious() {
    console.log('⬅️ Attempting to navigate to previous step from:', currentStep.value?.title);
    try {
        goToPrevious();
        console.log('✅ Navigation complete. Current step:', currentStep.value?.title);
    } catch (error) {
        console.warn('Navigation to previous step failed:', error);
        // The error is already logged in goToPrevious
        // You could add UI feedback here if needed
    }
}

const isQuestion = (step: Step | null): boolean => {
    if (!step) return false;
    return step.step_type === 'question';
};

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
</script>

<template>
    <Card class="w-full">
        <CardHeader>
            <CardTitle class="flex items-center">
                <template v-if="currentStep">
                    <GraduationCap v-if="!isQuestion(currentStep)" class="text-foreground mr-4 h-6 w-6" />
                    {{ currentStep?.title }}
                </template>
                <Skeleton v-else class="h-4 w-2/3" />
            </CardTitle>

            <CardDescription>
                <template v-if="currentStep">
                    {{ currentStep?.content }}
                </template>
                <template v-else>
                    <Skeleton class="h-4 w-full" />
                    <Skeleton class="mt-1 h-4 w-4/5" />
                </template>
            </CardDescription>
        </CardHeader>
        <CardContent>
            <template v-if="currentStep">
                <component
                    :is="mapComponent(currentStep)"
                    :step="currentStep"
                    @submit="onStepSubmit"
                >
                    <template v-slot:actions="{ submit }">
                        <div class="mt-6 flex justify-between">
                            <Button variant="outline" @click="onPrevious" type="button">
                                Zurück
                            </Button>
                            <Button @click="submit" type="submit">
                                Weiter
                            </Button>
                        </div>
                    </template>
                </component>
            </template>
        </CardContent>
    </Card>
</template>

<style scoped></style>

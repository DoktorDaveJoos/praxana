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
import { type Step } from '@/types';
import { GraduationCap } from 'lucide-vue-next';

const props = defineProps<{
    surveyRunId: string;
    steps: Step[];
}>();

const { current, goToPreviousStep } = useStepNavigation(props.surveyRunId);

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

const mapComponent = (type: string) => {
    // returns the component or `undefined` if not found
    return componentMap[type];
};
</script>

<template>
    <Card class="w-full">
        <CardHeader>
            <CardTitle class="flex items-center">
                <template v-if="current">
                    <GraduationCap v-if="!isQuestion(current)" class="text-foreground mr-4 h-6 w-6" />
                    {{ current?.title }}
                </template>
                <Skeleton v-else class="h-4 w-2/3" />
            </CardTitle>

            <CardDescription>
                <template v-if="current">
                    {{ current?.content }}
                </template>
                <template v-else>
                    <Skeleton class="h-4 w-full" />
                    <Skeleton class="h-4 mt-1 w-4/5" />
                </template>
            </CardDescription>
        </CardHeader>
        <CardContent>
            <template v-if="current">
                <template v-if="isQuestion(current)">
                    <component v-if="mapComponent(current?.question_type)" :is="mapComponent(current?.question_type)" :step="current">
                        <template v-slot:actions="{ submit }">
                            <div class="mt-6 flex justify-between">
                                <Button variant="outline"> Abbrechen</Button>
                                <Button @click="submit"> Weiter</Button>
                            </div>
                        </template>
                    </component>
                </template>
                <template v-else>
                    <div class="flex justify-between">
                        <Button variant="outline">Zurück</Button>
                        <Button> Verstanden!</Button>
                    </div>
                </template>
            </template>
        </CardContent>
    </Card>
</template>

<style scoped></style>

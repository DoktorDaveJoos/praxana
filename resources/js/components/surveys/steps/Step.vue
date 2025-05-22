<script setup lang="ts">
import DateStep from '@/components/surveys/steps/DateStep.vue';
import MultipleChoice from '@/components/surveys/steps/MultipleChoice.vue';
import NumberStep from '@/components/surveys/steps/NumberStep.vue';
import ScaleStep from '@/components/surveys/steps/ScaleStep.vue';
import SingleChoice from '@/components/surveys/steps/SingleChoice.vue';
import TextStep from '@/components/surveys/steps/TextStep.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { type Step, StepResponse } from '@/types';
import { GraduationCap } from 'lucide-vue-next';

defineProps<{
    step: Step;
}>();

const emit = defineEmits<{
    (e: 'submit', value: StepResponse<string>): void;
    (e: 'previous', value: StepResponse<string>): void;
}>();

const isQuestion = (step: Step): boolean => {
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

function mapComponent(type: string) {
    // returns the component or `undefined` if not found
    return componentMap[type];
}

const handleStepSubmit = (values: StepResponse<string>) => {
    emit('submit', values);
};
</script>

<template>
    <Card class="w-full">
        <CardHeader>
            <CardTitle class="flex items-center">
                <GraduationCap v-if="! isQuestion(step)" class="text-foreground h-6 w-6 mr-4" />
                {{ step.title }}
            </CardTitle>
            <CardDescription>
                {{ step.content }}
            </CardDescription>
        </CardHeader>
        <CardContent>
            <template v-if="isQuestion(step)">
                <component v-if="mapComponent(step.question_type)" :is="mapComponent(step.question_type)" :step="step" @submit="handleStepSubmit">
                    <template v-slot:actions="{ submit }">
                        <div class="mt-6 flex justify-between">
                            <Button
                                @click="
                                    emit('previous', {
                                        step_id: step.id,
                                        value: null,
                                    })
                                "
                                variant="outline"
                            >
                                Abbrechen
                            </Button>
                            <Button @click="submit"> Weiter</Button>
                        </div>
                    </template>
                </component>
            </template>
            <template v-else>
                <div class="flex justify-between">
                    <Button
                        variant="outline"
                        @click="
                            emit('previous', {
                                step_id: step.id,
                                value: null,
                            })
                        "
                    >
                        Zurück
                    </Button>
                    <Button
                        @click="
                            emit('submit', {
                                step_id: step.id,
                                value: null,
                            })
                        "
                    >
                        Verstanden!
                    </Button>
                </div>
            </template>
        </CardContent>
    </Card>
</template>

<style scoped></style>

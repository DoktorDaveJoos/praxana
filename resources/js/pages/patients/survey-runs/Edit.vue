<script setup lang="ts">
import HeadingSmall from '@/components/HeadingSmall.vue';
import Step from '@/components/surveys/steps/Step.vue';
import { Badge } from '@/components/ui/badge';
import { Progress } from '@/components/ui/progress';
import AppLayout from '@/layouts/AppLayout.vue';
import PatientsLayout from '@/layouts/patients/Layout.vue';
import type { BreadcrumbItem, Patient, Resource, SharedData, StepResponse, Survey, SurveyRun } from '@/types';
import { Head, usePage } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps<{
    patient: Resource<Patient>;
    surveyRun: Resource<SurveyRun>;
    survey: Resource<Survey>;
}>();

const breadcrumbItems: BreadcrumbItem[] = [
    {
        title: 'Patienten',
        href: route('practices.patients.index', {
            practice: usePage<SharedData>().props.auth.practice.id,
        }),
    },
    {
        title: props.patient.data.name,
        href: route('practices.patients.show', {
            practice: usePage<SharedData>().props.auth.practice.id,
            patient: props.patient.data.id,
        }),
    },
    {
        title: 'Anamnese',
        href: route('practices.patients.survey-runs.index', {
            practice: usePage<SharedData>().props.auth.practice.id,
            patient: props.patient.data.id,
        }),
    },
    {
        title: props.surveyRun.data.name,
        href: route('practices.patients.survey-runs.show', {
            practice: usePage<SharedData>().props.auth.practice.id,
            patient: props.patient.data.id,
            survey_run: props.surveyRun.data.id,
        }),
    },
    {
        title: 'Bearbeiten',
        href: route('practices.patients.survey-runs.edit', {
            practice: usePage<SharedData>().props.auth.practice.id,
            patient: props.patient.data.id,
            survey_run: props.surveyRun.data.id,
        }),
    },
];
const computeProgress = (step: number) => {
    const totalSteps = props.survey.data.steps?.length || 1;
    const currentStepIndex = props.survey.data.steps?.findIndex((s) => s.id === step) || 0;
    return Math.round((currentStepIndex / totalSteps) * 100);
};

const currentStep = ref<number>(props.survey.data.steps?.[0].id || 0);
const progress = ref(computeProgress(currentStep.value));
const stepResponses = ref<StepResponse<string>[]>([]);

const next = (resp: StepResponse<string>) => {
    // Check if there is already a response with step_id
    const existingResponse = stepResponses.value.find((response) => response.step_id === resp.step_id);
    if (existingResponse) {
        // Update the existing response
        existingResponse.value = resp.value;
    } else {
        // Add a new response
        stepResponses.value.push(resp);
    }

    currentStep.value = resp.step_id + 1;
    progress.value = computeProgress(currentStep.value);
};

const previous = (resp: StepResponse<string>) => {
    currentStep.value = resp.step_id - 1;
    progress.value = computeProgress(currentStep.value);
};
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbItems">
        <Head title="Anamnese" />

        <PatientsLayout :patient="patient">
            <div class="space-y-6">
                <HeadingSmall :title="survey.data.name" :description="survey.data.description" />

                <div class="flex items-center space-x-2">
                    <Badge variant="secondary" class="font-mono">v.{{ survey.data.version }}</Badge>
                </div>

                <Progress v-model="progress" class="w-full" />

                <Step
                    v-if="survey.data.steps"
                    :step="survey.data.steps.find((step) => step.id === currentStep) ?? survey.data.steps[0]"
                    @submit="next"
                    @previous="previous"
                />
            </div>
        </PatientsLayout>
    </AppLayout>
</template>

<style scoped></style>

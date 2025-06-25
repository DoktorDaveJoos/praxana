<script setup lang="ts">
import HeadingSmall from '@/components/HeadingSmall.vue';
import StepWrapper from '@/components/surveys/steps/StepWrapper.vue';
import { Badge } from '@/components/ui/badge';
import { Progress } from '@/components/ui/progress'
import AppLayout from '@/layouts/AppLayout.vue';
import PatientsLayout from '@/layouts/patients/Layout.vue';
import type { BreadcrumbItem, Patient, Resource, SharedData, Survey, SurveyRun } from '@/types';
import { Head, usePage } from '@inertiajs/vue3';
import { useStepNavigation } from '@/composables/useStepNavigation';
import { computed, onMounted } from 'vue';

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

const { setSurvey, progress } = useStepNavigation(props.surveyRun.data.id);

// Initialize the survey steps
onMounted(() => {
    console.log('Setting survey steps for progress tracking...');
    setSurvey(props.survey.data.steps);
});
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

                <StepWrapper
                    :surveyRunId="surveyRun.data.id"
                    :steps="survey.data.steps"
                />
            </div>
        </PatientsLayout>
    </AppLayout>
</template>

<style scoped></style>

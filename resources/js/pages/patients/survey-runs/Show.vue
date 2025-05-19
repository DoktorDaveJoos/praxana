<script setup lang="ts">
import AlertMarkdownContent from '@/components/AlertMarkdownContent.vue';
import HeadingSmall from '@/components/HeadingSmall.vue';
import BaseResponseCard from '@/components/surveys/responses/BaseResponseCard.vue';
import { Alert, AlertDescription, AlertTitle } from '@/components/ui/alert';
import { Badge } from '@/components/ui/badge';
import { Separator } from '@/components/ui/separator';
import AppLayout from '@/layouts/AppLayout.vue';
import PatientsLayout from '@/layouts/patients/Layout.vue';
import { BreadcrumbItem, Patient, Resource, SharedData, type SurveyRun } from '@/types';
import { Head, usePage } from '@inertiajs/vue3';
import { formatDistanceToNow } from 'date-fns';
import { de } from 'date-fns/locale';
import { Sparkles } from 'lucide-vue-next';
import { computed } from 'vue';

const props = defineProps<{
    patient: Resource<Patient>;
    surveyRun: Resource<SurveyRun>;
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
];

const infoText = computed(() => {
    return props.surveyRun.data.finished_at
        ? `Abgeschlossen ${formatDistanceToNow(new Date(props.surveyRun.data.finished_at), {
              addSuffix: true,
              locale: de,
          })}`
        : `Gestart ${formatDistanceToNow(new Date(props.surveyRun.data.started_at), {
              addSuffix: true,
              locale: de,
          })}`;
});
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbItems">
        <Head title="Anamnese" />

        <PatientsLayout :patient="patient">
            <div class="space-y-6">
                <HeadingSmall :title="surveyRun.data.name" description="Übersicht und Verwaltung der Anamnesedaten des Patienten." />

                <div class="flex items-center space-x-2">
                    <Badge variant="secondary">{{ surveyRun.data.status }}</Badge>
                    <p class="text-muted-foreground text-xs">{{ infoText }}</p>
                </div>

                <template v-if="surveyRun.data.status === 'completed'">
                    <Alert>
                        <Sparkles class="h-4 w-4" />
                        <AlertTitle>KI Analyse</AlertTitle>
                        <AlertDescription>
                            <Separator class="my-2" />
                            <AlertMarkdownContent :content="surveyRun.data.ai_analysis" />
                        </AlertDescription>
                    </Alert>

                    <div class="container mx-auto grid gap-4">
                        <HeadingSmall title="Fragen & Antworten" />
                        <BaseResponseCard
                            v-for="(response, index) in surveyRun.data.responses"
                            v-bind:key="response.id"
                            :response="response"
                            :index="index"
                        />
                    </div>
                </template>
                <template v-else>
                    <Alert>

                    </Alert>
                </template>
            </div>
        </PatientsLayout>
    </AppLayout>
</template>

<style scoped></style>

<script setup lang="ts">
import HeadingSmall from '@/components/HeadingSmall.vue';
import BaseResponseCard from '@/components/surveys/responses/BaseResponseCard.vue';
import { Alert, AlertDescription, AlertTitle } from '@/components/ui/alert';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { FormControl, FormDescription, FormField, FormItem, FormLabel, FormMessage } from '@/components/ui/form';
import AppLayout from '@/layouts/AppLayout.vue';
import PatientsLayout from '@/layouts/patients/Layout.vue';
import type { BreadcrumbItem, Patient, Resource, Response, SharedData, Survey, SurveyRun } from '@/types';
import { Head, router, usePage } from '@inertiajs/vue3';
import { toTypedSchema } from '@vee-validate/zod';
import { ClipboardCheck } from 'lucide-vue-next';
import { useForm } from 'vee-validate';
import * as z from 'zod';

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

const formSchema = toTypedSchema(
    z.object({
        ack: z.boolean().default(false).optional(),
    }),
);

const { handleSubmit } = useForm({
    validationSchema: formSchema,
    initialValues: {
        ack: false,
    },
});

const onSubmit = handleSubmit(() => {
    router.put(
        route('practices.patients.survey-runs.update', {
            practice: usePage<SharedData>().props.auth.practice.id,
            patient: props.patient.data.id,
            survey_run: props.surveyRun.data.id,
        }),
        {
            status: 'completed',
        },
    );
});

const handleBack = () => {
    //derive latest step id from responses
    const latest = props.surveyRun.data.responses?.reduce((acc: Response, curr: Response) => {
        if (acc.id < curr.id) {
            return curr;
        }
        return acc;
    });

    router.visit(
        route('practices.patients.survey-runs.steps.show', {
            practice: usePage<SharedData>().props.auth.practice.id,
            patient: props.patient.data.id,
            survey_run: props.surveyRun.data.id,
            step: latest?.step_id,
        }),
        { preserveState: true },
    );
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

                <Alert>
                    <ClipboardCheck class="h-4 w-4" />
                    <AlertTitle>Fertig!</AlertTitle>
                    <AlertDescription>
                        Bitte prüfe deine Antworten noch einmal sorgfältig. Anschließend bestätige mit dem Button unten, dass deine Antworten korrekt
                        sind.
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

                <form class="space-y-6" @submit="onSubmit">
                    <FormField v-slot="{ value, handleChange }" type="checkbox" name="mobile">
                        <FormItem class="flex flex-row items-start space-y-0 gap-x-3 rounded-md border p-4 shadow">
                            <FormControl>
                                <Checkbox :model-value="value" @update:model-value="handleChange" />
                            </FormControl>
                            <div class="space-y-1 leading-none">
                                <FormLabel>Ich bestätige die Richtigkeit meiner Angaben</FormLabel>
                                <FormDescription>
                                    Mit der Bestätigung erklären Sie, dass alle von Ihnen gemachten Angaben wahrheitsgemäß sind.
                                </FormDescription>
                                <FormMessage />
                            </div>
                        </FormItem>
                        <div class="flex justify-between">
                            <Button variant="secondary" type="button" @click="handleBack">Zurück</Button>
                            <Button :disabled="!value" type="submit">Bestätigen</Button>
                        </div>
                    </FormField>
                </form>
            </div>
        </PatientsLayout>
    </AppLayout>
</template>

<style scoped></style>

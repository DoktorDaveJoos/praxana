<script setup lang="ts">
import DataTable from '@/components/DataTable.vue';
import HeadingSmall from '@/components/HeadingSmall.vue';
import { columns } from '@/components/patients/survey-runs/columns';
import { Input } from '@/components/ui/input';
import AppLayout from '@/layouts/AppLayout.vue';
import PatientsLayout from '@/layouts/patients/Layout.vue';
import { BreadcrumbItem, Patient, Resource, type ResourceCollection, SharedData, type SurveyRun } from '@/types';
import { Head, usePage } from '@inertiajs/vue3';

const props = defineProps<{
    patient: Resource<Patient>;
    surveyRuns: ResourceCollection<SurveyRun>;
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
];
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbItems">
        <Head title="Anamnese" />

        <PatientsLayout :patient="patient">
            <div class="space-y-6">
                <HeadingSmall title="Anamnese" description="Übersicht und Verwaltung der Anamnesedaten des Patienten." />

                <div class="container mx-auto">
                    <DataTable :columns="columns" :data="surveyRuns.data.map((sr) => ({ ...sr, patientId: patient.data.id }))">
                        <template #filter="{ table }">
                            <Input
                                class="max-w-sm"
                                placeholder="Suche nach Name"
                                :model-value="table.getColumn('name')?.getFilterValue() as string"
                                @update:model-value="table.getColumn('name')?.setFilterValue($event)"
                            />
                        </template>
                    </DataTable>
                </div>
            </div>
        </PatientsLayout>
    </AppLayout>
</template>

<style scoped></style>

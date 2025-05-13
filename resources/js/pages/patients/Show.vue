<script setup lang="ts">
import Heading from '@/components/Heading.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem, Patient, Resource, SharedData } from '@/types';
import { Head, usePage } from '@inertiajs/vue3';

const props = defineProps<{
    patient: Resource<Patient>;
}>();

const breadcrumbItems: BreadcrumbItem[] = [
    {
        title: 'Patienten',
        href: route('practices.patients.index', {
            practice: usePage<SharedData>().props.auth.practice.id,
        }),
    },
    {
        title: props.patient.data.last_name + ', ' + props.patient.data.first_name,
        href: route('practices.patients.show', {
            practice: usePage<SharedData>().props.auth.practice.id,
            patient: props.patient.data.id,
        }),
    },
];
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbItems">
        <Head title="Patienten" />

        <div class="px-4 py-6">
            <Heading :title="patient.data.name" description="Detaillierte Informationen und Verwaltungsoptionen für den ausgewählten Patienten." />
        </div>
    </AppLayout>
</template>

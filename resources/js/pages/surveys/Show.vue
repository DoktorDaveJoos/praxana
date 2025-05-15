<script setup lang="ts">
import HeadingSmall from '@/components/HeadingSmall.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import PatientsLayout from '@/layouts/patients/Layout.vue';
import { type BreadcrumbItem, Patient, Resource, SharedData } from '@/types';
import { Head, usePage } from '@inertiajs/vue3';
import { defineProps } from 'vue';

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
        title: props.patient.data.name,
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

        <PatientsLayout :patient="patient">
            <div class="space-y-6">
                <HeadingSmall title="Stammdaten" description="Bearbeiten und verwalten Sie die grundlegenden Informationen des Patienten." />
            </div>
        </PatientsLayout>
    </AppLayout>
</template>

<style scoped></style>

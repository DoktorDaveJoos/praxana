<script setup lang="ts">
import Heading from '@/components/Heading.vue';
import { columns } from '@/components/patients/columns';
import DataTable from '@/components/patients/DataTable.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem, Patient, ResourceCollection, SharedData } from '@/types';
import { Head, usePage } from '@inertiajs/vue3';

defineProps<{
    patients: ResourceCollection<Patient>;
}>();

const breadcrumbItems: BreadcrumbItem[] = [
    {
        title: 'Patienten',
        href: route('practices.patients.index', {
            practice: usePage<SharedData>().props.auth.practice.id,
        }),
    },
];
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbItems">
        <Head title="Patienten" />

        <div class="px-4 py-6">
            <Heading title="Patienten Akte" description="Übersicht und Verwaltung der Patientendaten in Ihrer Praxis." />

            <div class="container mx-auto">
                <DataTable :columns="columns" :data="patients.data" />
            </div>
        </div>
    </AppLayout>
</template>

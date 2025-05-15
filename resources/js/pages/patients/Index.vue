<script setup lang="ts">
import DataTable from '@/components/DataTable.vue';
import Heading from '@/components/Heading.vue';
import { columns } from '@/components/patients/columns';
import { Input } from '@/components/ui/input';
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
                <DataTable :columns="columns" :data="patients.data">
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
    </AppLayout>
</template>

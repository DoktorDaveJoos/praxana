<script setup lang="ts">
import DataTable from '@/components/DataTable.vue';
import Heading from '@/components/Heading.vue';
import { selectSurveyColumns } from '@/components/surveys/selectSurveyColumns';
import { Input } from '@/components/ui/input';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem, Survey, ResourceCollection, SharedData } from '@/types';
import { Head, usePage } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';

defineProps<{
    surveys: ResourceCollection<Survey>;
}>();

const columns = selectSurveyColumns({ withSelection: false });

const breadcrumbItems: BreadcrumbItem[] = [
    {
        title: 'Fragebögen',
        href: route('practices.surveys.index', {
            practice: usePage<SharedData>().props.auth.practice.id,
        }),
    },
];
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbItems">
        <Head title="Fragebögen" />

        <div class="px-4 py-6">
            <div class="flex justify-between">
            <Heading title="Fragebögen" description="Übersicht und Verwaltung der Fragebögen." />
                <Button type="button">Hinzufügen</Button>
            </div>

            <div class="container mx-auto">
                <DataTable :columns="columns" :data="surveys.data">
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

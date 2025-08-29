<script setup lang="ts">
import DataTable from '@/components/DataTable.vue';
import Heading from '@/components/Heading.vue';
import { selectSurveyColumns } from '@/components/surveys/selectSurveyColumns';
import { Button } from '@/components/ui/button';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuLabel,
    DropdownMenuSeparator,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import { Input } from '@/components/ui/input';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem, ResourceCollection, SharedData, Survey } from '@/types';
import { Head, router, usePage } from '@inertiajs/vue3';

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

const handleClick = (type: string) => {
    switch (type) {
        case 'yaml':
            router.visit(
                route('practices.surveys.create', {
                    practice: usePage<SharedData>().props.auth.practice.id,
                }),
            );
            break;
        default:
            throw new Error('Unknown type');
    }
};
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbItems">
        <Head title="Fragebögen" />

        <div class="px-4 py-6">
            <div class="flex justify-between">
                <Heading title="Fragebögen" description="Übersicht und Verwaltung der Fragebögen." />
                <DropdownMenu>
                    <DropdownMenuTrigger>
                        <Button>Hinzufügen</Button>
                    </DropdownMenuTrigger>
                    <DropdownMenuContent>
                        <DropdownMenuLabel>Optionen</DropdownMenuLabel>
                        <DropdownMenuSeparator />
                        <DropdownMenuItem @click="handleClick('yaml')">YAML Editor</DropdownMenuItem>
                        <DropdownMenuItem disabled>JSON Editor</DropdownMenuItem>
                    </DropdownMenuContent>
                </DropdownMenu>
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

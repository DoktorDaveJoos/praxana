<script setup lang="ts">
import TipTapEditor from '@/components/TipTapEditor.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem, SharedData } from '@/types';
import { Head, usePage } from '@inertiajs/vue3';
import { useStorage } from '@vueuse/core';
import CodeMirrorEditor from '@/components/CodeMirrorEditor.vue';

const breadcrumbItems: BreadcrumbItem[] = [
    {
        title: 'Fragebögen',
        href: route('practices.surveys.create', {
            practice: usePage<SharedData>().props.auth.practice.id,
        }),
    },
    {
        title: 'Erstellen',
        href: route('practices.surveys.create', {
            practice: usePage<SharedData>().props.auth.practice.id,
        }),
    },
];

// bind object
const state = useStorage(`${usePage<SharedData>().props.auth.practice.id}-yaml-create`, '', localStorage);
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbItems">
        <Head title="Fragebögen" />
        <div class="p-6">
<!--            <TipTapEditor v-model="state" />-->
            <CodeMirrorEditor v-model="state" />
        </div>
    </AppLayout>
</template>

<style scoped></style>

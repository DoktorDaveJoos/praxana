<script setup lang="ts">
import CodeMirrorEditor from '@/components/CodeMirrorEditor.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem, SharedData } from '@/types';
import { Head, router, usePage } from '@inertiajs/vue3';
import { useStorage } from '@vueuse/core';

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

const handleUpload = () => {
    console.log('YEAHHHH BUDDY');

    router.post(
        route('practices.surveys.store', {
            practice: usePage<SharedData>().props.auth.practice.id,
        }),
        JSON.parse(state.value),
    );
};
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbItems">
        <Head title="Fragebögen" />
        <div class="p-6">
            <!--            <TipTapEditor v-model="state" />-->
            <CodeMirrorEditor v-model="state" @upload="handleUpload" />
        </div>
    </AppLayout>
</template>

<style scoped></style>

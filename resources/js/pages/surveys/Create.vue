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
];

// bind object
const state = useStorage(`${usePage<SharedData>().props.auth.practice.id}-yaml-create`, '', localStorage);

// default: comment-style guidance + minimal JSON skeleton
if (!state.value) {
    state.value = `// JSON Fragebogen Template
// ----------------------
// • Ersetze <PLATZHALTER> durch deine Werte
// • "steps" ist ein Array von Schritten (z. B. Fragen)
// • Unterstützte Eingabetypen: "text", "number", "select", ...
// • Siehe Dokumentation: https://laravel.com/docs/starter-kits#vue

{
  "type": "survey",
  "version": 1,
  "name": "<NAME>",
  "description": "Kurze Beschreibung des Fragebogens.",
  "steps": [
    {
      "type": "question",
      "title": "Wie heißt du?",
      "input": {
        "type": "text",
        "required": true,
        "placeholder": "Dein Name"
      }
    }
  ]
}
`;
}
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbItems">
        <Head title="Fragebögen" />
        <div class="p-6">
            <TipTapEditor v-model="state" />
            <CodeMirrorEditor v-model="state" />
        </div>
    </AppLayout>
</template>

<style scoped></style>

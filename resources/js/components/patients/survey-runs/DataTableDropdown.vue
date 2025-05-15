<script setup lang="ts">
import { Button } from '@/components/ui/button'
import { DropdownMenu, DropdownMenuContent, DropdownMenuItem, DropdownMenuLabel, DropdownMenuSeparator, DropdownMenuTrigger } from '@/components/ui/dropdown-menu'
import { MoreHorizontal } from 'lucide-vue-next'
import { SharedData, SurveyRun } from '@/types';
import { usePage } from '@inertiajs/vue3';

const props = defineProps<{
    patientId: string,
    surveyRun: SurveyRun
}>()

const page = usePage<SharedData>();

function showSurvey() {
    window.location.href = route('practices.patients.survey-runs.show', {
        practice: page.props.auth.practice.id,
        patient: props.patientId,
        survey_run: props.surveyRun.id
    });
}
</script>

<template>
    <DropdownMenu>
        <DropdownMenuTrigger as-child>
            <Button variant="ghost" class="w-8 h-8 p-0">
                <span class="sr-only">Menü öffnen</span>
                <MoreHorizontal class="w-4 h-4" />
            </Button>
        </DropdownMenuTrigger>
        <DropdownMenuContent align="end">
            <DropdownMenuLabel>Aktionen </DropdownMenuLabel>
            <DropdownMenuItem @click="showSurvey">Anzeigen</DropdownMenuItem>
            <DropdownMenuSeparator />
            <DropdownMenuItem class="text-destructive">Verwerfen</DropdownMenuItem>
        </DropdownMenuContent>
    </DropdownMenu>
</template>

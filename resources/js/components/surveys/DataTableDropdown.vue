<script setup lang="ts">
import { Button } from '@/components/ui/button'
import { DropdownMenu, DropdownMenuContent, DropdownMenuItem, DropdownMenuLabel, DropdownMenuSeparator, DropdownMenuTrigger } from '@/components/ui/dropdown-menu'
import { MoreHorizontal } from 'lucide-vue-next'
import { Survey, SharedData } from '@/types';
import { usePage, router } from '@inertiajs/vue3';

const props = defineProps<{
    survey: Survey
}>()

const page = usePage<SharedData>();

function showSurvey() {

    console.log(props.survey);

    router.visit(route('practices.surveys.show', {
        practice: page.props.auth.practice.id,
        survey: props.survey.id,
    }));
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
            <DropdownMenuLabel>Aktionen</DropdownMenuLabel>
            <DropdownMenuItem @click="showSurvey">Fragebogen anzeigen</DropdownMenuItem>
            <DropdownMenuSeparator />
            <DropdownMenuItem class="text-destructive">Fragebogen löschen</DropdownMenuItem>
        </DropdownMenuContent>
    </DropdownMenu>
</template>

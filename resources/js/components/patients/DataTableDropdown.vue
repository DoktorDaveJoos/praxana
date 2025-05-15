<script setup lang="ts">
import { Button } from '@/components/ui/button'
import { DropdownMenu, DropdownMenuContent, DropdownMenuItem, DropdownMenuLabel, DropdownMenuSeparator, DropdownMenuTrigger } from '@/components/ui/dropdown-menu'
import { MoreHorizontal } from 'lucide-vue-next'
import { Patient, SharedData } from '@/types';
import { usePage } from '@inertiajs/vue3';

const props = defineProps<{
    patient: Patient
}>()

const page = usePage<SharedData>();

function showPatient() {
    window.location.href = route('practices.patients.show', {
        practice: page.props.auth.practice.id,
        patient: props.patient.id,
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
            <DropdownMenuLabel>Aktionen</DropdownMenuLabel>
            <DropdownMenuItem @click="showPatient">Patient anzeigen</DropdownMenuItem>
            <DropdownMenuSeparator />
            <DropdownMenuItem class="text-destructive">Patient löschen</DropdownMenuItem>
        </DropdownMenuContent>
    </DropdownMenu>
</template>

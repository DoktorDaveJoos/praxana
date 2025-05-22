<script setup lang="ts">
import DataTable from '@/components/DataTable.vue';
import HeadingSmall from '@/components/HeadingSmall.vue';
import { columns } from '@/components/patients/survey-runs/columns';
import { selectSurveyColumns } from '@/components/surveys/selectSurveyColumns';
import { Alert, AlertDescription, AlertTitle } from '@/components/ui/alert';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { FormControl, FormDescription, FormField, FormItem, FormLabel, FormMessage } from '@/components/ui/form';
import { Input } from '@/components/ui/input';
import { Sheet, SheetContent, SheetDescription, SheetFooter, SheetHeader, SheetTitle, SheetTrigger } from '@/components/ui/sheet';
import AppLayout from '@/layouts/AppLayout.vue';
import PatientsLayout from '@/layouts/patients/Layout.vue';
import { BreadcrumbItem, Patient, Resource, type ResourceCollection, SharedData, type Survey, type SurveyRun } from '@/types';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { toTypedSchema } from '@vee-validate/zod';
import { LoaderCircle, TriangleAlert } from 'lucide-vue-next';
import { useForm } from 'vee-validate';
import { ref } from 'vue';
import { toast } from 'vue-sonner';
import * as z from 'zod';

const props = defineProps<{
    patient: Resource<Patient>;
    surveyRuns: ResourceCollection<SurveyRun>;
    surveys: ResourceCollection<Survey>;
}>();

const breadcrumbItems: BreadcrumbItem[] = [
    {
        title: 'Patienten',
        href: route('practices.patients.index', {
            practice: usePage<SharedData>().props.auth.practice.id,
        }),
    },
    {
        title: props.patient.data.name,
        href: route('practices.patients.show', {
            practice: usePage<SharedData>().props.auth.practice.id,
            patient: props.patient.data.id,
        }),
    },
    {
        title: 'Anamnese',
        href: route('practices.patients.survey-runs.index', {
            practice: usePage<SharedData>().props.auth.practice.id,
            patient: props.patient.data.id,
        }),
    },
];

const formSchema = toTypedSchema(
    z.object({
        surveys: z.array(z.number()).min(1, 'Bitte wählen Sie mindestens einen Anamnesebogen aus.'),
        send: z.boolean(),
    }),
);

const { handleSubmit, values } = useForm({
    validationSchema: formSchema,
    initialValues: {
        surveys: [],
        send: false,
    },
});

const loadingIndicator = ref<boolean>(false);
const onSubmit = handleSubmit((values) => {
    router.visit(
        route('practices.patients.survey-runs.store', {
            practice: usePage<SharedData>().props.auth.practice.id,
            patient: props.patient.data.id,
        }),
        {
            method: 'post',
            data: {
                surveys: values.surveys,
                send: values.send,
            },
            onStart: () => {
                loadingIndicator.value = true;
            },
            onSuccess: () => {
                toast.success('Anamnesebogen erfolgreich zugewiesen.');
            },
            onError: () => {
                toast.error('Fehler beim Zuweisen des Anamnesebogens.');
            },
        },
    );
});
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbItems">
        <Head title="Anamnese" />

        <PatientsLayout :patient="patient">
            <div class="space-y-6">
                <div class="flex justify-between">
                    <HeadingSmall title="Anamnese" description="Übersicht und Verwaltung der Anamnesedaten des Patienten." />
                    <Sheet>
                        <SheetTrigger>
                            <Button>Hinzufügen</Button>
                        </SheetTrigger>
                        <SheetContent class="min-w-xl overflow-auto">
                            <SheetHeader>
                                <SheetTitle class="px-4">Anamnesebogen zuweisen</SheetTitle>
                                <form @submit="onSubmit">
                                    <SheetDescription class="p-4">
                                        <p class="text-muted-foreground text-sm">
                                            Wählen Sie einen vorgefertigten Anamnesebogen aus der Liste aus und weisen Sie ihn dem Patienten zu.
                                            Stellen Sie sicher, dass der ausgewählte Bogen den Anforderungen entspricht.
                                        </p>
                                        <FormField v-slot="{ handleChange }" name="surveys">
                                            <FormItem>
                                                <FormControl>
                                                    <DataTable
                                                        :columns="selectSurveyColumns"
                                                        :data="surveys.data"
                                                        @update:selectedRows="(selected) => handleChange(selected.map((survey) => survey.id))"
                                                    />
                                                </FormControl>
                                            </FormItem>
                                            <FormMessage />
                                        </FormField>
                                        <FormField v-if="patient.data.email" v-slot="{ value, handleChange }" type="checkbox" name="send">
                                            <FormItem class="flex flex-row items-start space-y-0 gap-x-3 rounded-md border p-4 shadow">
                                                <FormControl>
                                                    <Checkbox :model-value="value" @update:model-value="handleChange" />
                                                </FormControl>
                                                <div class="space-y-1 leading-none">
                                                    <FormLabel>Anamnesebogen per E-Mail an den Patienten senden </FormLabel>
                                                    <FormDescription>
                                                        Wenn diese Option aktiviert ist, wird der ausgewählte Anamnesebogen nach dem Zuweisen
                                                        automatisch per E-Mail an den Patienten verschickt.
                                                    </FormDescription>
                                                    <FormMessage />
                                                </div>
                                            </FormItem>
                                        </FormField>
                                        <Alert v-else variant="destructive">
                                            <TriangleAlert class="h-4 w-4" />
                                            <AlertTitle>Keine E-Mail-Adresse hinterlegt</AlertTitle>
                                            <AlertDescription>
                                                Um den Anamnesebogen per E-Mail zu versenden, muss beim Patienten eine gültige E-Mail-Adresse
                                                hinterlegt sein.
                                                <Link
                                                    class="underline underline-offset-4"
                                                    :href="
                                                        route('practices.patients.show', {
                                                            practice: usePage<SharedData>().props.auth.practice.id,
                                                            patient: patient.data.id,
                                                        })
                                                    "
                                                >
                                                    E-Mail-Adresse hinzufügen
                                                </Link>
                                            </AlertDescription>
                                        </Alert>

                                        <Button :disabled="loadingIndicator" class="mt-4 w-full" type="submit">
                                            <template v-if="loadingIndicator">
                                                <LoaderCircle class="mr-2 h-4 w-4 animate-spin" />
                                            </template>
                                            ({{ values.surveys?.length ?? 0 }}) Anamnesebogen zuweisen
                                        </Button>
                                    </SheetDescription>
                                </form>
                            </SheetHeader>
                        </SheetContent>
                    </Sheet>
                </div>

                <div class="container mx-auto">
                    <DataTable :columns="columns" :data="surveyRuns.data.map((sr) => ({ ...sr, patientId: patient.data.id }))">
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
        </PatientsLayout>
    </AppLayout>
</template>

<style scoped></style>

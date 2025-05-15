<script setup lang="ts">
import HeadingSmall from '@/components/HeadingSmall.vue';
import { Button } from '@/components/ui/button';
import { FormControl, FormField, FormItem, FormLabel, FormMessage } from '@/components/ui/form';
import { Input } from '@/components/ui/input';
import { Select, SelectContent, SelectGroup, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Separator } from '@/components/ui/separator';
import { Textarea } from '@/components/ui/textarea';
import AppLayout from '@/layouts/AppLayout.vue';
import PatientsLayout from '@/layouts/patients/Layout.vue';
import { rules } from '@/lib/zod';
import { type BreadcrumbItem, Patient, Resource, SharedData } from '@/types';
import { vAutoAnimate } from '@formkit/auto-animate/vue';
import { Head, router, usePage } from '@inertiajs/vue3';
import { toTypedSchema } from '@vee-validate/zod';
import { useForm } from 'vee-validate';
import { ref } from 'vue';
import * as z from 'zod';
import { toast } from 'vue-sonner'

const props = defineProps<{
    patient: Resource<Patient>;
}>();

const page = usePage<SharedData>();

const breadcrumbItems: BreadcrumbItem[] = [
    {
        title: 'Patienten',
        href: route('practices.patients.index', {
            practice: usePage<SharedData>().props.auth.practice.id,
        }),
    },
    {
        title: props.patient.data.last_name + ', ' + props.patient.data.first_name,
        href: route('practices.patients.show', {
            practice: usePage<SharedData>().props.auth.practice.id,
            patient: props.patient.data.id,
        }),
    },
];

const formSchema = toTypedSchema(
    z.object({
        first_name: rules.string(),
        last_name: rules.string(),
        gender: z.string().optional(),
        birth_date: rules.date(),
        email: rules.email(),
        phone: z.string().optional(),
        address: z.string().optional(),
        postal_code: z.string().optional(),
        city: z.string().optional(),
        occupation: z.string().optional(),
        insurance_name: z.string().optional(),
        insurance_number: z.string().optional(),
        insurance_type: z.string().optional(),
        emergency_contact: z.string().optional(),
    }),
);

const form = useForm({
    validationSchema: formSchema,
    initialValues: {
        first_name: props.patient.data.first_name,
        last_name: props.patient.data.last_name,
        gender: props.patient.data.gender,
        birth_date: props.patient.data.birth_date,
        email: props.patient.data.email,
        phone: props.patient.data.phone,
        address: props.patient.data.address,
        postal_code: props.patient.data.postal_code,
        city: props.patient.data.city,
        occupation: props.patient.data.occupation,
        insurance_name: props.patient.data.insurance_name,
        insurance_number: props.patient.data.insurance_number,
        insurance_type: props.patient.data.insurance_type,
        emergency_contact: props.patient.data.emergency_contact,
    },
});

const recentlySuccessful = ref(false);
const onSubmit = form.handleSubmit((values) => {
    router.visit(
        route('practices.patients.update', {
            practice: page.props.auth.practice.id,
            patient: props.patient.data.id,
        }),
        {
            method: 'put',
            data: values,
            preserveScroll: true,
            onSuccess: () => {
                toast('Patient aktualisiert', {
                    description: 'Die Stammdaten des Patienten wurden erfolgreich aktualisiert.',
                });
            },
        },
    );
});
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbItems">
        <Head title="Patienten" />

        <PatientsLayout :patient="patient">
            <div class="space-y-6">
                <HeadingSmall title="Stammdaten" description="Bearbeiten und verwalten Sie die grundlegenden Informationen des Patienten." />

                <form @submit="onSubmit" class="space-y-4">
                    <div class="grid grid-cols-2 items-start gap-4">
                        <FormField v-slot="{ componentField }" name="first_name">
                            <FormItem>
                                <FormLabel>Vorname(n)</FormLabel>
                                <FormControl>
                                    <Input type="text" v-bind="componentField" />
                                </FormControl>
                                <FormMessage />
                            </FormItem>
                        </FormField>
                        <FormField v-slot="{ componentField }" name="last_name">
                            <FormItem>
                                <FormLabel>Nachname</FormLabel>
                                <FormControl>
                                    <Input type="text" v-bind="componentField" />
                                </FormControl>
                                <FormMessage />
                            </FormItem>
                        </FormField>
                    </div>
                    <div class="grid grid-cols-2 items-start gap-4">
                        <FormField v-slot="{ componentField }" name="birth_date">
                            <FormItem>
                                <FormLabel>Geburtsdatum</FormLabel>
                                <FormControl>
                                    <Input type="text" placeholder="tt.mm.yyyy" v-bind="componentField" />
                                </FormControl>
                                <FormMessage />
                            </FormItem>
                        </FormField>
                        <FormField v-slot="{ componentField }" name="gender">
                            <FormItem>
                                <FormLabel>Geschlecht</FormLabel>
                                <FormControl>
                                    <Select v-bind="componentField">
                                        <FormControl>
                                            <SelectTrigger>
                                                <SelectValue placeholder="Geschlecht" />
                                            </SelectTrigger>
                                        </FormControl>
                                        <SelectContent>
                                            <SelectGroup>
                                                <SelectItem value="m"> Männlich</SelectItem>
                                                <SelectItem value="w"> Weiblich</SelectItem>
                                                <SelectItem value="d"> Divers</SelectItem>
                                            </SelectGroup>
                                        </SelectContent>
                                    </Select>
                                </FormControl>
                                <FormMessage />
                            </FormItem>
                        </FormField>
                    </div>
                    <Separator />
                    <div class="grid grid-cols-2 items-start gap-4">
                        <FormField v-slot="{ componentField }" name="email">
                            <FormItem>
                                <FormLabel>Email</FormLabel>
                                <FormControl>
                                    <Input type="text" v-bind="componentField" />
                                </FormControl>
                                <FormMessage />
                            </FormItem>
                        </FormField>
                        <FormField v-slot="{ componentField }" name="phone">
                            <FormItem>
                                <FormLabel>Telefon</FormLabel>
                                <FormControl>
                                    <Input type="text" v-bind="componentField" />
                                </FormControl>
                                <FormMessage />
                            </FormItem>
                        </FormField>
                    </div>
                    <div class="grid gap-4">
                        <FormField v-slot="{ componentField }" name="address">
                            <FormItem>
                                <FormLabel>Straße & Hausnummer</FormLabel>
                                <FormControl>
                                    <Input type="text" v-bind="componentField" />
                                </FormControl>
                                <FormMessage />
                            </FormItem>
                        </FormField>
                    </div>
                    <div class="grid grid-cols-2 items-start gap-4">
                        <FormField v-slot="{ componentField }" name="postal_code">
                            <FormItem>
                                <FormLabel>Postleitzahl</FormLabel>
                                <FormControl>
                                    <Input type="text" v-bind="componentField" />
                                </FormControl>
                                <FormMessage />
                            </FormItem>
                        </FormField>
                        <FormField v-slot="{ componentField }" name="city">
                            <FormItem>
                                <FormLabel>Stadt</FormLabel>
                                <FormControl>
                                    <Input type="text" v-bind="componentField" />
                                </FormControl>
                                <FormMessage />
                            </FormItem>
                        </FormField>
                    </div>
                    <Separator />
                    <div class="grid grid-cols-2 items-start gap-4">
                        <FormField v-slot="{ componentField }" name="occupation">
                            <FormItem>
                                <FormLabel>Beruf</FormLabel>
                                <FormControl>
                                    <Input type="text" v-bind="componentField" />
                                </FormControl>
                                <FormMessage />
                            </FormItem>
                        </FormField>
                        <FormField v-slot="{ componentField }" name="insurance_type">
                            <FormItem>
                                <FormLabel>Krankenkasse</FormLabel>
                                <FormControl>
                                    <FormControl>
                                        <Select v-bind="componentField">
                                            <FormControl>
                                                <SelectTrigger>
                                                    <SelectValue placeholder="Art der Versicherung" />
                                                </SelectTrigger>
                                            </FormControl>
                                            <SelectContent>
                                                <SelectGroup>
                                                    <SelectItem value="Privat"> Privat</SelectItem>
                                                    <SelectItem value="Gesetzlich"> Gesetzlich</SelectItem>
                                                </SelectGroup>
                                            </SelectContent>
                                        </Select>
                                    </FormControl>
                                </FormControl>
                                <FormMessage />
                            </FormItem>
                        </FormField>
                    </div>
                    <div class="grid grid-cols-2 items-start gap-4">
                        <FormField v-slot="{ componentField }" name="insurance_name">
                            <FormItem>
                                <FormLabel>Krankenkasse</FormLabel>
                                <FormControl>
                                    <Input type="text" v-bind="componentField" />
                                </FormControl>
                                <FormMessage />
                            </FormItem>
                        </FormField>
                        <FormField v-slot="{ componentField }" name="insurance_number">
                            <FormItem>
                                <FormLabel>Versichertennummer</FormLabel>
                                <FormControl>
                                    <Input type="text" v-bind="componentField" />
                                </FormControl>
                                <FormMessage />
                            </FormItem>
                        </FormField>
                    </div>
                    <Separator />
                    <div class="grid gap-4">
                        <FormField v-slot="{ componentField }" name="emergency_contact">
                            <FormItem>
                                <FormLabel>Notfallkontakt</FormLabel>
                                <FormControl>
                                    <Textarea v-bind="componentField" placeholder="Notfallkontaktdaten." />
                                </FormControl>
                                <FormMessage />
                            </FormItem>
                        </FormField>
                    </div>
                    <div class="flex space-x-4 items-center">
                        <Button :disabled="!form.meta.value.dirty" type="submit">Speichern</Button>

                        <p v-auto-animate v-if="recentlySuccessful" class="text-sm text-neutral-600">Gespeichert.</p>
                    </div>
                </form>
            </div>
        </PatientsLayout>
    </AppLayout>
</template>

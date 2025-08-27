<script setup lang="ts">
import { Checkbox } from '@/components/ui/checkbox';
import { FormControl, FormField, FormItem, FormLabel, FormMessage } from '@/components/ui/form';
import { Step, StepResponse } from '@/types';
import { toTypedSchema } from '@vee-validate/zod';
import { useForm } from 'vee-validate';
import * as z from 'zod';

const props = defineProps<{
    step: Step;
}>();

const emits = defineEmits<{
    (e: 'submit', data: StepResponse): void;
}>();

const optional = props.step.options?.optional ?? false;
const formSchema = toTypedSchema(
    z.object({
        choices: optional
            ? z.array(z.string()).optional()
            : z.array(z.string(), { required_error: 'Wählen Sie bitte mindestens 1 Antwort aus.' }).min(1, {
                  message: 'Wählen Sie bitte mindestens 1 Antwort aus.',
              }),
    }),
);

const { handleSubmit } = useForm({
    validationSchema: formSchema,
    initialValues: {
        choices: [],
    },
});

const onSubmit = handleSubmit((values) => {
    const selectedChoices = values.choices?.map((choice) => props.step.choices.find((c) => c.value === choice)) ?? [];

    emits('submit', { value: selectedChoices, type: 'multiple_choice' });
});
</script>

<template>
    <form @submit="onSubmit">
        <FormField name="items">
            <FormItem>
                <FormField
                    v-for="choice in step.choices"
                    v-slot="{ value, handleChange }"
                    :key="choice.id"
                    type="checkbox"
                    :value="choice.value"
                    :unchecked-value="false"
                    name="choices"
                >
                    <FormItem class="flex flex-row items-start space-y-0 space-x-3">
                        <FormControl>
                            <Checkbox :model-value="value.includes(choice.value)" @update:model-value="handleChange" />
                        </FormControl>
                        <FormLabel class="font-normal">
                            {{ choice.label }}
                        </FormLabel>
                    </FormItem>
                </FormField>
                <FormMessage />
            </FormItem>
        </FormField>
        <slot name="actions" />
    </form>
</template>

<style scoped></style>

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

const emit = defineEmits<{
    (e: 'submit', values: StepResponse<string>): void;
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
    emit('submit', {
        step_id: props.step.id,
        value: JSON.stringify(values.choices),
    });
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
        <slot name="actions" :submit="onSubmit" />
    </form>
</template>

<style scoped></style>

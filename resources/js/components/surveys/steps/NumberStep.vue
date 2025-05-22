<script setup lang="ts">
import { toTypedSchema } from '@vee-validate/zod';
import { useForm } from 'vee-validate';
import * as z from 'zod';

import { FormControl, FormField, FormItem, FormMessage } from '@/components/ui/form';
import { NumberField, NumberFieldContent, NumberFieldDecrement, NumberFieldIncrement, NumberFieldInput } from '@/components/ui/number-field';
import { Step, StepResponse } from '@/types';

const props = defineProps<{
    step: Step;
}>();

const emit = defineEmits<{
    (e: 'submit', values: StepResponse<string>): void;
}>();

const optional = props.step.options?.optional ?? false;
const formSchema = toTypedSchema(
    z.object({
        number: optional ? z.number().optional() : z.number({ required_error: 'Dieses Feld ist erforderlich.' }),
    }),
);
const { handleSubmit, setFieldValue } = useForm({
    validationSchema: formSchema,
});

const onSubmit = handleSubmit((values) => {
    emit('submit', {
        step_id: props.step.id,
        value: values.number?.toString() ?? null,
    });
});
</script>

<template>
    <form class="space-y-6" @submit="onSubmit">
        <FormField v-slot="{ value }" name="number">
            <FormItem>
                <NumberField
                    class="gap-2"
                    :min="0"
                    :format-options="{}"
                    :model-value="value"
                    @update:model-value="
                        (v: number) => {
                            if (v) {
                                setFieldValue('number', v);
                            } else {
                                setFieldValue('number', undefined);
                            }
                        }
                    "
                >
                    <NumberFieldContent>
                        <NumberFieldDecrement />
                        <FormControl>
                            <NumberFieldInput />
                        </FormControl>
                        <NumberFieldIncrement />
                    </NumberFieldContent>
                </NumberField>
                <FormMessage />
            </FormItem>
        </FormField>
        <slot name="actions" :submit="onSubmit" />
    </form>
</template>

<style scoped></style>

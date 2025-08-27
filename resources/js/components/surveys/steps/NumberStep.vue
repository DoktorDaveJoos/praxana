<script setup lang="ts">
import { toTypedSchema } from '@vee-validate/zod';
import { useForm } from 'vee-validate';
import { computed, watch } from 'vue';
import * as z from 'zod';

import { FormControl, FormField, FormItem, FormMessage } from '@/components/ui/form';
import { NumberField, NumberFieldContent, NumberFieldDecrement, NumberFieldIncrement, NumberFieldInput } from '@/components/ui/number-field';
import { Step, StepResponse } from '@/types';

const props = defineProps<{ step: Step }>();
const emit = defineEmits<{ (e: 'submit', data: StepResponse): void }>();

// derive defaults from step
const optional = computed(() => props.step.options?.optional ?? false);
const initialNumber = computed<number | undefined>(() => {
    const raw = props.step.response?.value ?? props.step.options?.default ?? undefined;
    const n = raw !== undefined && raw !== null ? Number(raw) : undefined;
    return Number.isFinite(n as number) ? (n as number) : undefined;
});

// schema: coerce to number (inputs are strings)
const formSchema = computed(() =>
    toTypedSchema(
        z.object({
            number: optional.value ? z.coerce.number().optional() : z.coerce.number({ required_error: 'Dieses Feld ist erforderlich.' }),
        }),
    ),
);

// vee-validate: set initial value in the form state
const { handleSubmit, setFieldValue, resetForm } = useForm({
    validationSchema: formSchema,
    initialValues: { number: initialNumber.value ?? 0 },
});

// if the same component instance is reused for the next step, reset values
watch(
    () => props.step,
    () => {
        resetForm({ values: { number: initialNumber.value } });
    },
    { deep: true },
);

const onSubmit = handleSubmit((values) => {
    emit('submit', { value: values.number, type: 'number' } as StepResponse);
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
                    @update:model-value="(v: number | undefined) => setFieldValue('number', v)"
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
        <slot name="actions" />
    </form>
</template>

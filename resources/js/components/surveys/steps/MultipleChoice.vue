<script setup lang="ts">
import { toTypedSchema } from '@vee-validate/zod';
import { useForm } from 'vee-validate';
import * as z from 'zod';
import { FormControl, FormField, FormItem, FormLabel, FormMessage } from '@/components/ui/form';
import { Checkbox } from '@/components/ui/checkbox';
import { Step, StepResponse } from '@/types';

const props = defineProps<{
    step: Step;
}>();

const emit = defineEmits<{
    (e: 'submit', values: StepResponse<string>): void;
}>();

const formSchema = toTypedSchema(
    z.object({
        choices: z.array(z.string()),
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
                    :value="choice.id"
                    :unchecked-value="false"
                    name="choices"
                >
                    <FormItem class="flex flex-row items-start space-y-0 space-x-3">
                        <FormControl>
                            <Checkbox :model-value="value.includes(choice.id)" @update:model-value="handleChange" />
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

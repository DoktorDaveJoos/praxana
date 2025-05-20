<script setup lang="ts">
import { toTypedSchema } from '@vee-validate/zod';
import { useForm } from 'vee-validate';
import * as z from 'zod';

import { FormControl, FormField, FormItem, FormMessage } from '@/components/ui/form';
import { Slider } from '@/components/ui/slider';
import { Step, StepResponse } from '@/types';

const props = defineProps<{
    step: Step;
}>();

const emit = defineEmits<{
    (e: 'submit', values: StepResponse<string>): void;
}>();

const formSchema = toTypedSchema(
    z.object({
        scale: z.array(z.number()),
    }),
);

const { handleSubmit } = useForm({
    validationSchema: formSchema,
    initialValues: {
        scale: [0],
    },
});

const onSubmit = handleSubmit((values) => {
    emit('submit', {
        step_id: props.step.id,
        value: values.scale.toString()
    });
});
</script>

<template>
    <form class="w-2/3 space-y-6" @submit="onSubmit">
        <FormField v-slot="{ componentField }" name="scale">
            <FormItem>
                <FormControl>
                    <Slider
                        :model-value="componentField.modelValue"
                        :default-value="[0]"
                        :max="100"
                        :min="0"
                        :step="5"
                        :name="componentField.name"
                        @update:model-value="componentField['onUpdate:modelValue']"
                    />
                </FormControl>
                <FormMessage />
            </FormItem>
        </FormField>
        <slot name="actions" :submit="onSubmit" />
    </form>
</template>

<style scoped></style>

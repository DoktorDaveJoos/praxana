<script setup lang="ts">
import { toTypedSchema } from '@vee-validate/zod';
import { useForm } from 'vee-validate';
import * as z from 'zod';

import { FormControl, FormField, FormItem, FormMessage } from '@/components/ui/form';
import { Slider } from '@/components/ui/slider';
import { useStepNavigation } from '@/composables/useStepNavigation';
import { Step, StepResponse } from '@/types';

const props = defineProps<{
    step: Step;
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

const { handleStepSubmit, getNextStepId } = useStepNavigation();
const onSubmit = handleSubmit((values) => {
    handleStepSubmit({
        step_id: props.step.id,
        value: JSON.stringify(values.scale),
        next_step_id: getNextStepId(props.step),
        type: 'number',
    } as StepResponse<number[]>);
});
</script>

<template>
    <form class="w-full space-y-6" @submit="onSubmit">
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

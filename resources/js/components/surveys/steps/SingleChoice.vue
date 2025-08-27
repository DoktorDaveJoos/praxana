<script setup lang="ts">
import { toTypedSchema } from '@vee-validate/zod';
import { useForm } from 'vee-validate';
import * as z from 'zod';

import { FormControl, FormField, FormItem, FormLabel, FormMessage } from '@/components/ui/form';
import { RadioGroup, RadioGroupItem } from '@/components/ui/radio-group';
import { Choice, Step, StepResponse } from '@/types';

const props = defineProps<{
    step: Step;
}>();

const emit = defineEmits<{
    (e: 'submit', data: StepResponse): void;
}>();

const formSchema = toTypedSchema(
    z.object({
        choice: z.string(),
    }),
);

const { handleSubmit } = useForm({
    validationSchema: formSchema,
    initialValues: {
        choice:
            ((props.step.response as Extract<StepResponse, { type: 'single_choice' }> | undefined)
                ?.value as Choice | undefined)?.id?.toString() ?? '',
    },
});

const onSubmit = handleSubmit((values) => {
    const selectedChoice = props.step.choices?.find((c) => c.id.toString() === values.choice);

    emit('submit', { value: selectedChoice, type: 'single_choice' });
});
</script>

<template>
    <form class="w-full space-y-6" @submit="onSubmit">
        <FormField v-slot="{ componentField }" type="radio" name="choice">
            <FormItem class="space-y-3">
                <FormControl>
                    <RadioGroup class="flex flex-col space-y-1" v-bind="componentField">
                        <FormItem v-for="choice in step.choices" :key="choice.id" class="flex items-center space-y-0 gap-x-3">
                            <FormControl>
                                <RadioGroupItem :value="choice.id.toString()" />
                            </FormControl>
                            <FormLabel class="font-normal">
                                {{ choice.label }}
                            </FormLabel>
                        </FormItem>
                    </RadioGroup>
                </FormControl>
                <FormMessage />
            </FormItem>
        </FormField>
        <slot name="actions" />
    </form>
</template>

<style scoped></style>

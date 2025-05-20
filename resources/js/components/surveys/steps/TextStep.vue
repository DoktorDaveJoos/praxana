<script setup lang="ts">
import { toTypedSchema } from '@vee-validate/zod';
import { useForm } from 'vee-validate';
import * as z from 'zod';

import { FormControl, FormField, FormItem, FormMessage } from '@/components/ui/form';
import { Textarea } from '@/components/ui/textarea';
import { Step, StepResponse } from '@/types';

const props = defineProps<{
    step: Step;
}>();

const emit = defineEmits<{
    (e: 'submit', values: StepResponse<string>): void;
}>();

const formSchema = toTypedSchema(
    z.object({
        text: z.string(),
    }),
);

const { handleSubmit } = useForm({
    validationSchema: formSchema,
});

const onSubmit = handleSubmit((values) => {
    emit('submit', {
        step_id: props.step.id,
        value: values.text
    });
});
</script>

<template>
    <form class="space-y-6" @submit="onSubmit">
        <FormField v-slot="{ componentField }" type="radio" name="text">
            <FormItem class="space-y-3">
                <FormControl>
                    <FormItem class="flex items-center space-y-0 gap-x-3">
                        <Textarea placeholder="" class="resize-none" v-bind="componentField" />
                    </FormItem>
                </FormControl>
                <FormMessage />
            </FormItem>
        </FormField>
        <slot name="actions" :submit="onSubmit" />
    </form>
</template>

<style scoped></style>

<script setup lang="ts">
import { toTypedSchema } from '@vee-validate/zod';
import { useForm } from 'vee-validate';
import * as z from 'zod';

import { FormControl, FormField, FormItem, FormLabel, FormMessage } from '@/components/ui/form';
import { RadioGroup, RadioGroupItem } from '@/components/ui/radio-group';
import { Step, StepResponse } from '@/types';

const props = defineProps<{
    step: Step;
}>();

const emit = defineEmits<{
    (e: 'submit', values: StepResponse<string>): void;
}>();

const formSchema = toTypedSchema(z.object({
    choice: z.string(),
}))

const { handleSubmit } = useForm({
    validationSchema: formSchema,
})

const onSubmit = handleSubmit((values) => {
    emit('submit', {
        step_id: props.step.id,
        value: values.choice
    });
});
</script>

<template>
    <form class="w-2/3 space-y-6" @submit="onSubmit">
        <FormField v-slot="{ componentField }" type="radio" name="choice">
            <FormItem class="space-y-3">
                <FormControl>
                    <RadioGroup
                        class="flex flex-col space-y-1"
                        v-bind="componentField"
                    >
                        <FormItem v-for="choice in step.choices" v-bind:key="choice.id" class="flex items-center space-y-0 gap-x-3">
                            <FormControl>
                                <RadioGroupItem :value="choice.value" />
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
        <slot name="actions" :submit="onSubmit" />
    </form>
</template>

<style scoped></style>

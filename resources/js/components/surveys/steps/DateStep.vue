<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { FormControl, FormField, FormItem, FormMessage } from '@/components/ui/form';
import { Popover, PopoverContent, PopoverTrigger } from '@/components/ui/popover';
import { Step, StepResponse } from '@/types';
import { cn } from '@/lib/utils';
import { DateFormatter, parseDate } from '@internationalized/date';
import { toTypedSchema } from '@vee-validate/zod';
import { CalendarIcon } from 'lucide-vue-next';
import { toDate } from 'reka-ui/date';
import { useForm } from 'vee-validate';
import { computed, ref } from 'vue';
import { z } from 'zod';
import CustomDatePicker from '@/components/CustomDatePicker.vue';

const props = defineProps<{
    step: Step;
}>();

const emit = defineEmits<{
    (e: 'submit', values: StepResponse<string>): void;
}>();

const df = new DateFormatter('de-DE', {
    dateStyle: 'long',
});

const formSchema = toTypedSchema(
    z.object({
        date: z.string(),
    }),
);

const placeholder = ref();

const { handleSubmit, setFieldValue, values } = useForm({
    validationSchema: formSchema,
    initialValues: {},
});

const value = computed({
    get: () => (values.date ? parseDate(values.date) : undefined),
    set: (val) => val,
});

const onSubmit = handleSubmit((values) => {
    emit('submit', {
        step_id: props.step.id,
        value: values.date
    });
});
</script>

<template>
    <form class="space-y-8" @submit="onSubmit">
        <FormField name="date">
            <FormItem class="flex flex-col">
                <Popover>
                    <PopoverTrigger as-child>
                        <FormControl>
                            <Button variant="outline" :class="cn('w-[240px] ps-3 text-start font-normal', !value && 'text-muted-foreground')">
                                <span>{{ value ? df.format(toDate(value)) : 'Datum auswählen' }}</span>
                                <CalendarIcon class="ms-auto h-4 w-4 opacity-50" />
                            </Button>
                            <input hidden />
                        </FormControl>
                    </PopoverTrigger>
                    <PopoverContent class="w-auto p-0">
                        <CustomDatePicker
                            v-model:placeholder="placeholder"
                            v-model="value"
                            calendar-label="Datum"
                            initial-focus
                            :min-value="step.options?.min"
                            :max-value="step.options?.max"
                            @update:model-value="
                                (v) => {
                                    if (v) {
                                        setFieldValue('date', v.toString());
                                    } else {
                                        setFieldValue('date', undefined);
                                    }
                                }
                            "
                        />
                    </PopoverContent>
                </Popover>
                <FormMessage />
            </FormItem>
        </FormField>
        <slot name="actions" :submit="onSubmit" />
    </form>
</template>

<style scoped></style>

<script setup lang="ts">
import CustomDatePicker from '@/components/CustomDatePicker.vue';
import { Button } from '@/components/ui/button';
import { FormControl, FormField, FormItem, FormMessage } from '@/components/ui/form';
import { Popover, PopoverContent, PopoverTrigger } from '@/components/ui/popover';
import { cn } from '@/lib/utils';
import { Step } from '@/types';
import { DateFormatter, parseDate } from '@internationalized/date';
import { toTypedSchema } from '@vee-validate/zod';
import { CalendarIcon } from 'lucide-vue-next';
import { toDate } from 'reka-ui/date';
import { useForm } from 'vee-validate';
import { computed, ref } from 'vue';
import { z } from 'zod';

const props = defineProps<{
    step: Step;
}>();

const df = new DateFormatter('de-DE', {
    dateStyle: 'long',
});

const optional = props.step.options?.optional ?? false;
const formSchema = toTypedSchema(
    z.object({
        value: optional
            ? z.string().optional()
            : z.string({
                  required_error: 'Dieses Feld ist erforderlich.',
              }),
    }),
);

const placeholder = ref();

const { handleSubmit, setFieldValue, values } = useForm({
    validationSchema: formSchema,
    initialValues: {},
});

const value = computed({
    get: () => (values.value ? parseDate(values.value) : undefined),
    set: (val) => val,
});



</script>

<template>
    <form class="space-y-8" @submit="handleSubmit">
        <FormField name="value">
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
                                        setFieldValue('value', v.toString());
                                    } else {
                                        setFieldValue('value', undefined);
                                    }
                                }
                            "
                        />
                    </PopoverContent>
                </Popover>
                <FormMessage />
            </FormItem>
        </FormField>
        <slot name="actions" :submit="handleSubmit" />
    </form>
</template>

<style scoped></style>

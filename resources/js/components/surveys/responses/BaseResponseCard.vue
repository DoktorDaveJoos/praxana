<script setup lang="ts">
import { Badge } from '@/components/ui/badge';
import type { Response, Choice } from '@/types';

const props = defineProps<{ response: Response; index: number }>();

const isChoice = (v: unknown): v is Choice =>
    !!v && typeof v === 'object' && 'label' in (v as any) && 'value' in (v as any);

const isChoiceArray = (v: unknown): v is Choice[] =>
    Array.isArray(v) && v.every(isChoice);

// return string or array of strings
const displayValue = (r: Response): string | string[] => {
    const v = r.value as unknown;
    if (isChoiceArray(v)) return v.map(c => c.label);
    if (isChoice(v)) return v.label;
    return String(v);
};
</script>

<template>
    <div class="flex items-start">
        <div class="min-w-12">
            <Badge variant="secondary" class="font-mono text-xs">{{ index + 1 }}</Badge>
        </div>

        <div class="flex flex-col space-y-1">
            <p class="text-sm leading-none font-medium">{{ props.response.question }}</p>

            <!-- if array → bullet list -->
            <ul v-if="Array.isArray(displayValue(props.response))" class="list-disc list-inside text-muted-foreground text-sm">
                <li v-for="(item, i) in displayValue(props.response)" :key="i">{{ item }}</li>
            </ul>

            <!-- if single value → plain text -->
            <p v-else class="text-muted-foreground text-sm">{{ displayValue(props.response) }}</p>
        </div>
    </div>
</template>

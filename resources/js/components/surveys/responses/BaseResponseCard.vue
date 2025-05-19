<script setup lang="ts">
import { Badge } from '@/components/ui/badge';
import { Response } from '@/types';

defineProps<{
    response: Response;
    index: number;
}>();
</script>

<template>
    <div class="flex items-start">
        <div class="min-w-12">
            <Badge variant="secondary" class="font-mono text-xs">{{ index + 1 }}</Badge>
        </div>

        <div class="flex flex-col space-y-1">
            <p class="text-sm leading-none font-medium">{{ response.question }}</p>
            <p class="text-muted-foreground text-sm">{{ response.value }}</p>
        </div>
    </div>
</template>

<style scoped></style>

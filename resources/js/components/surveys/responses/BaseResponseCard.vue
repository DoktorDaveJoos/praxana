<script setup lang="ts">
import { Response } from '@/types';

defineProps<{
    response: Response;
}>();
</script>

<template>
    <div class="flex flex-col">
        <p class="text-sm leading-none font-medium">{{ response.question }}</p>
        <p class="text-muted-foreground text-sm">{{ response.value }}</p>
    </div>
</template>

<style scoped></style>

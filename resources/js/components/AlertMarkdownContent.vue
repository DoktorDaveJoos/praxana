<script setup lang="ts">
import { computed } from 'vue';
import { marked } from 'marked';
import { cn } from '@/lib/utils';

const props = defineProps<{
    content: string;
}>();

const markdownClass = computed(() =>
    cn(
        // Base styles
        'grid',
        'justify-items-start',
        'gap-1',
        // Paragraphs
        'text-primary',
        'text-xs',
        '[&_p]:leading-relaxed',
        // Headings
        '[&_h1]:font-semibold',
        '[&_h1]:text-sm',
        '[&_h2]:font-semibold',
        '[&_h2]:text-sm',
        '[&_h3]:font-semibold',
        '[&_h3]:text-xs',
        // Lists
        '[&_ul]:list-disc',
        '[&_ul]:pl-2',
        '[&_ol]:list-decimal',
        '[&_ol]:pl-2',
        '[&_li]:leading-relaxed',
        // Links
        '[&_a]:underline',
        '[&_a]:text-primary',
        '[&_a]:hover:text-primary/80',
        // Code blocks
        '[&_pre]:bg-secondary',
        '[&_pre]:rounded',
        '[&_pre]:p-2',
        '[&_pre]:text-xs',
        '[&_pre]:text-primary',
    )
);

// leave marked at its defaults (it already handles headers, links, lists, etc.)
const html = computed(() => marked.parse(props.content));
</script>

<template>
    <div
        :class="markdownClass"
        v-html="html"
    />
</template>

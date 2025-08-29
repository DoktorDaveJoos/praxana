<script setup lang="ts">
import { Alert, AlertDescription, AlertTitle } from '@/components/ui/alert';
import { Button } from '@/components/ui/button';
import { cn } from '@/lib/utils';
import CodeBlockLowlight from '@tiptap/extension-code-block-lowlight';
import Document from '@tiptap/extension-document';
import Text from '@tiptap/extension-text';
import { EditorContent, useEditor } from '@tiptap/vue-3';
import json from 'highlight.js/lib/languages/json';
import yaml from 'highlight.js/lib/languages/yaml';
import { common, createLowlight } from 'lowlight';
import { AlertCircle, ListChecks, Trash, WrapText } from 'lucide-vue-next';
import { computed, HTMLAttributes, onBeforeUnmount, ref } from 'vue';

// bind object
const props = defineProps<{
    class?: HTMLAttributes['class'];
    defaultValue?: string | number;
    modelValue?: string | number;
    placeholder?: string;
}>();

const emit = defineEmits<{
    'update:modelValue': [value: string];
}>();

const errors = ref<string[]>([]);

// initial content (stringify numbers just in case)
const initial = computed(() => (props.modelValue ?? props.defaultValue ?? '').toString());

const lowlight = createLowlight(common);
lowlight.register('yaml', yaml);
lowlight.register('json', json);

// create editor
const editor = useEditor({
    content: {
        type: 'doc',
        content: [
            {
                type: 'codeBlock',
                attrs: { language: 'json' },
                content: [{ type: 'text', text: initial.value }],
            },
        ],
    },
    extensions: [
        Document,
        Text,
        CodeBlockLowlight.configure({
            lowlight,
        }),
    ],
    onUpdate({ editor }) {
        emit('update:modelValue', editor.getText());
    },
    editorProps: {
        attributes: {
            class: cn(
                'tiptap min-h-64 overflow-auto rounded-md border bg-transparent px-3 py-2 font-mono text-base antialiased shadow-xs outline-none disabled:cursor-not-allowed disabled:opacity-50',
                'border-input text-foreground placeholder:text-muted-foreground',
                'focus-visible:ring-ring/50 focus-visible:border-ring focus-visible:ring-[3px]',
                'aria-invalid:border-destructive aria-invalid:ring-destructive/20 dark:aria-invalid:ring-destructive/40',
                'md:text-sm',
                props.class,
            ),
        },
    },
});

onBeforeUnmount(() => {
    editor?.value?.destroy();
});

const reformat = () => {
    editor.value?.commands.setContent({
        type: 'doc',
        content: [
            {
                type: 'codeBlock',
                attrs: { language: 'json' },
                content: [{ type: 'text', text: JSON.stringify(JSON.parse(editor.value?.getText()), null, 2) }],
            },
        ],
    });
};
</script>

<template>
    <Alert v-if="errors.length > 0" variant="destructive" class="mb-4">
        <AlertCircle class="h-4 w-4" />
        <AlertTitle>Error</AlertTitle>
        <AlertDescription>
            {{ errors.join(', ') }}
        </AlertDescription>
    </Alert>

    <div v-if="editor" class="mb-4 flex">
        <Button variant="ghost" @click="reformat">
            <WrapText class="h-4 w-4" />
        </Button>
        <Button variant="ghost">
            <ListChecks class="h-4 w-4" />
        </Button>
        <Button variant="ghost">
            <Trash class="h-4 w-4" />
        </Button>
    </div>

    <EditorContent v-if="editor" :editor="editor" />
</template>

<style scoped></style>

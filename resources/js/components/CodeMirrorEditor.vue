<script setup lang="ts">
import { Alert, AlertDescription, AlertTitle } from '@/components/ui/alert';
import { Button } from '@/components/ui/button';
import { json } from '@codemirror/lang-json';
import { javascript } from '@codemirror/lang-javascript';
import { basicSetup, EditorView } from 'codemirror';
import { AlertCircle, ListChecks, Trash, WrapText } from 'lucide-vue-next';
import { computed, HTMLAttributes, onMounted, ref } from 'vue';

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


const cmTheme = EditorView.theme(
    {
        /* root editor */
        '&': {
            /* layout + box */
            minHeight: '16rem',              /* min-h-64 */
            backgroundColor: 'transparent',  /* bg-transparent */
            border: '1px solid hsl(var(--input))', /* border-input */
            borderRadius: '0.375rem',        /* rounded-md */
            boxShadow: '0 1px 1px 0 rgb(0 0 0 / 0.05)', /* shadow-xs */
            outline: 'none',
            /* text */
            color: 'hsl(var(--foreground))', /* text-foreground */
            fontFamily: 'var(--font-mono), monospace', /* font-mono */
            fontSize: '.9rem',                /* text-base (Tailwind base) */
            lineHeight: '1.5',
            WebkitFontSmoothing: 'antialiased',
            MozOsxFontSmoothing: 'grayscale',
        },

        /* content padding (px-3 py-2) */
        '.cm-content': {
            padding: '0.5rem 0.75rem', /* py-2 px-3 */
            caretColor: 'currentColor',
        },

        /* make the scroll area behave like overflow-auto on the wrapper */
        '.cm-scroller': {
            overflow: 'auto',
        },

        /* placeholder color (via @codemirror/view placeholder extension) */
        '.cm-placeholder': {
            color: 'hsl(var(--muted-foreground))', /* placeholder:text-muted-foreground */
            pointerEvents: 'none',
        },

        /* selection (optional but nice) */
        '&.cm-editor .cm-selectionBackground, .cm-selectionMatch': {
            backgroundColor: 'hsl(var(--ring) / 0.15)',
        },

        /* focus-visible ring + border */
        '&:focus-within': {
            borderColor: 'hsl(var(--ring))',          /* focus-visible:border-ring */
            boxShadow: '0 0 0 3px hsl(var(--ring) / 0.5)', /* focus-visible:ring-[3px] ring-ring/50 */
        },

        /* aria-invalid (error) */
        '&[aria-invalid="true"]': {
            borderColor: 'hsl(var(--destructive))',             /* aria-invalid:border-destructive */
            boxShadow: '0 0 0 3px hsl(var(--destructive) / 0.2)', /* aria-invalid:ring-destructive/20 */
        },
        /* optional darker ring for dark mode error (matches your comment) */
        '.dark & [aria-invalid="true"]': {
            boxShadow: '0 0 0 3px hsl(var(--destructive) / 0.4)', /* dark:aria-invalid:ring-destructive/40 */
        },

        /* disabled / readOnly look */
        '&.cm-readOnly, &[aria-disabled="true"]': {
            cursor: 'not-allowed',              /* disabled:cursor-not-allowed */
            opacity: 0.5,                       /* disabled:opacity-50 */
        },
        '&.cm-readOnly .cm-content': {
            userSelect: 'none',
        },

        /* gutters to match transparent bg */
        '.cm-gutters': {
            backgroundColor: 'transparent',
            borderRight: 'none',
            color: 'hsl(var(--muted-foreground))',
        },

        /* hide default outline artifacts */
        '.cm-focused': {
            outline: 'none',
        },
    },
    {
        /* Let it apply for both light/dark — colors come from CSS vars (shadcn/ui) */
        dark: false,
    }
);

const editorEl = ref<HTMLDivElement | null>(null);
let view: EditorView | null = null;

const initial = computed(() => (props.modelValue ?? props.defaultValue ?? '').toString());

onMounted(() => {
    if (editorEl.value) {
        view = new EditorView({
            parent: editorEl.value,
            doc: initial.value,
            extensions: [basicSetup, javascript(), cmTheme],
        });
    }
});
</script>

<template>
    <Alert v-if="errors.length > 0" variant="destructive" class="mb-4">
        <AlertCircle class="h-4 w-4" />
        <AlertTitle>Error</AlertTitle>
        <AlertDescription>
            {{ errors.join(', ') }}
        </AlertDescription>
    </Alert>

    <div class="mb-4 flex">
        <Button variant="ghost">
            <WrapText class="h-4 w-4" />
        </Button>
        <Button variant="ghost">
            <ListChecks class="h-4 w-4" />
        </Button>
        <Button variant="ghost">
            <Trash class="h-4 w-4" />
        </Button>
    </div>
    <div
        ref="editorEl"
        class=""
    />
</template>

<style scoped></style>

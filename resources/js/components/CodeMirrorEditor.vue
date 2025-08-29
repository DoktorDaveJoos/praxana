<script setup lang="ts">
import { Alert, AlertDescription, AlertTitle } from '@/components/ui/alert';
import { Button } from '@/components/ui/button';
import { json } from '@codemirror/lang-json';
import { javascript } from '@codemirror/lang-javascript';
import {
    EditorView,
    keymap,
    drawSelection,
    dropCursor,
    highlightSpecialChars,
    rectangularSelection,
    crosshairCursor,
    lineNumbers,
} from '@codemirror/view';
import { indentOnInput, bracketMatching, syntaxHighlighting, defaultHighlightStyle } from '@codemirror/language';
import { history, defaultKeymap, historyKeymap, indentWithTab } from '@codemirror/commands';
import { closeBrackets, closeBracketsKeymap, autocompletion, completionKeymap } from '@codemirror/autocomplete';
import { computed, HTMLAttributes, onMounted, ref, watch } from 'vue';
import { ListChecks, Trash, WrapText, AlertCircle, Bone, Copy, CopyCheck } from 'lucide-vue-next';
import Heading from '@/components/Heading.vue';
import { useClipboard } from '@vueuse/core'

const props = defineProps<{
    class?: HTMLAttributes['class'];
    defaultValue?: string | number;
    modelValue?: string | number;
    placeholder?: string;
    strictJson?: boolean; // set true if you want *strict* JSON (no comments)
}>();

const emit = defineEmits<{ 'update:modelValue': [value?: string] }>();
const errors = ref<string[]>([]);
const { copy, copied } = useClipboard()

const example = {
    "survey": {
        "name": "Anamnese – Check-up",
        "description": "Kurze medizinische Anamnese vor dem Termin.",
        "version": 1,
        "steps": [
            {
                "order": 1,
                "title": "Willkommen",
                "content": "Diese kurze Anamnese hilft uns, Ihren Gesundheitszustand besser einzuschätzen. Bitte beantworten Sie die folgenden Fragen ehrlich. Ihre Angaben bleiben vertraulich.",
                "step_type": "dialog",
            },
            {
                "order": 2,
                "title": "Aktuelle Beschwerden",
                "content": "Haben Sie derzeit Beschwerden? (Mehrfachauswahl möglich)",
                "step_type": "question",
                "question_type": "multiple_choice",
                "choices": [
                    { "label": "Keine Beschwerden", "value": "none", "optional_next_step": 4, "order": 1 },
                    { "label": "Schmerzen", "value": "pain", "optional_next_step": null, "order": 2 },
                    { "label": "Fieber", "value": "fever", "optional_next_step": null, "order": 3 },
                    { "label": "Husten", "value": "cough", "optional_next_step": null, "order": 4 },
                    { "label": "Atemnot", "value": "dyspnea", "optional_next_step": null, "order": 5 }
                ]
            },
            {
                "order": 3,
                "title": "Medikamente",
                "content": "Welche Medikamente nehmen Sie regelmäßig? (Wirkstoff, Dosierung, Häufigkeit)",
                "step_type": "question",
                "question_type": "text",
                "options": {
                    "placeholder": "z. B. Metformin 500 mg, 1–0–1",
                    "max_length": 500,
                    "optional": true
                }
            },
            {
                "order": 4,
                "title": "Letzte Vorsorgeuntersuchung",
                "content": "Wann war Ihre letzte Vorsorge- bzw. Gesundheitsuntersuchung?",
                "step_type": "question",
                "question_type": "date",
                "options": {
                    "min": "1900-01-01",
                    "max": "today",
                    "optional": true
                }
            }
        ]
    }
}

const cmTheme = EditorView.theme({
    '&': {
        minHeight: '16rem',
        backgroundColor: 'transparent',
        border: '1px solid hsl(var(--input))',
        borderRadius: '0.375rem',
        boxShadow: '0 1px 1px 0 rgb(0 0 0 / 0.05)',
        outline: 'none',
        color: 'hsl(var(--foreground))',
        fontFamily: 'var(--font-mono), monospace',
        fontSize: '.9rem',
        lineHeight: '1.5',
        WebkitFontSmoothing: 'antialiased',
        MozOsxFontSmoothing: 'grayscale',
        width: '100%',
        maxWidth: '100%',
    },
    '.cm-content': {
        padding: '0.5rem 0.75rem',
        caretColor: 'currentColor',
    },
    '.cm-scroller': { overflow: 'auto' },
    '.cm-placeholder': {
        color: 'hsl(var(--muted-foreground))',
        pointerEvents: 'none',
    },
    // ensure there’s no “blue bar” active-line highlight
    '.cm-activeLine': { backgroundColor: 'transparent' },

    '&:focus-within': {
        borderColor: 'hsl(var(--ring))',
        boxShadow: '0 0 0 3px hsl(var(--ring) / 0.5)',
    },
    '&[aria-invalid="true"]': {
        borderColor: 'hsl(var(--destructive))',
        boxShadow: '0 0 0 3px hsl(var(--destructive) / 0.2)',
    },
    '.cm-gutters': {
        display: 'block',
        //  display: 'none' // hide line numbers to match the top block
    },
    '.cm-focused': { outline: 'none' },
});

const editorEl = ref<HTMLDivElement | null>(null);
let view: EditorView | null = null;

const initial = computed(() => (props.modelValue ?? props.defaultValue ?? '').toString());

onMounted(() => {
    if (!editorEl.value) return;

    const language = props.strictJson ? json() : javascript(); // JSON with comments by default

    view = new EditorView({
        parent: editorEl.value,
        doc: initial.value,
        extensions: [
            // minimal, hand-picked setup (no line numbers, no active-line)
            highlightSpecialChars(),
            history(),
            drawSelection(),
            dropCursor(),
            rectangularSelection(),
            crosshairCursor(),
            indentOnInput(),
            bracketMatching(),
            closeBrackets(),
            autocompletion(),
            lineNumbers(),
            syntaxHighlighting(defaultHighlightStyle),
            keymap.of([...defaultKeymap, ...historyKeymap, ...closeBracketsKeymap, ...completionKeymap, indentWithTab]),
            language,
            cmTheme,
            EditorView.lineWrapping,
            EditorView.updateListener.of((v) => {
                if (v.docChanged) emit('update:modelValue', v.state.doc.toString());
            }),
        ],
    });
});

// keep external v-model in sync
watch(
    () => props.modelValue,
    (val) => {
        if (!view) return;
        const text = (val ?? '').toString();
        if (text !== view.state.doc.toString()) {
            view.dispatch({
                changes: { from: 0, to: view.state.doc.length, insert: text },
            });
        }
    },
);

const reformat = () => {
    emit('update:modelValue', JSON.stringify(JSON.parse(view?.state?.doc?.toString() ?? ''), null, 2));
};

const clear = () => {
    emit('update:modelValue', undefined);
}

const skeleton = () => {
    emit('update:modelValue', JSON.stringify(example, null, 2))
}

const toClipboard = () => {
    if (! view?.state?.doc) return;

    copy(view?.state?.doc?.toString())
}
</script>

<template>
    <Alert v-if="errors.length" variant="destructive" class="mb-4">
        <AlertCircle class="h-4 w-4" />
        <AlertTitle>Error</AlertTitle>
        <AlertDescription>{{ errors.join(', ') }}</AlertDescription>
    </Alert>

    <div class="mb-2 flex justify-between">
        <Heading title="Fragebogen erstellen" description="Erstelle einen neuen Fragebogen mit einem JSON-Schema und einer Reihe von Fragen." />

        <div class="flex items-end">
            <Button variant="ghost" @click="reformat">
                <WrapText class="h-4 w-4" />
            </Button>
            <Button variant="ghost">
                <ListChecks class="h-4 w-4" />
            </Button>
            <Button variant="ghost" @click="skeleton">
                <Bone class="h-4 w-4" />
            </Button>
            <Button variant="ghost" @click="toClipboard">
                <Copy v-if="!copied" class="h-4 w-4" />
                <CopyCheck v-else class="h-4 w-4" />
            </Button>
            <Button variant="ghost" @click="clear">
                <Trash class="h-4 w-4" />
            </Button>
        </div>
    </div>

    <div ref="editorEl" class="min-h-64 overflow-auto rounded-md border bg-transparent font-mono text-base antialiased shadow-xs outline-none" />
</template>

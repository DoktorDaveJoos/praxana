<script setup lang="ts">
import AddQuestionDialog from '@/components/AddQuestionDialog.vue';
import Heading from '@/components/Heading.vue';
import { Button } from '@/components/ui/button';
import { Separator } from '@/components/ui/separator';
import { useSurveySchemaValidity } from '@/composables/useSurveySchemaValidity';
import { createSurveySchemaLinter } from '@/lib/codemirror-survey-schema-linter';
import { autocompletion, closeBrackets, closeBracketsKeymap, completionKeymap } from '@codemirror/autocomplete';
import { defaultKeymap, history, historyKeymap, indentWithTab } from '@codemirror/commands';
import { javascript } from '@codemirror/lang-javascript';
import { json } from '@codemirror/lang-json';
import { bracketMatching, defaultHighlightStyle, indentOnInput, syntaxHighlighting } from '@codemirror/language';
import {
    crosshairCursor,
    drawSelection,
    dropCursor,
    EditorView,
    highlightSpecialChars,
    keymap,
    lineNumbers,
    rectangularSelection,
} from '@codemirror/view';
import { useClipboard } from '@vueuse/core';
import { Bone, Check, CircleX, Copy, CopyCheck, Trash, Upload, WrapText } from 'lucide-vue-next';
import { computed, HTMLAttributes, onMounted, ref, watch } from 'vue';

const {
    isValid,
    extension: validityExtension,
    validateNow,
} = useSurveySchemaValidity({
    collectErrors: true,
    debounceMs: 80,
});

const props = defineProps<{
    class?: HTMLAttributes['class'];
    defaultValue?: string | number;
    modelValue?: string | number;
    placeholder?: string;
    strictJson?: boolean; // set true if you want *strict* JSON (no comments)
}>();

const emit = defineEmits<{
    'update:modelValue': [value?: string];
    upload: [];
}>();
const { copy, copied } = useClipboard();

const example = {
    survey: {
        name: 'Medizinischer Check-up – Anamnese',
        description: 'Kurze medizinische Anamnese vor dem Termin.',
        version: 1,
        is_active: true,
        steps: [
            {
                order: 1,
                title: 'Willkommen',
                content:
                    'Diese kurze Anamnese hilft uns, Ihren Gesundheitszustand besser einzuschätzen. Bitte beantworten Sie die Fragen ehrlich. Ihre Angaben bleiben vertraulich.',
                step_type: 'info',
            },
            {
                order: 2,
                title: 'Aktuelle Beschwerden',
                content: 'Haben Sie derzeit Beschwerden? (Mehrfachauswahl möglich)',
                step_type: 'question',
                question_type: 'multiple_choice',
                options: {
                    min_choices: 1,
                    max_choices: 5,
                    optional: false,
                },
                choices: [
                    { label: 'Keine Beschwerden', value: 'none', next_step: 4, order: 1 },
                    { label: 'Schmerzen', value: 'pain', next_step: null, order: 2 },
                    { label: 'Fieber', value: 'fever', next_step: null, order: 3 },
                    { label: 'Husten', value: 'cough', next_step: null, order: 4 },
                    { label: 'Atemnot', value: 'dyspnea', next_step: null, order: 5 },
                ],
            },
            {
                order: 3,
                title: 'Medikamente',
                content: 'Welche Medikamente nehmen Sie regelmäßig? (Wirkstoff, Dosierung, Häufigkeit)',
                step_type: 'question',
                question_type: 'text',
                options: {
                    placeholder: 'z. B. Metformin 500 mg, 1–0–1',
                    max_length: 500,
                    optional: true,
                },
            },
            {
                order: 4,
                title: 'Letzte Vorsorgeuntersuchung',
                content: 'Wann war Ihre letzte Vorsorge- oder Gesundheitsuntersuchung?',
                step_type: 'question',
                question_type: 'date',
                options: {
                    min: '1900-01-01',
                    max: 'today',
                    format: 'YYYY-MM-DD',
                    optional: true,
                },
            },
            {
                order: 5,
                title: 'Kennen wir uns?',
                content: 'Waren Sie bereits bei uns?',
                step_type: 'question',
                question_type: 'single_choice',
                choices: [
                    { label: 'Ja', value: 'true', order: 1 },
                    { label: 'Nein', value: 'false', order: 2 },
                ],
            },
        ],
    },
};

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
            createSurveySchemaLinter(),
            syntaxHighlighting(defaultHighlightStyle),
            keymap.of([...defaultKeymap, ...historyKeymap, ...closeBracketsKeymap, ...completionKeymap, indentWithTab]),
            language,
            cmTheme,
            validityExtension,
            EditorView.lineWrapping,
            EditorView.updateListener.of((v) => {
                if (v.docChanged) emit('update:modelValue', v.state.doc.toString());
            }),
        ],
    });

    validateNow(initial.value);
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
};

const skeleton = () => {
    emit('update:modelValue', JSON.stringify(example, null, 2));
};

const toClipboard = () => {
    if (!view?.state?.doc) return;

    copy(view?.state?.doc?.toString());
};

const upload = () => {
    emit('upload');
};
</script>

<template>
    <div class="mb-2 flex flex-col">
        <Heading title="Fragebogen erstellen" description="Erstelle einen neuen Fragebogen mit einem JSON-Schema und einer Reihe von Fragen." />

        <div class="mb-1 flex h-5 w-full items-center space-x-2 self-end">
            <div class="flex w-full items-center justify-between">
                <span v-if="isValid" class="text-muted-foreground text-sm">Alles in Ordnung</span>
                <span v-else class="text-sm text-red-600">Bitte Fehler korrigieren</span>

                <Check v-if="isValid" class="mr-4 h-4 w-4 text-teal-600" />
                <CircleX v-else class="mr-4 h-4 w-4 text-red-600" />
            </div>
            <Separator orientation="vertical" />
            <Button variant="ghost" @click="reformat">
                <WrapText class="h-4 w-4" />
            </Button>
            <AddQuestionDialog />
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
            <Separator orientation="vertical" />
            <Button :disabled="!isValid" variant="ghost" @click="upload">
                <Upload class="h-4 w-4" />
            </Button>
        </div>
    </div>

    <div ref="editorEl" class="min-h-64 overflow-auto rounded-md border bg-transparent font-mono text-base antialiased shadow-xs outline-none" />
</template>

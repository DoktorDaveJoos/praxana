// Dependencies:
//   npm i ajv json-source-map ajv-formats
// Types (optional):
//   npm i -D @types/json-source-map
//
// Usage example is at the bottom.

import { EditorView, type ViewUpdate } from '@codemirror/view';
import addFormats from 'ajv-formats';
import Ajv2020, { type ErrorObject, type ValidateFunction } from 'ajv/dist/2020';
import { parse as parseWithPointers } from 'json-source-map';
import { onBeforeUnmount, ref } from 'vue';

// 👉 import your schema from the module you showed
import { surveySchema } from '@/lib/codemirror-survey-schema-linter';
import { Extension } from '@codemirror/state'; // adjust the path

type Options = {
    collectErrors?: boolean; // default false
    debounceMs?: number; // default 80
};

function pointerToReadable(ptr: string): string {
    if (!ptr) return '<root>';
    const parts = ptr
        .split('/')
        .slice(1)
        .map((u) => u.replace(/~1/g, '/').replace(/~0/g, '~'));
    return parts
        .map((p) => (String(+p) === p ? `[${p}]` : /^[A-Za-z_][A-Za-z0-9_]*$/.test(p) ? `.${p}` : `["${p}"]`))
        .join('')
        .replace(/^\./, '');
}

function formatAjvError(e: ErrorObject): string {
    const path = e.instancePath ? pointerToReadable(e.instancePath) : '<root>';
    const base = e.message || 'schema violation';
    if (e.keyword === 'enum' && Array.isArray((e.params as any).allowedValues)) {
        return `${path}: must be one of ${(e.params as any).allowedValues.map(String).join(', ')}`;
    }
    if ((e as any).params?.missingProperty) {
        return `${path}: missing required property '${(e as any).params.missingProperty}'`;
    }
    return `${path}: ${base}`;
}

function extraChoiceBoundsErrors(data: any): string[] {
    const errs: string[] = [];
    const steps = data?.survey?.steps;
    if (!Array.isArray(steps)) return errs;
    steps.forEach((step: any, i: number) => {
        const opts = step?.options;
        if (!opts || typeof opts !== 'object') return;
        if (typeof opts.min_choices === 'number' && typeof opts.max_choices === 'number') {
            if (opts.min_choices > opts.max_choices) {
                errs.push(`survey.steps[${i}].options: min_choices (${opts.min_choices}) cannot be greater than max_choices (${opts.max_choices}).`);
            }
        }
    });
    return errs;
}

function buildSurveyValidator(): ValidateFunction {
    const ajv = new Ajv2020({
        strict: true,
        allErrors: true,
        allowUnionTypes: true,
    });
    addFormats(ajv);
    return ajv.compile(surveySchema as any);
}

const validateFnSingleton: ValidateFunction = buildSurveyValidator();

function validateText(text: string): { isValid: boolean; errors: string[] } {
    let data: any = null;
    try {
        const res = parseWithPointers(text);
        data = res.data;
    } catch {
        return { isValid: false, errors: ['Invalid JSON'] };
    }

    const ok = validateFnSingleton(data);
    const ajvErrors = ok ? [] : (validateFnSingleton.errors as ErrorObject[] | null) || [];
    const ajvMsgs = ajvErrors.map(formatAjvError);
    const extraMsgs = extraChoiceBoundsErrors(data);

    return { isValid: ok && extraMsgs.length === 0, errors: [...ajvMsgs, ...extraMsgs] };
}

export function useSurveySchemaValidity(options: Options = {}) {
    const { collectErrors = false, debounceMs = 80 } = options;

    const isValid = ref(false);
    const errors = ref<string[]>([]);
    let disposed = false;
    let timer: number | undefined;

    const run = (text: string) => {
        const res = validateText(text);
        isValid.value = res.isValid;
        if (collectErrors) errors.value = res.errors;
    };

    const schedule = (text: string) => {
        if (timer) window.clearTimeout(timer);
        timer = window.setTimeout(() => !disposed && run(text), debounceMs);
    };

    const extension: Extension = EditorView.updateListener.of((vu: ViewUpdate) => {
        if (vu.docChanged) {
            schedule(vu.state.doc.toString());
        }
    });

    // Call this once after the editor is created to seed the state
    const validateNow = (text: string) => run(text);

    onBeforeUnmount(() => {
        disposed = true;
        if (timer) window.clearTimeout(timer);
    });

    return { isValid, errors, extension, validateNow };
}

// CodeMirror 6 linter for the provided Survey JSON Schema (Draft 2020-12)
// Dependencies (install in your app):
//   npm i @codemirror/lint @codemirror/state @codemirror/view ajv json-source-map
// Optional (typings):
//   npm i -D @types/json-source-map

import type { Extension } from "@codemirror/state";
import { linter, type Diagnostic } from "@codemirror/lint";
import type { EditorView } from "@codemirror/view";
import Ajv2020, { type ErrorObject, type ValidateFunction } from "ajv/dist/2020";
import addFormats from "ajv-formats"; // to support a date format in options_date
import { parse as parseWithPointers } from "json-source-map";

/** The Survey JSON Schema (Draft 2020-12) */
export const surveySchema = {
    $schema: "https://json-schema.org/draft/2020-12/schema",
    $id: "https://example.com/survey.schema.json",
    title: "Survey Payload",
    type: "object",
    additionalProperties: false,
    required: ["survey"],
    properties: {
        survey: {
            type: "object",
            additionalProperties: false,
            required: ["name", "version", "steps"],
            properties: {
                name: { type: "string", minLength: 1 },
                description: { type: ["string", "null"] },
                version: { type: "integer", minimum: 1, default: 1 },
                is_active: { type: "boolean", default: true },
                steps: {
                    type: "array",
                    minItems: 1,
                    items: { $ref: "#/$defs/step" },
                },
            },
        },
    },
    $defs: {
        step: {
            type: "object",
            additionalProperties: false,
            required: ["order", "step_type"],
            properties: {
                order: { type: "integer", minimum: 1 },
                title: { type: ["string", "null"] },
                content: { type: ["string", "null"] },
                step_type: { type: "string", enum: ["dialog", "question"] },
                question_type: {
                    type: ["string", "null"],
                    enum: ["single_choice", "multiple_choice", "text", "number", "date", null],
                },
                options: {
                    oneOf: [
                        { type: "null" },
                        { type: "object" },
                    ],
                },
                choices: { $ref: "#/$defs/choicesArray" },
            },
            allOf: [
                {
                    if: { properties: { step_type: { const: "question" } } },
                    then: {
                        required: ["question_type"],
                        allOf: [
                            {
                                if: {
                                    properties: {
                                        question_type: { enum: ["single_choice", "multiple_choice"] },
                                    },
                                },
                                then: {
                                    required: ["options", "choices"],
                                    properties: {
                                        options: { $ref: "#/$defs/options_choice" },
                                        choices: { $ref: "#/$defs/choicesArray" },
                                    },
                                },
                            },
                            {
                                if: { properties: { question_type: { const: "text" } } },
                                then: {
                                    required: ["options"],
                                    properties: { options: { $ref: "#/$defs/options_text" }, choices: false },
                                },
                            },
                            {
                                if: { properties: { question_type: { const: "number" } } },
                                then: {
                                    required: ["options"],
                                    properties: { options: { $ref: "#/$defs/options_number" }, choices: false },
                                },
                            },
                            {
                                if: { properties: { question_type: { const: "date" } } },
                                then: {
                                    required: ["options"],
                                    properties: { options: { $ref: "#/$defs/options_date" }, choices: false },
                                },
                            },
                        ],
                    },
                    else: {
                        not: { required: ["choices"] },
                        properties: {
                            question_type: { type: ["null"] },
                            options: { type: ["null"] },
                        },
                    },
                },
            ],
        },
        choicesArray: {
            type: "array",
            minItems: 1,
            items: { $ref: "#/$defs/choice" },
        },
        choice: {
            type: "object",
            additionalProperties: false,
            required: ["label", "value"],
            properties: {
                label: { type: "string", minLength: 1 },
                value: { type: "string", minLength: 1 },
                optional_next_step: { type: ["integer", "null"], minimum: 1 },
                order: { type: ["integer", "null"], minimum: 1 },
            },
        },
        options_choice: {
            type: "object",
            additionalProperties: false,
            required: ["optional"],
            properties: {
                min_choices: { type: "integer", minimum: 0 },
                max_choices: { type: "integer", minimum: 1 },
                allow_other: { type: "boolean", default: false },
                other_label: { type: ["string", "null"] },
                optional: { type: "boolean", default: false },
            },
            description:
                "For single_choice/multiple_choice. (Note: schema cannot enforce min_choices ≤ max_choices without $data extensions.)",
        },
        options_text: {
            type: "object",
            additionalProperties: false,
            required: ["optional"],
            properties: {
                placeholder: { type: ["string", "null"] },
                max_length: { type: ["integer", "null"], minimum: 1 },
                optional: { type: "boolean", default: false },
            },
        },
        options_number: {
            type: "object",
            additionalProperties: false,
            required: ["optional"],
            properties: {
                min: { type: ["number", "null"] },
                max: { type: ["number", "null"] },
                step: { type: ["number", "null"] },
                optional: { type: "boolean", default: false },
            },
        },
        options_date: {
            type: "object",
            additionalProperties: false,
            required: ["optional"],
            properties: {
                min: {
                    oneOf: [
                        { type: "string", format: "date" },
                        { const: "today" },
                        { type: "null" },
                    ],
                },
                max: {
                    oneOf: [
                        { type: "string", format: "date" },
                        { const: "today" },
                        { type: "null" },
                    ],
                },
                format: {
                    type: ["string", "null"],
                    enum: ["YYYY-MM-DD", null],
                },
                optional: { type: "boolean", default: false },
            },
        },
    },
} as const;

/**
 * Build a CodeMirror linter extension that validates JSON against the surveySchema.
 * It maps Ajv errors to source ranges using json-source-map, and also adds
 * one extra semantic check (min_choices ≤ max_choices) that JSON Schema cannot express here.
 */
export function createSurveySchemaLinter(): Extension {
    // Prepare Ajv (Draft 2020-12)
    const ajv = new Ajv2020({
        strict: true,
        allErrors: true,
        allowUnionTypes: true, // helps with ["string", "null"]
    });
    addFormats(ajv);
    const validate: ValidateFunction = ajv.compile(surveySchema as any);

    // Utility: map a JSON Pointer to a [from, to] document range using json-source-map pointers
    function rangeForPointer(pointers: any, ptr: string, docLength: number): { from: number; to: number } {
        const info = pointers[ptr];
        if (info?.value) {
            const from = info.value.pos;
            const to = typeof info.valueEnd?.pos === "number" ? info.valueEnd.pos : info.value.pos + 1;
            return { from, to };
        }
        // Try parent key when the exact value node is missing
        const parent = ptr.replace(/\/(?:[^/]+)$/u, "");
        const parentInfo = pointers[parent];
        if (parentInfo?.key) {
            const from = parentInfo.key.pos;
            const to = typeof parentInfo.keyEnd?.pos === "number" ? parentInfo.keyEnd.pos : from + 1;
            return { from, to };
        }
        return { from: 0, to: Math.max(0, Math.min(docLength, 1)) };
    }

    // Extra rule: ensure min_choices ≤ max_choices in choice options
    function extraChoiceBoundsDiagnostics(data: any, pointers: any): Diagnostic[] {
        const diags: Diagnostic[] = [];
        if (!data || typeof data !== "object") return diags;
        const steps = data?.survey?.steps;
        if (!Array.isArray(steps)) return diags;
        steps.forEach((step: any, idx: number) => {
            const ptrBase = `/survey/steps/${idx}/options`;
            const opts = step?.options;
            if (!opts || typeof opts !== "object") return;
            if (typeof opts.min_choices === "number" && typeof opts.max_choices === "number") {
                if (opts.min_choices > opts.max_choices) {
                    const { from, to } = rangeForPointer(pointers, ptrBase, Infinity);
                    diags.push({
                        from,
                        to,
                        severity: "error",
                        message: `min_choices (${opts.min_choices}) cannot be greater than max_choices (${opts.max_choices}).`,
                    });
                }
            }
        });
        return diags;
    }

    return linter((view: EditorView) => {
        const text = view.state.doc.toString();

        // Quick exit: if not valid JSON, let the JSON language/syntax linter surface errors.
        // We still try to parse via json-source-map which is forgiving about mapping positions.
        let data: any = null;
        let pointers: Record<string, any> = {};
        try {
            const res = parseWithPointers(text);
            data = res.data;
            pointers = res.pointers as any;
        } catch {
            return [];
        }

        const diagnostics: Diagnostic[] = [];

        const ok = validate(data);
        if (!ok && validate.errors) {
            for (const err of validate.errors as ErrorObject[]) {
                // Ajv uses instancePath (leading '/') for the failing location. Root is ''
                const ptr = err.instancePath || "";
                const { from, to } = rangeForPointer(pointers, ptr, text.length);
                const msg = formatAjvError(err);
                diagnostics.push({ from, to, severity: "error", message: msg });
            }
        }

        // Extra semantic checks not covered by schema
        diagnostics.push(...extraChoiceBoundsDiagnostics(data, pointers));

        return diagnostics;
    }, { needsRefresh: (update) => update.docChanged });
}

function formatAjvError(e: ErrorObject): string {
    // Build a compact human-readable message
    // Example: "survey.steps[0].options.required: must have required property 'optional'"
    const path = e.instancePath ? pointerToReadable(e.instancePath) : "<root>";
    const base = e.message || "schema violation";
    if (e.keyword === "enum" && Array.isArray((e.params as any).allowedValues)) {
        return `${path}: must be one of ${(e.params as any).allowedValues.map(String).join(", ")}`;
    }
    if ((e as any).params?.missingProperty) {
        return `${path}: missing required property '${(e as any).params.missingProperty}'`;
    }
    return `${path}: ${base}`;
}

function pointerToReadable(ptr: string): string {
    if (!ptr) return "<root>";
    // Convert JSON Pointer to a friendlier dot/bracket path
    const parts = ptr.split("/").slice(1).map(u => u.replace(/~1/g, "/").replace(/~0/g, "~"));
    return parts
        .map(p => (String(+p) === p ? `[${p}]` : (p.match(/^[A-Za-z_][A-Za-z0-9_]*$/) ? `.${p}` : `[\"${p}\"]`)))
        .join("")
        .replace(/^\./, "");
}

/**
 * Example usage:
 *
 * import { EditorView, basicSetup } from "codemirror";
 * import { json } from "@codemirror/lang-json";
 * import { createSurveySchemaLinter } from "./codemirror-survey-schema-linter";
 *
 * const view = new EditorView({
 *   doc: '{\n  "survey": {\n    "practice_hash": ""\n  }\n}',
 *   extensions: [basicSetup, json(), createSurveySchemaLinter()],
 *   parent: document.getElementById("editor")!,
 * });
 */

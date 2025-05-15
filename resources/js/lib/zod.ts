// `resources/js/lib/rules.ts`
import { z } from 'zod';

export const errorMessages = {
    required: 'Dieses Feld ist erforderlich.',
    min: 'Das Feld muss mindestens 2 Zeichen lang sein.',
    max: 'Das Feld darf maximal 50 Zeichen lang sein.',
    email: 'Bitte geben Sie eine gültige E-Mail-Adresse ein.',
    regex: 'Ungültiges Format.',
    invalidDate: 'Ungültiges Geburtsdatum.',
    invalidDateFormat: 'Falsches Format.',
};

// Reusable rules
export const rules = {
    string: (min = 2, max = 50) => z.string().min(min, errorMessages.min).max(max, errorMessages.max),
    optionalString: (min = 2, max = 50) => z.string().min(min, errorMessages.min).max(max, errorMessages.max).optional(),
    email: () => z.string().email(errorMessages.email),
    date: () =>
        z
            .string()
            .regex(/^\d{2}\.\d{2}\.\d{4}$/, { message: errorMessages.invalidDateFormat })
            .refine(
                (val) => {
                    const [day, month, year] = val.split('.').map(Number);
                    const date = new Date(year, month - 1, day);
                    return date.getFullYear() === year && date.getMonth() === month - 1 && date.getDate() === day;
                },
                { message: errorMessages.invalidDate },
            ),
};

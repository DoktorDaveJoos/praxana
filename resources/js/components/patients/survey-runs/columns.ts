import DataTableDropdown from '@/components/patients/survey-runs/DataTableDropdown.vue';
import { Badge } from '@/components/ui/badge';

import type { SurveyRun } from '@/types';
import { ColumnDef } from '@tanstack/vue-table';
import { format } from 'date-fns';
import { h } from 'vue';

export const columns: ColumnDef<SurveyRun & { patientId: string }>[] = [
    {
        accessorKey: 'id',
        enableHiding: false,
        header: () => h('div', { class: 'text-left' }, 'ID'),
        cell: ({ row }) => {
            const chopped = String(row.getValue('id')).replace(/-/g, '').slice(-6);

            return h('code', { class: 'relative rounded max-w-4 bg-muted px-[0.3rem] py-[0.2rem] font-mono text-xs truncate' }, chopped);
        },
    },
    {
        accessorKey: 'name',
        enableHiding: false,
        header: () => h('div', { class: 'text-left' }, 'Name'),
        cell: ({ row }) => {
            return h('div', { class: 'text-left font-medium' }, row.getValue('name'));
        },
    },
    {
        accessorKey: 'status',
        header: () => h('div', { class: 'text-left' }, 'Status'),
        cell: ({ row }) => {
            const status = String(row.getValue('status') ?? '');

            // Prefer a prop over utility classes if your Badge supports variants
            const variant =
                status === 'completed' ? 'default' : status === 'pending' ? 'secondary' : status === 'aborted' ? 'destructive' : 'default';

            return h(
                Badge,
                { class: 'inline-flex', variant },
                { default: () => status }
            );
        },
    },
    {
        accessorKey: 'started_at',
        header: () => h('div', { class: 'text-left' }, 'Startdatum'),
        cell: ({ row }) => {
            const formattedDate = row.getValue('finished_at') ? format(new Date(row.getValue('started_at')), 'dd.MM.yyyy') : '-';
            return h('div', { class: 'text-left text-xs' }, formattedDate);
        },
    },
    {
        accessorKey: 'finished_at',
        header: () => h('div', { class: 'text-left' }, 'Enddatum'),
        cell: ({ row }) => {
            const formattedDate = row.getValue('finished_at') ? format(new Date(row.getValue('finished_at')), 'dd.MM.yyyy') : '-';
            return h('div', { class: 'text-left text-xs' }, formattedDate);
        },
    },
    {
        id: 'actions',
        enableHiding: false,
        cell: ({ row }) => {
            const surveyRun = row.original;

            return h(
                'div',
                { class: 'relative text-right' },
                h(DataTableDropdown, {
                    surveyRun: surveyRun,
                    patientId: row.original.patientId,
                }),
            );
        },
    },
];

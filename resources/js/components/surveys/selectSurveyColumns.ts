import type { Survey } from '@/types';
import { ColumnDef } from '@tanstack/vue-table';
import { h } from 'vue';
import { Checkbox } from '@/components/ui/checkbox';

export const selectSurveyColumns: ColumnDef<Survey>[] = [
    {
        id: 'select',
        header: ({ table }) => h(Checkbox, {
            'modelValue': table.getIsAllPageRowsSelected(),
            'onUpdate:modelValue': (value: boolean) => table.toggleAllPageRowsSelected(!!value),
            'ariaLabel': 'Select all',
        }),
        cell: ({ row }) => h(Checkbox, {
            'modelValue': row.getIsSelected(),
            'onUpdate:modelValue': (value: boolean) => row.toggleSelected(!!value),
            'ariaLabel': 'Select row',
        }),
        enableSorting: false,
        enableHiding: false,
    },
    {
        accessorKey: 'name',
        enableHiding: false,
        header: () => h('div', { class: 'text-left' }, 'Name'),
        cell: ({ row }) => {
            return h('div', { class: 'text-left text-primary font-medium' }, row.getValue('name'));
        },
    },
    {
        accessorKey: 'id',
        header: () => h('div', { class: 'text-left' }, 'ID'),
        cell: ({ row }) => {
            return h('code', { class: 'relative rounded max-w-4 bg-muted px-[0.3rem] py-[0.2rem] font-mono text-xs truncate' }, row.getValue('id'));
        },
    },
    {
        accessorKey: 'version',
        header: () => h('div', { class: 'text-left' }, 'Version'),
        cell: ({ row }) => {
            return h('code', { class: 'relative rounded max-w-4 bg-muted px-[0.3rem] py-[0.2rem] font-mono text-xs truncate' }, 'v.' + row.getValue('version'));
        },
    },
];

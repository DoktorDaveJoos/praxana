import DataTableDropdown from '@/components/patients/DataTableDropdown.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import type { Patient } from '@/types';
import { ColumnDef } from '@tanstack/vue-table';
import { ArrowUpDown } from 'lucide-vue-next';
import { h } from 'vue';

export const columns: ColumnDef<Patient>[] = [
    {
        accessorKey: 'name',
        enableHiding: false,
        header: ({ column }) => {
            return h(
                Button,
                {
                    variant: 'ghost',
                    onClick: () => column.toggleSorting(column.getIsSorted() === 'asc'),
                },
                () => ['Name', h(ArrowUpDown, { class: 'ml-2 h-4 w-4' })],
            );
        },
        cell: ({ row }) => {
            return h('div', { class: 'text-left font-medium' }, row.getValue('name'));
        },
    },
    {
        accessorKey: 'email',
        header: () => h('div', { class: 'text-left' }, 'Email'),
        cell: ({ row }) => h('div', { class: 'lowercase' }, row.getValue('email')),
    },
    {
        accessorKey: 'phone',
        header: () => h('div', { class: 'text-left' }, 'Telefon'),
        cell: ({ row }) => {
            return h('div', { class: 'text-left font-medium' }, row.getValue('phone'));
        },
    },
    {
        accessorKey: 'insurance_name',
        header: () => h('div', { class: 'text-left' }, 'Versicherung'),
        cell: ({ row }) => {
            return h('div', { class: 'text-left font-medium' }, row.getValue('insurance_name') ?? '-');
        },
    },
    {
        accessorKey: 'insurance_type',
        header: () => h('div', { class: 'text-left' }, 'Versicherungtyp'),
        cell: ({ row }) => {
            const type = row.getValue('insurance_type') as string | null | undefined;

            if (!type) {
                return h('span', { class: 'text-left text-xs text-muted-foreground' }, '-');
            }

            return h(
                Badge,
                { class: 'inline-flex' },
                { default: () => String(type) },
            );
        },
    },
    {
        id: 'actions',
        enableHiding: false,
        cell: ({ row }) => {
            const patient = row.original;

            return h(
                'div',
                { class: 'relative text-right' },
                h(DataTableDropdown, {
                    patient,
                }),
            );
        },
    },
];

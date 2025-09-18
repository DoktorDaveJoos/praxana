import DataTableDropdown from '@/components/surveys/DataTableDropdown.vue';
import { Checkbox } from '@/components/ui/checkbox';
import type { Survey } from '@/types';
import { ColumnDef } from '@tanstack/vue-table';
import { h } from 'vue';

export function selectSurveyColumns(options?: { withSelection?: boolean; withDescription?: boolean }): ColumnDef<Survey>[] {
    const { withSelection = true, withDescription = true } = options ?? {};

    const columns: ColumnDef<Survey>[] = [];

    if (withSelection) {
        columns.push({
            id: 'select',
            header: ({ table }) =>
                h(Checkbox, {
                    checked: table.getIsAllPageRowsSelected(),
                    'onUpdate:modelValue': (value: boolean) => table.toggleAllPageRowsSelected(value),
                    'aria-label': 'Select all',
                    // prevent sorting/focus quirks if you click the header
                    onClick: (e: Event) => e.stopPropagation(),
                }),
            cell: ({ row }) =>
                h(Checkbox, {
                    checked: row.getIsSelected(),
                    'onUpdate:modelValue': (value: boolean) => row.toggleSelected(value),
                    'aria-label': 'Select row',
                    onClick: (e: Event) => e.stopPropagation(),
                }),
            enableSorting: false,
            enableHiding: false,
            size: 32, // optional: keep the column narrow
        });
    }

    columns.push({
        accessorKey: 'name',
        enableHiding: false,
        header: () => h('div', { class: 'text-left' }, 'Name'),
        cell: ({ row }) => h('div', { class: 'text-left text-primary font-medium' }, row.getValue('name')),
    });

    if (withDescription) {
        columns.push({
            accessorKey: 'description',
            enableHiding: false,
            header: () => h('div', { class: 'text-left' }, 'Beschreibung'),
            cell: ({ row }) => h('div', { class: 'text-left text-primary max-w-64 truncate' }, row.getValue('description') ?? '-'),
        });
    }

    columns.push(
        {
            // @todo adapt size of column regarding display size
            accessorKey: 'id',
            header: () => h('div', { class: 'text-left' }, 'ID'),
            cell: ({ row }) =>
                h(
                    'div',
                    { class: 'max-w-16 overflow-hidden text-ellipsis whitespace-nowrap' },
                    h(
                        'code',
                        {
                            class: 'relative truncate block rounded bg-muted px-[0.3rem] py-[0.2rem] font-mono text-xs',
                        },
                        row.getValue('id'),
                    ),
                ),
        },
        {
            accessorKey: 'version',
            header: () => h('div', { class: 'text-left' }, 'Version'),
            cell: ({ row }) =>
                h(
                    'code',
                    {
                        class: 'relative rounded max-w-4 bg-muted px-[0.3rem] py-[0.2rem] font-mono text-xs truncate',
                    },
                    'v.' + row.getValue('version'),
                ),
        },
        {
            id: 'actions',
            enableHiding: false,
            cell: ({ row }) => {
                const survey = row.original;

                return h(
                    'div',
                    { class: 'relative text-right' },
                    h(DataTableDropdown, {
                        survey,
                    }),
                );
            },
        },
    );

    return columns;
}

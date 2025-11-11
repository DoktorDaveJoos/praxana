<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle, DialogTrigger } from '@/components/ui/dialog';
import { Form, FormControl, FormField, FormItem, FormLabel, FormMessage } from '@/components/ui/form';
import { Input } from '@/components/ui/input';
import { Item, ItemActions, ItemContent, ItemDescription, ItemMedia, ItemTitle } from '@/components/ui/item';
import { useVueStateMachine } from '@/composables/useVueStateMachine';
import { toTypedSchema } from '@vee-validate/zod';
import { AlignVerticalSpaceAround, ChevronRight, Info, MessageCircleQuestion } from 'lucide-vue-next';
import { useForm } from 'vee-validate';
import { ref } from 'vue';
import * as z from 'zod';

const States = {
    ChooseBlock: 'ChooseBlock',
    ChooseType: 'ChooseType',
    TitleContent: 'TitleContent',
};

const isOpen = ref(true);

const { StateMachine } = useVueStateMachine({
    initial: States.ChooseBlock,
    events: ['handleChooseType', 'handleTitleContent'],
    transitions: {
        [States.ChooseBlock]: {
            handleChooseType: States.ChooseType,
            handleTitleContent: States.TitleContent,
        },
        [States.ChooseType]: {
            //
        },
        [States.TitleContent]: {
            //
        },
    },
});

const formSchema = toTypedSchema(
    z.object({
        title: z.string(),
    }),
);

// const placeholder = ref();

const { handleSubmit } = useForm({
    validationSchema: formSchema,
});

// const value = computed({
//     get: () => (values.value ? parseDate(values.value) : undefined),
//     set: (val) => val,
// });

const onSubmit = handleSubmit((values) => {
    console.log(values);
    // emits('submit', { value: formValues.value, type: 'date' });
});
</script>

<template>
    <Dialog v-model:open="isOpen">
        <DialogTrigger as-child>
            <Button variant="ghost">
                <AlignVerticalSpaceAround class="h-4 w-4" />
            </Button>
        </DialogTrigger>

        <DialogContent class="h-[34rem] sm:max-w-2xl">
            <DialogHeader>
                <DialogTitle>Heading</DialogTitle>
                <DialogDescription> Fügen Sie Informationsblöcke und Fragen zum Fragebogen hinzu.</DialogDescription>
            </DialogHeader>

            <div class="grid gap-4 py-4">
                <StateMachine v-slot="{ current, fire }">
                    <!-- ChooseBlock: Initial -->
                    <template v-if="current === States.ChooseBlock">
                        <div class="flex w-full flex-col gap-2">
                            <Item variant="outline">
                                <ItemMedia variant="icon">
                                    <Info />
                                </ItemMedia>
                                <ItemContent>
                                    <ItemTitle>Informationsblock</ItemTitle>
                                    <ItemDescription>Neuen Infoblock zum Fragebogen hinzufügen.</ItemDescription>
                                </ItemContent>
                                <ItemActions>
                                    <Button size="sm" variant="outline" @click="fire.handleTitleContent">
                                        <ChevronRight class="h-4 w-4" />
                                    </Button>
                                </ItemActions>
                            </Item>

                            <Item variant="outline">
                                <ItemMedia variant="icon">
                                    <MessageCircleQuestion />
                                </ItemMedia>
                                <ItemContent>
                                    <ItemTitle>Frageblock</ItemTitle>
                                    <ItemDescription>Neue Frage zum Fragebogen hinzufügen.</ItemDescription>
                                </ItemContent>
                                <ItemActions>
                                    <Button size="sm" variant="outline" @click="fire.handleChooseType">
                                        <ChevronRight class="h-4 w-4" />
                                    </Button>
                                </ItemActions>
                            </Item>
                        </div>
                    </template>

                    <template v-if="current === States.ChooseType"> CHOOSE TYPE</template>

                    <template v-if="current === States.TitleContent">
                        <Form @submit="onSubmit">
                            <FormField v-slot="{ componentField }" name="title">
                                <FormItem>
                                    <FormLabel />
                                    <FormControl>
                                        <Input type="text" placeholder="Anamnese 1.1" v-bind="componentField" />
                                    </FormControl>

                                    <FormMessage />
                                </FormItem>
                            </FormField>
                        </Form>
                    </template>
                </StateMachine>
            </div>

            <DialogFooter />
        </DialogContent>
    </Dialog>
</template>

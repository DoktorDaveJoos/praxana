<script setup lang="ts">
import Heading from '@/components/Heading.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Item, ItemContent, ItemDescription, ItemTitle } from '@/components/ui/item';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem, Resource, SharedData, Survey } from '@/types';
import { Head, router, usePage } from '@inertiajs/vue3';
import { defineProps } from 'vue';

const props = defineProps<{
    survey: Resource<Survey>;
}>();

const breadcrumbItems: BreadcrumbItem[] = [
    {
        title: 'Fragebögen',
        href: route('practices.surveys.index', {
            practice: usePage<SharedData>().props.auth.practice.id,
        }),
    },
    {
        title: props.survey.data.name,
        href: route('practices.surveys.show', {
            practice: usePage<SharedData>().props.auth.practice.id,
            survey: props.survey.data.id,
        }),
    },
];

const editSurvey = () => {
    router.visit(
        route('practices.surveys.edit', {
            practice: usePage<SharedData>().props.auth.practice.id,
            survey: props.survey.data.id,
        }),
    );
};
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbItems">
        <Head title="Fragebögen" />

        <div class="space-y-6 px-4 py-6">
            <div class="flex justify-between">
                <Heading :title="survey.data.name" :description="survey.data.description" />
                <Button type="button" @click="editSurvey">Bearbeiten</Button>
            </div>
            <div class="flex items-center space-x-2">
                <Badge variant="secondary">v{{ survey.data.version }}</Badge>
                <Badge variant="secondary">{{ survey.data.id }}</Badge>
                <Badge variant="secondary">{{ survey.data.is_active ? 'active' : 'inactive' }}</Badge>
            </div>

            <div class="flex w-full flex-col gap-2">
                <Item v-for="step in survey.data.steps" :key="step.id" variant="outline">
                    <ItemContent>
                        <ItemTitle>
                            {{ step.title }}
                            <Badge variant="secondary">{{ step.step_type }}</Badge>
                            <Badge v-if="step.step_type === 'question'">{{ step.question_type }}</Badge>
                        </ItemTitle>
                        <ItemDescription>{{ step.content }}</ItemDescription>

                        <div class="pl-6" v-if="step.choices && step.choices?.length > 0">
                            <ul class="list-disc">
                                <li v-for="choice in step.choices" :key="choice.id">{{ choice.label }}</li>
                            </ul>
                        </div>
                    </ItemContent>
                    <!--                    <ItemActions>-->
                    <!--                        <Button variant="outline" size="sm"> Action</Button>-->
                    <!--                    </ItemActions>-->
                </Item>
            </div>
        </div>
    </AppLayout>
</template>

<style scoped></style>

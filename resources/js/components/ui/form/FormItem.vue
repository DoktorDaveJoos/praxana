<script lang="ts" setup>
import { cn } from '@/lib/utils';
import { useId } from 'reka-ui';
import { type HTMLAttributes, provide } from 'vue';
import { FORM_ITEM_INJECTION_KEY } from './injectionKeys';

import { vAutoAnimate } from '@formkit/auto-animate/vue';

const props = defineProps<{
    class?: HTMLAttributes['class']
}>();

const id = useId();
provide(FORM_ITEM_INJECTION_KEY, id);
</script>

<template>
    <div
        v-auto-animate
        data-slot="form-item"
        :class="cn('grid gap-2', props.class)"
    >
        <slot />
    </div>
</template>

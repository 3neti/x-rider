<script setup lang="ts">
import { computed } from 'vue';

const props = defineProps<{
    content?: {
        enabled?: boolean;
        type?: string;
        content?: string | null;
        meta?: Record<string, unknown>;
    } | null;
}>();

const enabled = computed(() => props.content?.enabled !== false && !!props.content?.content);
const type = computed(() => props.content?.type ?? 'markdown');
</script>

<template>
    <article v-if="enabled" class="prose prose-sm mx-auto max-w-none text-center dark:prose-invert">
        <div v-if="type === 'html'" v-html="content?.content" />
        <p v-else class="whitespace-pre-line">
            {{ content?.content }}
        </p>
    </article>
</template>

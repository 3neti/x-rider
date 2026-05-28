<script setup lang="ts">
import { computed } from 'vue';
import type { RiderContent } from './types';

const props = defineProps<{
  content?: RiderContent | null;
}>();

const enabled = computed(() =>
    props.content?.enabled !== false && !!props.content?.content
);

const type = computed(() => props.content?.type ?? 'markdown');

const canRenderHtml = computed(() =>
        type.value === 'html'
        && (
            props.content?.meta?.sanitized === true
            || props.content?.meta?.trusted_html === true
        )
);
</script>

<template>
  <article
      v-if="enabled"
      class="prose prose-sm mx-auto max-w-none text-center dark:prose-invert"
  >
    <div
        v-if="canRenderHtml"
        v-html="content?.content"
    />

    <p
        v-else
        class="whitespace-pre-line"
    >
      {{ content?.content }}
    </p>
  </article>
</template>
<script setup lang="ts">
import { computed } from 'vue';
import { marked } from 'marked';
import type { RiderStage } from '../types';

const props = defineProps<{
  stage: RiderStage;
}>();

const content = computed(() => props.stage.payload?.content as string | null | undefined);
const contentType = computed(() => props.stage.payload?.content_type as string | null | undefined);

const renderedContent = computed(() => {
  if (!content.value) {
    return null;
  }

  if (contentType.value === 'text') {
    return content.value.replace(/\n/g, '<br>');
  }

  try {
    return marked.parse(content.value) as string;
  } catch {
    return content.value.replace(/\n/g, '<br>');
  }
});

const timeout = computed(() => props.stage.payload?.timeout);

const presentation = computed(() =>
    (props.stage.payload?.presentation as string | undefined) ?? 'inline'
);
</script>

<template>
  <div
      v-if="stage.enabled && renderedContent && presentation === 'inline'"
      class="rounded-2xl border border-primary/10 bg-primary/5 p-4 text-center"
      :data-timeout="timeout"
  >
    <div
        v-html="renderedContent"
        class="prose prose-sm max-w-none dark:prose-invert"
    />
  </div>
</template>
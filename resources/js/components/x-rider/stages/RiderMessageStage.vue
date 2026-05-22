<script setup lang="ts">
import { computed } from 'vue';
import { marked } from 'marked';

interface RiderStage {
  type: string;
  enabled: boolean;
  key?: string | null;
  payload?: Record<string, unknown>;
  meta?: Record<string, unknown>;
}

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
</script>

<template>
  <div v-if="stage.enabled && renderedContent" class="overflow-visible">
    <div
        v-html="renderedContent"
        class="prose prose-lg max-w-none text-center font-semibold dark:prose-invert"
    />
  </div>
</template>
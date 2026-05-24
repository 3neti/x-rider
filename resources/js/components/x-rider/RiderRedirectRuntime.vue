<script setup lang="ts">
import { computed, onMounted } from 'vue';
import type { RawRiderStage } from '@/components/x-rider/types';

const props = defineProps<{
  stage?: RawRiderStage | null;
}>();

const redirectUrl = computed(() =>
    (props.stage?.payload?.url ?? props.stage?.url) as string | undefined
);

const redirectTimeout = computed(() => {
  const value =
      props.stage?.payload?.timeout
      ?? props.stage?.timeout
      ?? 0;

  return Number(value) || 0;
});

const isExternal = computed(() =>
    Boolean(props.stage?.payload?.external ?? true)
);

function executeRedirect(): void {
  if (!redirectUrl.value) {
    return;
  }

  if (isExternal.value) {
    window.location.href = redirectUrl.value;

    return;
  }

  window.location.assign(redirectUrl.value);
}

onMounted(() => {
  window.setTimeout(
      executeRedirect,
      redirectTimeout.value * 1000
  );
});
</script>

<template>
  <div
      v-if="redirectUrl"
      class="flex flex-col items-center justify-center gap-3 py-6 text-center"
  >
    <div class="text-sm font-medium text-muted-foreground">
      Redirecting...
    </div>

    <div class="text-xs text-muted-foreground">
      {{ redirectUrl }}
    </div>
  </div>
</template>
<script setup lang="ts">
import { computed, ref, watch } from 'vue';
import RiderStagePresenter from './RiderStagePresenter.vue';
import type { RawRiderStage } from './types';

const props = withDefaults(defineProps<{
  stages?: RawRiderStage[] | null;
  dismissLabel?: string;
}>(), {
  dismissLabel: 'Continue',
});

const dismissedKeys = ref<Set<string>>(new Set());

const modalStages = computed<RawRiderStage[]>(() =>
    (props.stages ?? []).filter((stage) => {
      const presentation = String(
          stage.payload?.presentation
          ?? stage.presentation
          ?? 'inline'
      ).trim().toLowerCase();

      return stage.enabled !== false
          && presentation === 'modal'
          && ['splash', 'message', 'image', 'link'].includes(stage.type);
    })
);

const activeStage = computed<RawRiderStage | null>(() =>
    modalStages.value.find((stage, index) => {
      const key = stage.key ?? `${stage.type}-${index}`;

      return !dismissedKeys.value.has(key);
    }) ?? null
);

const activeStageKey = computed(() => {
  if (!activeStage.value) {
    return null;
  }

  const index = modalStages.value.indexOf(activeStage.value);

  return activeStage.value.key ?? `${activeStage.value.type}-${index}`;
});

const visible = computed(() => !!activeStage.value);

watch(
    () => props.stages,
    () => {
      dismissedKeys.value = new Set();
    },
    { deep: true }
);

function dismiss(): void {
  if (!activeStageKey.value) {
    return;
  }

  const next = new Set(dismissedKeys.value);
  next.add(activeStageKey.value);
  dismissedKeys.value = next;
}
</script>

<template>
  <Teleport to="body">
    <div
        v-if="visible"
        class="fixed inset-0 z-50 flex items-center justify-center bg-background/80 px-4 backdrop-blur-sm"
        role="dialog"
        aria-modal="true"
    >
      <div class="w-full max-w-md rounded-2xl border bg-card p-5 shadow-lg">
        <div class="space-y-5">
          <RiderStagePresenter :stage="activeStage" force />

          <button
              type="button"
              class="inline-flex w-full items-center justify-center rounded-full bg-primary px-4 py-2 text-sm font-medium text-primary-foreground transition hover:bg-primary/90"
              @click="dismiss"
          >
            {{ dismissLabel }}
          </button>
        </div>
      </div>
    </div>
  </Teleport>
</template>
<script setup lang="ts">
import { computed, onMounted, onUnmounted, ref, watch } from 'vue';
import RiderStagePresenter from './RiderStagePresenter.vue';
import type { RawRiderStage, RiderRuntimeAction } from './types';
import { useRiderRuntimeActions } from './useRiderRuntimeActions';

type RuntimeTiming = 'on_mount' | 'after_delay' | 'on_complete';

const props = defineProps({
  stages: {
    type: Array as () => RawRiderStage[],
    default: () => [],
  },
  redirectEndpoint: {
    type: String,
    default: null,
  },
  continueLabel: {
    type: String,
    default: 'Continue',
  },
});

function isRecord(value: unknown): value is Record<string, unknown> {
  return Boolean(value && typeof value === 'object' && !Array.isArray(value));
}

function isRuntimeStage(value: unknown): value is RawRiderStage {
  return isRecord(value);
}

const visibleStageKeys = ref<string[]>([]);
const executedActionKeys = ref<string[]>([]);
const dismissedBlockingStageKeys = ref<string[]>([]);

function stageKey(stage: RawRiderStage, index: number): string {
  return stage.key ?? `${stage.type}-${index}`;
}

function isVisibleByKey(stageKey: string): boolean {
  return visibleStageKeys.value.includes(stageKey);
}

function showStage(stageKey: string): void {
  if (!isVisibleByKey(stageKey)) {
    visibleStageKeys.value.push(stageKey);
  }
}

const runtime = useRiderRuntimeActions({
  onShowStage: showStage,

  onTrackEvent: (event: string, meta?: Record<string, unknown>) => {
    console.debug('[x-rider] runtime event', event, meta ?? {});
  },

  onError: (error: unknown, action: RiderRuntimeAction) => {
    console.warn('[x-rider] runtime action failed', action, error);
  },
});

const enabledStages = computed((): RawRiderStage[] =>
    props.stages
        .filter(isRuntimeStage)
        .filter((stage: RawRiderStage) => stage.enabled !== false)
);

function isInitiallyHidden(stage: RawRiderStage): boolean {
  return Boolean(
      stage.payload?.initially_hidden
      || stage.payload?.hidden_until_shown
  );
}

function presentationOf(stage: RawRiderStage): string {
  return String(
      stage.payload?.presentation
      ?? stage.presentation
      ?? 'inline'
  ).trim().toLowerCase();
}

function isBlockingStage(stage: RawRiderStage): boolean {
  return stage.enabled !== false
      && ['modal', 'fullscreen'].includes(presentationOf(stage));
}

function isRedirectStage(stage: RawRiderStage): boolean {
  return stage.type === 'redirect';
}

const visibleStages = computed((): RawRiderStage[] =>
    enabledStages.value.filter((stage: RawRiderStage, index: number) => {
      const key = stageKey(stage, index);

      return isVisibleByKey(key) || !isInitiallyHidden(stage);
    })
);

const inlineStages = computed((): RawRiderStage[] =>
    visibleStages.value.filter((stage: RawRiderStage) =>
        !isBlockingStage(stage)
        && !isRedirectStage(stage)
    )
);

const blockingStages = computed((): RawRiderStage[] =>
    visibleStages.value.filter((stage: RawRiderStage) =>
        isBlockingStage(stage)
        && !dismissedBlockingStageKeys.value.includes(stage.key ?? '')
    )
);

const activeBlockingStage = computed<RawRiderStage | null>(() =>
    blockingStages.value[0] ?? null
);

const activePresentation = computed(() =>
    activeBlockingStage.value
        ? presentationOf(activeBlockingStage.value)
        : 'inline'
);

const isModal = computed(() => activePresentation.value === 'modal');
const isFullscreen = computed(() => activePresentation.value === 'fullscreen');

function actionKey(
    stage: RawRiderStage,
    action: RiderRuntimeAction,
    stageIndex: number,
    actionIndex: number
): string {
  return action.key ?? `${stageKey(stage, stageIndex)}:${action.type}:${actionIndex}`;
}

function hasExecuted(key: string): boolean {
  return executedActionKeys.value.includes(key);
}

function markExecuted(key: string): void {
  if (!hasExecuted(key)) {
    executedActionKeys.value.push(key);
  }
}

function legacyRedirectActions(stage: RawRiderStage): RiderRuntimeAction[] {
  if (stage.type !== 'redirect') {
    return [];
  }

  const url = props.redirectEndpoint
      ?? String(stage.payload?.url ?? stage.payload?.redirect_url ?? '');

  if (!url) {
    return [];
  }

  const timeoutSeconds = Number(stage.payload?.timeout ?? stage.timeout ?? 0);
  const delayMs = timeoutSeconds > 0 ? timeoutSeconds * 1000 : 0;

  return [
    {
      key: `${stage.key ?? 'legacy-redirect'}:delay`,
      type: 'delay',
      timing: 'on_complete',
      enabled: delayMs > 0,
      payload: {
        delay_ms: delayMs,
      },
    },
    {
      key: `${stage.key ?? 'legacy-redirect'}:redirect`,
      type: 'redirect',
      timing: 'on_complete',
      enabled: true,
      payload: {
        url,
      },
    },
  ];
}

function actionsForStageAndTiming(
    stage: RawRiderStage,
    timing: RuntimeTiming
): RiderRuntimeAction[] {
  const actions = [
    ...(stage.actions ?? []),
    ...legacyRedirectActions(stage),
  ];

  return runtime.actionsForTiming(actions, timing);
}

const isUnmounted = ref(false);

async function executeStageActions(
    stage: RawRiderStage,
    stageIndex: number,
    timing: RuntimeTiming
): Promise<void> {
  const actions = actionsForStageAndTiming(stage, timing);

  for (let actionIndex = 0; actionIndex < actions.length; actionIndex += 1) {
    if (isUnmounted.value) {
      return;
    }

    const action = actions[actionIndex];
    const key = actionKey(stage, action, stageIndex, actionIndex);

    if (hasExecuted(key)) {
      continue;
    }

    markExecuted(key);

    await runtime.execute(action);

    if (isUnmounted.value) {
      return;
    }
  }
}

async function runStage(stage: RawRiderStage, stageIndex: number): Promise<void> {
  if (isUnmounted.value) {
    return;
  }

  const key = stageKey(stage, stageIndex);

  showStage(key);

  await executeStageActions(stage, stageIndex, 'on_mount');
  await executeStageActions(stage, stageIndex, 'after_delay');
  await executeStageActions(stage, stageIndex, 'on_complete');
}

async function runSequence(): Promise<void> {
  for (let index = 0; index < enabledStages.value.length; index += 1) {
    if (isUnmounted.value) {
      return;
    }

    await runStage(enabledStages.value[index], index);
  }
}

watch(
    () => props.stages,
    () => {
      visibleStageKeys.value = [];

      void runSequence();
    },
    { deep: true }
);

onMounted(() => {
  void runSequence();
});

onUnmounted(() => {
  isUnmounted.value = true;
});

function advanceBlockingStage(): void {
  const stage = activeBlockingStage.value;

  if (!stage) {
    return;
  }

  const key = stage.key ?? `${stage.type}:${dismissedBlockingStageKeys.value.length}`;

  if (!dismissedBlockingStageKeys.value.includes(key)) {
    dismissedBlockingStageKeys.value.push(key);
  }
}
</script>

<template>
  <div
      v-if="inlineStages.length"
      class="space-y-3"
  >
    <RiderStagePresenter
        v-for="(stage, index) in inlineStages"
        :key="stage.key ?? `${stage.type}-${index}`"
        :stage="stage"
    />
  </div>

  <Teleport to="body">
  <div
      v-if="activeBlockingStage && isModal"
      class="fixed inset-0 flex items-center justify-center bg-background/80 px-4 backdrop-blur-sm"
      style="z-index: 60"
      role="dialog"
      aria-modal="true"
      :aria-label="String(activeBlockingStage.payload?.aria_label ?? activeBlockingStage.payload?.label ?? activeBlockingStage.key ?? 'Rider experience')"
  >
    <div class="w-full max-w-lg rounded-2xl border bg-card p-5 shadow-xl">
      <div class="space-y-4">
        <RiderStagePresenter
            :stage="activeBlockingStage"
            embedded
        />

        <button
            type="button"
            data-test="dismiss"
            class="inline-flex w-full items-center justify-center rounded-full bg-primary px-4 py-2 text-sm font-medium text-primary-foreground transition hover:bg-primary/90"
            @click="advanceBlockingStage"
        >
          {{ continueLabel }}
        </button>
      </div>
    </div>
  </div>

  <section
      v-else-if="activeBlockingStage && isFullscreen"
      class="fixed inset-0 flex h-[100svh] flex-col overflow-y-auto bg-background px-4 pt-6 sm:px-6"
      style="z-index: 60; padding-bottom: max(0.2in, calc(env(safe-area-inset-bottom) + 1rem))"
      role="dialog"
      aria-modal="true"
      :aria-label="String(activeBlockingStage.payload?.aria_label ?? activeBlockingStage.payload?.label ?? activeBlockingStage.key ?? 'Rider experience')"
  >
    <div class="mx-auto flex min-h-full w-full max-w-2xl flex-1 flex-col">
      <div class="flex flex-1 flex-col justify-center py-4 text-center">
        <RiderStagePresenter
            :stage="activeBlockingStage"
            embedded
        />
      </div>
      <button
          type="button"
          data-test="dismiss"
          class="mx-auto mt-4 inline-flex h-11 w-full max-w-lg shrink-0 items-center justify-center rounded-full bg-primary px-4 text-sm font-medium text-primary-foreground shadow-lg shadow-foreground/10 transition hover:bg-primary/90"
          @click="advanceBlockingStage"
      >
        {{ continueLabel }}
      </button>
    </div>
  </section>
  </Teleport>
</template>

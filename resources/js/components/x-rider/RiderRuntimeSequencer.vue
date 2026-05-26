<script setup lang="ts">
import { computed, onMounted, ref } from 'vue';
import RiderStagePresenter from './RiderStagePresenter.vue';
import type { RawRiderStage, RiderRuntimeAction } from './types';
import { useRiderRuntimeActions } from './useRiderRuntimeActions';

type RuntimeTiming = 'on_mount' | 'after_delay' | 'on_complete';

interface Props {
  stages?: RawRiderStage[];
  redirectEndpoint?: string | null;
}

const props = withDefaults(defineProps<Props>(), {
  stages: () => [],
  redirectEndpoint: null,
});

const visibleStageKeys = ref<string[]>([]);
const executedActionKeys = ref<string[]>([]);

function stageKey(stage: RawRiderStage, index: number): string {
  return stage.key ?? `${stage.type}-${index}`;
}

function isVisibleByKey(stageKey: string): boolean {
  return visibleStageKeys.value.indexOf(stageKey) >= 0;
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

const enabledStages = computed<RawRiderStage[]>(() =>
    props.stages.filter((stage) => stage.enabled !== false)
);

function isInitiallyHidden(stage: RawRiderStage): boolean {
  return Boolean(
      stage.payload?.initially_hidden
      || stage.payload?.hidden_until_shown
  );
}

const visibleStages = computed<RawRiderStage[]>(() =>
    enabledStages.value.filter((stage, index) => {
      const key = stageKey(stage, index);

      return isVisibleByKey(key) || !isInitiallyHidden(stage);
    })
);

function actionKey(
    stage: RawRiderStage,
    action: RiderRuntimeAction,
    stageIndex: number,
    actionIndex: number
): string {
  return action.key ?? `${stageKey(stage, stageIndex)}:${action.type}:${actionIndex}`;
}

function hasExecuted(key: string): boolean {
  return executedActionKeys.value.indexOf(key) >= 0;
}

function markExecuted(key: string): void {
  if (!hasExecuted(key)) {
    executedActionKeys.value.push(key);
  }
}

function legacyRedirectAction(stage: RawRiderStage): RiderRuntimeAction | null {
  if (stage.type !== 'redirect') {
    return null;
  }

  const url = props.redirectEndpoint
      ?? String(stage.payload?.url ?? stage.payload?.redirect_url ?? '');

  if (!url) {
    return null;
  }

  return {
    key: `${stage.key ?? 'legacy-redirect'}:redirect`,
    type: 'redirect',
    timing: 'on_complete',
    enabled: true,
    payload: {
      url,
    },
  };
}

function actionsForStageAndTiming(
    stage: RawRiderStage,
    timing: RuntimeTiming
): RiderRuntimeAction[] {
  const legacy = legacyRedirectAction(stage);
  const actions = legacy
      ? [...(stage.actions ?? []), legacy]
      : stage.actions;

  return runtime.actionsForTiming(actions, timing);
}

async function executeStageActions(
    stage: RawRiderStage,
    stageIndex: number,
    timing: RuntimeTiming
): Promise<void> {
  const actions = actionsForStageAndTiming(stage, timing);

  for (let actionIndex = 0; actionIndex < actions.length; actionIndex += 1) {
    const action = actions[actionIndex];
    const key = actionKey(stage, action, stageIndex, actionIndex);

    if (hasExecuted(key)) {
      continue;
    }

    markExecuted(key);

    await runtime.execute(action);
  }
}

async function runStage(stage: RawRiderStage, stageIndex: number): Promise<void> {
  await executeStageActions(stage, stageIndex, 'on_mount');
  await executeStageActions(stage, stageIndex, 'after_delay');
  await executeStageActions(stage, stageIndex, 'on_complete');
}

async function runSequence(): Promise<void> {
  for (let index = 0; index < enabledStages.value.length; index += 1) {
    await runStage(enabledStages.value[index], index);
  }
}

onMounted(() => {
  void runSequence();
});
</script>

<template>
  <div
      v-if="visibleStages.length"
      class="space-y-3"
  >
    <RiderStagePresenter
        v-for="(stage, index) in visibleStages"
        :key="stage.key ?? `${stage.type}-${index}`"
        :stage="stage"
    />
  </div>
</template>
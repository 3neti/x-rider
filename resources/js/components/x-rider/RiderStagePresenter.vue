<script setup lang="ts">
import { computed, onMounted, onUnmounted, ref } from 'vue';
import RiderRenderer from './RiderRenderer.vue';
import type { RawRiderStage, RiderRuntimeAction } from './types';
import { useRiderRuntimeActions } from './useRiderRuntimeActions';

const props = defineProps({
  stage: {
    type: Object as () => RawRiderStage,
    required: true,
  },
});

const dismissed = ref(false);

const presentation = computed(() =>
    String(
        props.stage.payload?.presentation
        ?? props.stage.presentation
        ?? 'inline'
    ).trim().toLowerCase()
);

const isModal = computed(() => presentation.value === 'modal');
const isFullscreen = computed(() => presentation.value === 'fullscreen');

const label = computed(() =>
    String(props.stage.payload?.label ?? 'Continue')
);

const url = computed(() =>
    String(props.stage.payload?.url ?? props.stage.src ?? '')
);

const imageSrc = computed(() =>
    String(props.stage.src ?? props.stage.payload?.src ?? '')
);

const imageAlt = computed(() =>
    String(props.stage.alt ?? props.stage.payload?.alt ?? 'Rider image')
);

const stageContent = computed(() => ({
  enabled: props.stage.enabled !== false,
  type: props.stage.content_type ?? 'markdown',
  content: props.stage.content ?? '',
}));

const remainingSeconds = ref(0);
let countdownTimer: number | null = null;

const redirectTimeout = computed(() =>
    Number(props.stage.payload?.timeout ?? props.stage.timeout ?? 0)
);

const isRedirect = computed(() =>
    props.stage.type === 'redirect'
);

const runtime = useRiderRuntimeActions({
  userGesture: true,
  onError: (error: unknown, action: RiderRuntimeAction) => {
    console.warn('[x-rider] click action failed', action, error);
  },
});

const clickActions = computed(() =>
    runtime.actionsForTiming(props.stage.actions, 'on_click')
);

const hasClickActions = computed(() =>
    clickActions.value.length > 0
);

async function handleClickAction(event: Event): Promise<void> {
  if (!hasClickActions.value) {
    return;
  }

  event.preventDefault();

  await runtime.executeMany(clickActions.value);
}

const emit = defineEmits(['dismissed']);

function dismiss(): void {
  dismissed.value = true;
  emit('dismissed');
}

onMounted(() => {
  if (props.stage.type !== 'redirect') {
    return;
  }

  remainingSeconds.value = redirectTimeout.value;

  if (remainingSeconds.value <= 0) {
    return;
  }

  countdownTimer = window.setInterval(() => {
    remainingSeconds.value -= 1;

    if (remainingSeconds.value <= 0 && countdownTimer !== null) {
      window.clearInterval(countdownTimer);
      countdownTimer = null;
    }
  }, 1000);
});

onUnmounted(() => {
  if (countdownTimer !== null) {
    window.clearInterval(countdownTimer);
    countdownTimer = null;
  }
});

const copyActions = computed(() =>
    runtime.actionsForTiming(props.stage.actions, 'on_click')
        .filter((action) => action.type === 'copy_to_clipboard')
);

const hasCopyActions = computed(() =>
    copyActions.value.length > 0
);

const copyLabel = computed(() =>
    String(copyActions.value[0]?.payload?.label ?? 'Copy')
);

async function handleCopyAction(): Promise<void> {
  if (!hasCopyActions.value) {
    return;
  }

  await runtime.executeMany(copyActions.value);
}
</script>

<template>
  <template v-if="!dismissed">
    <div
        :class="{
                'fixed inset-0 z-50 flex items-center justify-center bg-black/50 px-4': isModal,
                'fixed inset-0 z-50 flex items-center justify-center bg-background px-6': isFullscreen,
            }"
    >
      <div
          :class="{
                    'w-full max-w-md rounded-2xl bg-background p-5 shadow-xl': isModal,
                    'mx-auto w-full max-w-lg space-y-6 text-center': isFullscreen,
                    'space-y-3': !isModal && !isFullscreen,
                }"
      >
        <RiderRenderer
            v-if="stage.content && !isRedirect"
            :content="stageContent"
        />

        <button
            v-else-if="hasCopyActions"
            type="button"
            class="inline-flex rounded-full bg-primary px-4 py-2 text-sm font-medium text-primary-foreground"
            @click="handleCopyAction"
        >
          {{ copyLabel }}
        </button>

        <img
            v-else-if="stage.type === 'image' && imageSrc"
            :src="imageSrc"
            :alt="imageAlt"
            class="w-full rounded-xl object-cover"
        />

        <a
            v-else-if="stage.type === 'link' && url"
            :href="url"
            target="_blank"
            rel="noopener noreferrer"
            class="inline-flex text-sm font-medium text-primary underline"
            @click="handleClickAction"
        >
          {{ label }}
        </a>

        <a
            v-else-if="stage.type === 'cta' && url"
            :href="url"
            target="_blank"
            rel="noopener noreferrer"
            class="inline-flex rounded-full bg-primary px-4 py-2 text-sm font-medium text-primary-foreground"
            @click="handleClickAction"
        >
          {{ label }}
        </a>

        <div
            v-if="isRedirect"
            class="rounded-2xl border bg-card p-5 text-center shadow-sm"
        >
          <RiderRenderer
              v-if="stage.content"
              :content="stageContent"
          />

          <p
              v-else
              class="text-sm font-medium text-foreground"
          >
            Redirecting you now...
          </p>

          <p
              v-if="redirectTimeout > 0"
              class="mt-1 text-xs text-muted-foreground"
          >
            Redirecting in {{ Math.max(remainingSeconds, 0) }} seconds.
          </p>
        </div>

        <button
            v-if="isModal || isFullscreen"
            type="button"
            class="w-full rounded-full bg-primary px-4 py-2 text-sm font-medium text-primary-foreground"
            @click="dismiss"
        >
          Continue
        </button>
      </div>
    </div>
  </template>
</template>
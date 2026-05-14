<script setup lang="ts">
import { computed, onBeforeUnmount, onMounted, ref } from 'vue';

const props = withDefaults(defineProps<{
    seconds?: number;
    endpoint?: string | null;
    enabled?: boolean;
}>(), {
    seconds: 5,
    endpoint: null,
    enabled: true,
});

const remaining = ref(Math.max(0, props.seconds));
let timer: number | undefined;

const canRedirect = computed(() => props.enabled && !!props.endpoint);

function goNow() {
    if (!canRedirect.value || !props.endpoint) return;
    window.location.href = props.endpoint;
}

onMounted(() => {
    if (!canRedirect.value) return;

    timer = window.setInterval(() => {
        remaining.value -= 1;

        if (remaining.value <= 0) {
            window.clearInterval(timer);
            goNow();
        }
    }, 1000);
});

onBeforeUnmount(() => {
    if (timer) window.clearInterval(timer);
});
</script>

<template>
    <div v-if="canRedirect" class="space-y-3 text-center">
        <p class="text-sm text-muted-foreground">
            Continuing in {{ remaining }} second<span v-if="remaining !== 1">s</span>.
        </p>
        <button
            type="button"
            class="inline-flex items-center justify-center rounded-lg border px-4 py-2 text-sm font-medium shadow-sm"
            @click="goNow"
        >
            Continue now
        </button>
    </div>
</template>

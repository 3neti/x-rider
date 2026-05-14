<script setup lang="ts">
import RiderCountdown from '@/components/x-rider/RiderCountdown.vue';
import RiderRenderer from '@/components/x-rider/RiderRenderer.vue';

const props = defineProps<{
    rider: {
        state?: string;
        subject?: { reference?: string; code?: string | null };
        success?: { enabled?: boolean; type?: string; content?: string | null; meta?: Record<string, unknown> } | null;
        redirect?: { enabled?: boolean; timeout?: number; url?: string | null } | null;
    };
    redirectEndpoint?: string | null;
}>();
</script>

<template>
    <main class="flex min-h-screen items-center justify-center bg-background px-4 py-10 text-foreground">
        <section class="w-full max-w-md space-y-6 rounded-2xl border bg-card p-6 text-card-foreground shadow-sm">
            <div class="space-y-2 text-center">
                <p class="text-xs uppercase tracking-wide text-muted-foreground">Claim accepted</p>
                <h1 class="text-2xl font-semibold">Thank you</h1>
                <p v-if="props.rider.subject?.code" class="text-sm text-muted-foreground">
                    Pay Code: {{ props.rider.subject.code }}
                </p>
            </div>

            <RiderRenderer :content="props.rider.success" />

            <RiderCountdown
                :enabled="props.rider.redirect?.enabled ?? false"
                :seconds="props.rider.redirect?.timeout ?? 5"
                :endpoint="props.redirectEndpoint"
            />
        </section>
    </main>
</template>

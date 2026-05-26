import type { RawRiderStage, RiderStagePhase } from './types';

export function stagePresentation(stage?: RawRiderStage | null): string {
    return String(
        stage?.payload?.presentation
        ?? stage?.presentation
        ?? 'inline'
    ).trim().toLowerCase();
}

export function explicitStagePhase(stage?: RawRiderStage | null): string | null {
    const phase = stage?.payload?.phase ?? stage?.phase ?? null;

    if (!phase) {
        return null;
    }

    return String(phase).trim().toLowerCase();
}

export function inferStagePhase(stage?: RawRiderStage | null): RiderStagePhase {
    const explicit = explicitStagePhase(stage);

    if (
        explicit === 'pre_claim'
        || explicit === 'runtime'
        || explicit === 'success'
        || explicit === 'redirect'
        || explicit === 'post_claim'
    ) {
        return explicit;
    }

    const presentation = stagePresentation(stage);
    const type = stage?.type;

    if (type === 'redirect') {
        return 'redirect';
    }

    if (presentation === 'modal' || presentation === 'fullscreen') {
        return 'runtime';
    }

    if (type === 'message') {
        return 'success';
    }

    return 'pre_claim';
}

export function stageIsInPhase(
    stage: RawRiderStage,
    phase: RiderStagePhase
): boolean {
    return inferStagePhase(stage) === phase;
}
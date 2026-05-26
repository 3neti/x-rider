export type RiderPresentationMode = 'inline' | 'modal' | 'fullscreen';

export interface RiderContent {
    enabled: boolean;
    type: string;
    content?: string | null;
    meta?: Record<string, unknown>;
}

export interface RiderRedirect {
    enabled: boolean;
    url?: string | null;
    timeout: number;
    fallbackUrl?: string | null;
    meta?: Record<string, unknown>;
}

export interface RiderStage {
    type: string;
    enabled: boolean;
    key?: string | null;

    /**
     * Stage-driver-specific normalized payload.
     *
     * Examples:
     * - splash payload
     * - redirect payload
     * - message payload
     * - future video/cta payloads
     */
    payload?: Record<string, unknown>;

    /**
     * Runtime metadata.
     */
    meta?: Record<string, unknown>;
}

export interface RiderStageCollection {
    stages?: RiderStage[];
    meta?: Record<string, unknown>;
}

export interface RiderExperience {
    state?: string;
    preClaim?: RiderContent | null;
    success?: RiderContent | null;
    redirect?: RiderRedirect | null;
    stages?: RiderStageCollection | null;
    ads?: unknown[];
    analytics?: Record<string, unknown>;
    meta?: Record<string, unknown>;
}

export type RiderStagePhase =
    | 'pre_claim'
    | 'runtime'
    | 'success'
    | 'redirect'
    | 'post_claim';

/**
 * Loose raw stage shape for preview payloads that still expose original YAML config.
 *
 * Prefer RiderExperience.preClaim when available.
 */
export interface RawRiderStage {
    type: string;
    enabled?: boolean;
    key?: string | null;

    phase?: RiderStagePhase | string | null;

    payload?: Record<string, unknown> & {
        phase?: RiderStagePhase | string | null;
        presentation?: RiderPresentationMode | string | null;
    };
    meta?: Record<string, unknown>;

    content?: string | null;
    content_type?: string | null;
    timeout?: number | string | null;

    presentation?: RiderPresentationMode | string | null;

    action?: string | null;
    label?: string | null;
    url?: string | null;

    src?: string | null;
    alt?: string | null;
}

export interface RiderPreviewPayload {
    state?: string;
    preClaim?: RiderContent | null;
    stages?: RiderStageCollection | null;
    meta?: Record<string, unknown>;
}

export interface RiderRedirectPayload {
    url?: string | null;
    timeout?: number | null;
    external?: boolean | null;
}
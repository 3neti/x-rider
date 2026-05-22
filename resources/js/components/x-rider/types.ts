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
    payload?: Record<string, unknown>;
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

/**
 * Loose raw stage shape for preview payloads that still expose original YAML config.
 *
 * Prefer RiderExperience.preClaim when available.
 */
export interface RawRiderStage {
    type: string;
    enabled?: boolean;
    key?: string | null;
    payload?: Record<string, unknown>;
    meta?: Record<string, unknown>;
    content?: string | null;
    content_type?: string | null;
    timeout?: number | string | null;
}

export interface RiderPreviewPayload {
    state?: string;
    preClaim?: RiderContent | null;
    stages?: RiderStageCollection | null;
    meta?: Record<string, unknown>;
}
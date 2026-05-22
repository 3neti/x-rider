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
import type {
    RiderRuntimeAction,
    RiderRuntimeActionTiming,
} from './types';

export interface RiderRuntimeActionContext {
    userGesture?: boolean;
    onShowStage?: (stageKey: string) => void;
    onClose?: () => void;
    onTrackEvent?: (event: string, meta?: Record<string, unknown>) => void;
    onError?: (error: unknown, action: RiderRuntimeAction) => void;
}

function isEnabled(action: RiderRuntimeAction): boolean {
    return action.enabled !== false;
}

function startsWithSlash(rawUrl: string): boolean {
    return rawUrl.charAt(0) === '/';
}

function protocolIsAllowed(protocol: string): boolean {
    return protocol === 'http:' || protocol === 'https:';
}

function isSafeUrl(rawUrl?: string): boolean {
    if (!rawUrl) return false;

    if (startsWithSlash(rawUrl)) {
        return true;
    }

    try {
        const url = new URL(rawUrl);

        return protocolIsAllowed(url.protocol);
    } catch {
        return false;
    }
}

function shouldBlockForGesture(
    action: RiderRuntimeAction,
    context: RiderRuntimeActionContext
): boolean {
    return action.requires_user_gesture === true && context.userGesture !== true;
}

function delay(ms: number): Promise<void> {
    return new Promise<void>((resolve) => {
        window.setTimeout(resolve, ms);
    });
}

export function useRiderRuntimeActions(context: RiderRuntimeActionContext = {}) {
    async function execute(action: RiderRuntimeAction): Promise<void> {
        try {
            if (!isEnabled(action)) return;

            if (shouldBlockForGesture(action, context)) {
                return;
            }

            const payload = action.payload || {};

            switch (action.type) {
                case 'redirect': {
                    if (!isSafeUrl(payload.url)) return;

                    window.location.href = String(payload.url);
                    return;
                }

                case 'open_url': {
                    if (!isSafeUrl(payload.url)) return;

                    const target = payload.target || '_blank';

                    window.open(payload.url, target, 'noopener,noreferrer');
                    return;
                }

                case 'copy_to_clipboard': {
                    if (!payload.text) return;

                    await navigator.clipboard.writeText(payload.text);
                    return;
                }

                case 'track_event': {
                    if (!payload.event) return;

                    if (context.onTrackEvent) {
                        context.onTrackEvent(
                            payload.event,
                            payload.meta
                        );
                    }

                    return;
                }

                case 'delay': {
                    await delay(Number(payload.delay_ms || 0));
                    return;
                }

                case 'show_stage': {
                    if (!payload.stage_key) return;

                    if (context.onShowStage) {
                        context.onShowStage(payload.stage_key);
                    }

                    return;
                }

                case 'close': {
                    if (context.onClose) {
                        context.onClose();
                    }

                    return;
                }

                default:
                    return;
            }
        } catch (error) {
            if (context.onError) {
                context.onError(error, action);
            }
        }
    }

    async function executeMany(actions: RiderRuntimeAction[]): Promise<void> {
        for (const action of actions) {
            await execute(action);
        }
    }

    function actionsForTiming(
        actions: RiderRuntimeAction[] | undefined,
        timing: RiderRuntimeActionTiming
    ): RiderRuntimeAction[] {
        return (actions || []).filter((action) =>
            action.enabled !== false
            && (action.timing || 'on_click') === timing
        );
    }

    return {
        execute,
        executeMany,
        actionsForTiming,
    };
}
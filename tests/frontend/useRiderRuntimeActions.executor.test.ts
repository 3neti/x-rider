import { afterEach, beforeEach, describe, expect, it, vi } from 'vitest';
import { useRiderRuntimeActions } from '../../resources/js/components/x-rider/useRiderRuntimeActions';

describe('useRiderRuntimeActions executor behavior', () => {
    beforeEach(() => {
        vi.useFakeTimers();
        vi.restoreAllMocks();
    });

    afterEach(() => {
        vi.useRealTimers();
        vi.restoreAllMocks();
    });

    it('executes delay actions', async () => {
        const runtime = useRiderRuntimeActions();

        let completed = false;

        const promise = runtime.execute({
            type: 'delay',
            payload: {
                delay_ms: 3000,
            },
        }).then(() => {
            completed = true;
        });

        expect(completed).toBe(false);

        await vi.advanceTimersByTimeAsync(3000);
        await promise;

        expect(completed).toBe(true);
    });

    it('executes show_stage callback', async () => {
        const onShowStage = vi.fn();

        const runtime = useRiderRuntimeActions({
            onShowStage,
        });

        await runtime.execute({
            type: 'show_stage',
            payload: {
                stage_key: 'next-stage',
            },
        });

        expect(onShowStage).toHaveBeenCalledWith('next-stage');
    });

    it('executes close callback', async () => {
        const onClose = vi.fn();

        const runtime = useRiderRuntimeActions({
            onClose,
        });

        await runtime.execute({
            type: 'close',
        });

        expect(onClose).toHaveBeenCalledOnce();
    });

    it('executes track_event callback', async () => {
        const onTrackEvent = vi.fn();

        const runtime = useRiderRuntimeActions({
            onTrackEvent,
        });

        await runtime.execute({
            type: 'track_event',
            payload: {
                event: 'stage.viewed',
                meta: {
                    key: 'intro',
                },
            },
        });

        expect(onTrackEvent).toHaveBeenCalledWith('stage.viewed', {
            key: 'intro',
        });
    });

    it('copies text to clipboard', async () => {
        const writeText = vi.fn().mockResolvedValue(undefined);

        Object.assign(navigator, {
            clipboard: {
                writeText,
            },
        });

        const runtime = useRiderRuntimeActions();

        await runtime.execute({
            type: 'copy_to_clipboard',
            payload: {
                text: 'ABC123',
            },
        });

        expect(writeText).toHaveBeenCalledWith('ABC123');
    });

    it('reports errors to onError', async () => {
        const error = new Error('Clipboard failed');
        const onError = vi.fn();

        Object.assign(navigator, {
            clipboard: {
                writeText: vi.fn().mockRejectedValue(error),
            },
        });

        const runtime = useRiderRuntimeActions({
            onError,
        });

        const action = {
            type: 'copy_to_clipboard' as const,
            payload: {
                text: 'ABC123',
            },
        };

        await runtime.execute(action);

        expect(onError).toHaveBeenCalledWith(error, action);
    });

    it('filters actions by timing', () => {
        const runtime = useRiderRuntimeActions();

        const actions = [
            {
                type: 'track_event' as const,
                timing: 'on_mount' as const,
                payload: {
                    event: 'mounted',
                },
            },
            {
                type: 'open_url' as const,
                timing: 'on_click' as const,
                payload: {
                    url: 'https://example.com',
                },
            },
            {
                type: 'close' as const,
                enabled: false,
                timing: 'on_click' as const,
            },
        ];

        expect(runtime.actionsForTiming(actions, 'on_click'))
            .toHaveLength(1);

        expect(runtime.actionsForTiming(actions, 'on_click')[0].type)
            .toBe('open_url');
    });

    it('defaults missing timing to on_click', () => {
        const runtime = useRiderRuntimeActions();

        const actions = [
            {
                type: 'close' as const,
            },
        ];

        expect(runtime.actionsForTiming(actions, 'on_click'))
            .toHaveLength(1);
    });
});
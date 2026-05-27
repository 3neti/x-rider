import { describe, expect, it, vi, beforeEach, afterEach } from 'vitest';
import { useRiderRuntimeActions } from '../../resources/js/components/x-rider/useRiderRuntimeActions';

describe('useRiderRuntimeActions URL safety', () => {
    const originalLocation = window.location;

    beforeEach(() => {
        vi.restoreAllMocks();

        Object.defineProperty(window, 'location', {
            configurable: true,
            value: {
                href: '',
            },
        });

        vi.spyOn(window, 'open').mockImplementation(() => null);
    });

    afterEach(() => {
        Object.defineProperty(window, 'location', {
            configurable: true,
            value: originalLocation,
        });

        vi.restoreAllMocks();
    });

    it('allows relative redirect URLs', async () => {
        const runtime = useRiderRuntimeActions();

        await runtime.execute({
            type: 'redirect',
            payload: {
                url: '/x/claim/ABC123/redirect',
            },
        });

        expect(window.location.href).toBe('/x/claim/ABC123/redirect');
    });

    it('allows https redirect URLs', async () => {
        const runtime = useRiderRuntimeActions();

        await runtime.execute({
            type: 'redirect',
            payload: {
                url: 'https://example.com/success',
            },
        });

        expect(window.location.href).toBe('https://example.com/success');
    });

    it('blocks javascript redirect URLs', async () => {
        const runtime = useRiderRuntimeActions();

        await runtime.execute({
            type: 'redirect',
            payload: {
                url: 'javascript:alert(1)',
            },
        });

        expect(window.location.href).toBe('');
    });

    it('blocks data redirect URLs', async () => {
        const runtime = useRiderRuntimeActions();

        await runtime.execute({
            type: 'redirect',
            payload: {
                url: 'data:text/html,<script>alert(1)</script>',
            },
        });

        expect(window.location.href).toBe('');
    });

    it('allows https open_url actions', async () => {
        const runtime = useRiderRuntimeActions();

        await runtime.execute({
            type: 'open_url',
            payload: {
                url: 'https://example.com/reward',
                target: '_blank',
            },
        });

        expect(window.open).toHaveBeenCalledWith(
            'https://example.com/reward',
            '_blank',
            'noopener,noreferrer'
        );
    });

    it('blocks unsafe open_url actions', async () => {
        const runtime = useRiderRuntimeActions();

        await runtime.execute({
            type: 'open_url',
            payload: {
                url: 'javascript:alert(1)',
                target: '_blank',
            },
        });

        expect(window.open).not.toHaveBeenCalled();
    });

    it('does not execute gesture-required actions without user gesture', async () => {
        const runtime = useRiderRuntimeActions({
            userGesture: false,
        });

        await runtime.execute({
            type: 'open_url',
            requires_user_gesture: true,
            payload: {
                url: 'https://example.com/reward',
            },
        });

        expect(window.open).not.toHaveBeenCalled();
    });

    it('executes gesture-required actions when user gesture is present', async () => {
        const runtime = useRiderRuntimeActions({
            userGesture: true,
        });

        await runtime.execute({
            type: 'open_url',
            requires_user_gesture: true,
            payload: {
                url: 'https://example.com/reward',
            },
        });

        expect(window.open).toHaveBeenCalledWith(
            'https://example.com/reward',
            '_blank',
            'noopener,noreferrer'
        );
    });

    it('does not execute disabled actions', async () => {
        const runtime = useRiderRuntimeActions();

        await runtime.execute({
            type: 'open_url',
            enabled: false,
            payload: {
                url: 'https://example.com/reward',
            },
        });

        expect(window.open).not.toHaveBeenCalled();
    });
});
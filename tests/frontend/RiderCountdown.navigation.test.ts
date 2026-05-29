import { mount } from '@vue/test-utils';
import { afterEach, beforeEach, describe, expect, it, vi } from 'vitest';
import RiderCountdown from '../../resources/js/components/x-rider/RiderCountdown.vue';

vi.mock('@inertiajs/vue3', () => ({
    router: {
        visit: vi.fn(),
    },
}));

vi.mock('@/components/ui/button', () => ({
    Button: {
        name: 'Button',
        template: '<button><slot /></button>',
    },
}));

describe('RiderCountdown navigation', () => {
    beforeEach(() => {
        vi.useFakeTimers();
    });

    afterEach(() => {
        vi.useRealTimers();
        vi.restoreAllMocks();
    });

    it('navigates to redirectEndpoint when countdown completes', async () => {
        const originalLocation = window.location;

        delete (window as any).location;

        (window as any).location = {
            ...originalLocation,
            href: '',
        };

        mount(RiderCountdown, {
            props: {
                redirect: {
                    enabled: true,
                    url: 'https://example.com/raw-rider-url',
                    timeout: 2,
                    delay_seconds: 2,
                },
                redirectEndpoint: '/x/claim/TEST123/redirect',
            },
        });

        await vi.advanceTimersByTimeAsync(3000);

        expect(window.location.href).toBe('/x/claim/TEST123/redirect');
        expect(window.location.href).not.toBe('https://example.com/raw-rider-url');

        window.location = originalLocation;
    });
});
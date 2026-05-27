import { afterEach, beforeEach, describe, expect, it, vi } from 'vitest';
import { mount } from '@vue/test-utils';
import RiderRuntimeSequencer from '../../resources/js/components/x-rider/RiderRuntimeSequencer.vue';

vi.mock('../../resources/js/components/x-rider/RiderStagePresenter.vue', () => ({
    default: {
        props: ['stage'],
        template: '<div data-test="stage">{{ stage.key }}</div>',
    },
}));

describe('RiderRuntimeSequencer redirect runtime', () => {
    const originalLocation = window.location;

    beforeEach(() => {
        vi.useFakeTimers();

        Object.defineProperty(window, 'location', {
            configurable: true,
            value: {
                href: '',
            },
        });
    });

    afterEach(() => {
        vi.useRealTimers();

        Object.defineProperty(window, 'location', {
            configurable: true,
            value: originalLocation,
        });

        vi.restoreAllMocks();
    });

    it('delays legacy redirect by payload timeout seconds', async () => {
        mount(RiderRuntimeSequencer, {
            props: {
                stages: [
                    {
                        type: 'redirect',
                        key: 'legacy-redirect',
                        phase: 'redirect',
                        payload: {
                            url: 'https://example.com/success',
                            timeout: 8,
                        },
                    },
                ],
            },
        });

        await vi.advanceTimersByTimeAsync(7999);
        expect(window.location.href).toBe('');

        await vi.advanceTimersByTimeAsync(1);
        expect(window.location.href).toBe('https://example.com/success');
    });

    it('prefers redirectEndpoint prop over stage payload url', async () => {
        mount(RiderRuntimeSequencer, {
            props: {
                redirectEndpoint: '/x/claim/ABC123/redirect',
                stages: [
                    {
                        type: 'redirect',
                        key: 'legacy-redirect',
                        phase: 'redirect',
                        payload: {
                            url: 'https://example.com/success',
                            timeout: 1,
                        },
                    },
                ],
            },
        });

        await vi.advanceTimersByTimeAsync(1000);

        expect(window.location.href).toBe('/x/claim/ABC123/redirect');
    });

    it('does not redirect twice after rerender', async () => {
        const stages = [
            {
                type: 'redirect',
                key: 'legacy-redirect',
                phase: 'redirect',
                payload: {
                    url: 'https://example.com/success',
                    timeout: 1,
                },
            },
        ];

        const wrapper = mount(RiderRuntimeSequencer, {
            props: { stages },
        });

        await vi.advanceTimersByTimeAsync(1000);

        expect(window.location.href).toBe('https://example.com/success');

        window.location.href = '';

        await wrapper.setProps({ stages: [...stages] });
        await wrapper.vm.$nextTick();

        await vi.advanceTimersByTimeAsync(1000);

        expect(window.location.href).toBe('');
    });

    it('does not redirect unsafe legacy redirect urls', async () => {
        mount(RiderRuntimeSequencer, {
            props: {
                stages: [
                    {
                        type: 'redirect',
                        key: 'unsafe-redirect',
                        phase: 'redirect',
                        payload: {
                            url: 'javascript:alert(1)',
                            timeout: 1,
                        },
                    },
                ],
            },
        });

        await vi.advanceTimersByTimeAsync(1000);

        expect(window.location.href).toBe('');
    });
});
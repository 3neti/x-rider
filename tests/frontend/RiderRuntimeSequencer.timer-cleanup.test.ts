import { describe, expect, it, vi, afterEach } from 'vitest';
import { mount } from '@vue/test-utils';
import RiderRuntimeSequencer from '../../resources/js/components/x-rider/RiderRuntimeSequencer.vue';
import RiderStagePresenter from '../../resources/js/components/x-rider/RiderStagePresenter.vue';

describe('RiderRuntimeSequencer timer cleanup', () => {
    afterEach(() => {
        vi.useRealTimers();
        vi.restoreAllMocks();
    });

    it('does not execute delayed actions after unmount', async () => {
        vi.useFakeTimers();

        const assign = vi.fn();
        Object.defineProperty(window, 'location', {
            value: {
                href: '',
                assign,
            },
            writable: true,
        });

        const wrapper = mount(RiderRuntimeSequencer, {
            props: {
                stages: [
                    {
                        type: 'redirect',
                        key: 'delayed-redirect',
                        payload: {
                            url: 'https://example.com/success',
                            timeout: 5,
                        },
                    },
                ],
            },
        });

        await wrapper.vm.$nextTick();

        wrapper.unmount();

        await vi.advanceTimersByTimeAsync(5000);

        expect(window.location.href).toBe('');
        expect(assign).not.toHaveBeenCalled();
    });

    it('does not duplicate delayed redirect timers after rerender', async () => {
        vi.useFakeTimers();

        const wrapper = mount(RiderRuntimeSequencer, {
            props: {
                stages: [
                    {
                        type: 'redirect',
                        key: 'single-delay',
                        payload: {
                            url: 'https://example.com/success',
                            timeout: 2,
                        },
                    },
                ],
            },
        });

        await wrapper.vm.$nextTick();

        await wrapper.setProps({
            stages: [
                {
                    type: 'redirect',
                    key: 'single-delay',
                    payload: {
                        url: 'https://example.com/success',
                        timeout: 2,
                    },
                },
            ],
        });

        await wrapper.vm.$nextTick();

        await vi.advanceTimersByTimeAsync(2000);

        expect(window.location.href).toBe('https://example.com/success');

        window.location.href = '';

        await vi.advanceTimersByTimeAsync(2000);

        expect(window.location.href).toBe('');
    });

    it('cleans up presenter countdown timers on unmount', async () => {
        vi.useFakeTimers();

        const clearIntervalSpy = vi.spyOn(window, 'clearInterval');

        const wrapper = mount(RiderStagePresenter, {
            props: {
                stage: {
                    type: 'redirect',
                    key: 'countdown-cleanup',
                    content: 'Redirecting safely',
                    payload: {
                        timeout: 5,
                    },
                },
            },
        });

        await wrapper.vm.$nextTick();

        wrapper.unmount();

        expect(clearIntervalSpy).toHaveBeenCalled();
    });
});
import { describe, expect, it, vi, afterEach } from 'vitest';
import { mount } from '@vue/test-utils';
import RiderRuntimeSequencer from '../../resources/js/components/x-rider/RiderRuntimeSequencer.vue';

describe('RiderRuntimeSequencer redirect failure survivability', () => {
    afterEach(() => {
        vi.restoreAllMocks();
        vi.useRealTimers();
    });

    it('survives redirect execution without breaking visible stages', async () => {
        vi.useFakeTimers();

        const wrapper = mount(RiderRuntimeSequencer, {
            props: {
                stages: [
                    {
                        type: 'redirect',
                        key: 'redirect-stage',
                        payload: {
                            url: 'https://example.com/success',
                            timeout: 1,
                        },
                    },
                    {
                        type: 'splash',
                        key: 'surviving-stage',
                        content: 'Runtime still alive',
                    },
                ],
            },
        });

        await wrapper.vm.$nextTick();
        await vi.advanceTimersByTimeAsync(1000);

        expect(wrapper.text()).toContain('Runtime still alive');
    });

    it('does not replay redirect in a way that breaks rerendered runtime', async () => {
        vi.useFakeTimers();

        const stages = [
            {
                type: 'redirect',
                key: 'redirect-once',
                payload: {
                    url: 'https://example.com/success',
                    timeout: 1,
                },
            },
            {
                type: 'splash',
                key: 'after-redirect',
                content: 'Still rendered after redirect rerender',
            },
        ];

        const wrapper = mount(RiderRuntimeSequencer, {
            props: { stages },
        });

        await wrapper.vm.$nextTick();
        await vi.advanceTimersByTimeAsync(1000);

        await wrapper.setProps({ stages: [...stages] });
        await wrapper.vm.$nextTick();
        await vi.advanceTimersByTimeAsync(1000);

        expect(wrapper.text()).toContain('Still rendered after redirect rerender');
    });

    it('safely ignores malformed redirect payloads', async () => {
        vi.useFakeTimers();

        const wrapper = mount(RiderRuntimeSequencer, {
            props: {
                stages: [
                    {
                        type: 'redirect',
                        key: 'bad-redirect',
                        payload: null,
                    },
                    {
                        type: 'splash',
                        key: 'after-bad-redirect',
                        content: 'Bad redirect did not break runtime',
                    },
                ] as any,
            },
        });

        await wrapper.vm.$nextTick();
        await vi.advanceTimersByTimeAsync(1000);

        expect(wrapper.text()).toContain('Bad redirect did not break runtime');
    });
});
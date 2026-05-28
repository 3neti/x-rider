import { describe, expect, it, vi } from 'vitest';
import { mount } from '@vue/test-utils';
import RiderRuntimeSequencer from '../../resources/js/components/x-rider/RiderRuntimeSequencer.vue';

describe('RiderRuntimeSequencer resilience', () => {
    it('ignores malformed disabled stages safely', () => {
        const wrapper = mount(RiderRuntimeSequencer, {
            props: {
                stages: [
                    null,
                    undefined,
                    false,
                    {
                        type: 'splash',
                        key: 'valid-stage',
                        content: 'Valid stage still renders',
                    },
                ] as any,
            },
        });

        expect(wrapper.text()).toContain('Valid stage still renders');
    });

    it('survives malformed action payloads without breaking visible stages', async () => {
        const warn = vi.spyOn(console, 'warn').mockImplementation(() => {});

        const wrapper = mount(RiderRuntimeSequencer, {
            props: {
                stages: [
                    {
                        type: 'splash',
                        key: 'stage-with-bad-actions',
                        content: 'Runtime survives bad actions',
                        actions: [
                            null,
                            {},
                            {
                                type: 'redirect',
                                timing: 'on_mount',
                                payload: null,
                            },
                            {
                                type: 'copy_to_clipboard',
                                timing: 'bad_timing',
                                payload: {},
                            },
                        ],
                    },
                ] as any,
            },
        });

        await wrapper.vm.$nextTick();

        expect(wrapper.text()).toContain('Runtime survives bad actions');

        warn.mockRestore();
    });

    it('survives stages with missing payload fields', () => {
        const wrapper = mount(RiderRuntimeSequencer, {
            props: {
                stages: [
                    {
                        type: 'image',
                        key: 'missing-image-src',
                        payload: {},
                    },
                    {
                        type: 'link',
                        key: 'missing-link-url',
                        payload: {},
                    },
                    {
                        type: 'splash',
                        key: 'safe-fallback-stage',
                        payload: {
                            content: 'Fallback content renders',
                        },
                    },
                ] as any,
            },
        });

        expect(wrapper.text()).toContain('Fallback content renders');
    });

    it('does not crash on unknown stage types', () => {
        const wrapper = mount(RiderRuntimeSequencer, {
            props: {
                stages: [
                    {
                        type: 'unknown_runtime_stage',
                        key: 'unknown-stage',
                        payload: {
                            content: 'Unknown stage content',
                        },
                    },
                    {
                        type: 'splash',
                        key: 'known-stage',
                        content: 'Known stage renders',
                    },
                ] as any,
            },
        });

        expect(wrapper.text()).toContain('Known stage renders');
    });
});
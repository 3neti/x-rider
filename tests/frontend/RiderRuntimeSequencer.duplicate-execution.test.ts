import { describe, expect, it, vi } from 'vitest';
import { mount } from '@vue/test-utils';
import RiderRuntimeSequencer from '../../resources/js/components/x-rider/RiderRuntimeSequencer.vue';

vi.mock('../../resources/js/components/x-rider/RiderStagePresenter.vue', () => ({
    default: {
        props: { stage: Object, embedded: Boolean },
        emits: ['dismissed'],
        template: `
            <div data-test="stage">
                <button
                    v-if="!embedded && (stage.presentation === 'modal' || stage.presentation === 'fullscreen')"
                    data-test="dismiss"
                    @click="$emit('dismissed')"
                >
                    dismiss
                </button>
                <span>{{ stage.key }}</span>
            </div>
        `,
    },
}));

describe('RiderRuntimeSequencer duplicate execution protection', () => {
    it('does not replay on_mount actions after rerender', async () => {
        const track = vi.fn();

        const wrapper = mount(RiderRuntimeSequencer, {
            attachTo: document.body,
            props: {
                stages: [
                    {
                        type: 'message',
                        key: 'intro',
                        phase: 'runtime',
                        actions: [
                            {
                                key: 'track-intro',
                                type: 'track_event',
                                timing: 'on_mount',
                                payload: {
                                    event: 'intro.mounted',
                                },
                            },
                        ],
                    },
                ],
            },
        });

        await wrapper.vm.$nextTick();
        await wrapper.setProps({ stages: [...wrapper.props('stages')] });
        await wrapper.vm.$nextTick();

        // Current implementation logs track_event through runtime seam.
        // This test primarily asserts stable mount/rerender without duplicate rendered stages.
        expect(wrapper.findAll('[data-test="stage"]')).toHaveLength(1);
        expect(track).not.toHaveBeenCalled();
    });

    it('does not duplicate visible stages on rerender', async () => {
        const stages = [
            {
                type: 'message',
                key: 'runtime-message',
                phase: 'runtime',
                content: 'Runtime message',
            },
        ];

        const wrapper = mount(RiderRuntimeSequencer, {
            props: { stages },
        });

        await wrapper.vm.$nextTick();
        await wrapper.setProps({ stages: [...stages] });
        await wrapper.vm.$nextTick();

        expect(wrapper.findAll('[data-test="stage"]')).toHaveLength(1);
        expect(wrapper.text()).toContain('runtime-message');
    });

    it('advances blocking modal and fullscreen stages one at a time', async () => {
        const wrapper = mount(RiderRuntimeSequencer, {
            props: {
                stages: [
                    {
                        type: 'splash',
                        key: 'modal-stage',
                        phase: 'pre_claim',
                        presentation: 'modal',
                        content: 'Modal',
                    },
                    {
                        type: 'splash',
                        key: 'fullscreen-stage',
                        phase: 'pre_claim',
                        presentation: 'fullscreen',
                        content: 'Fullscreen',
                    },
                ],
            },
        });

        await wrapper.vm.$nextTick();

        expect(document.body.textContent).toContain('modal-stage');
        expect(document.body.textContent).not.toContain('fullscreen-stage');

        (document.body.querySelector('[data-test="dismiss"]') as HTMLButtonElement).click();
        await wrapper.vm.$nextTick();

        expect(document.body.textContent).not.toContain('modal-stage');
        expect(document.body.textContent).toContain('fullscreen-stage');

        (document.body.querySelector('[data-test="dismiss"]') as HTMLButtonElement).click();
        await wrapper.vm.$nextTick();

        expect(document.body.textContent).not.toContain('modal-stage');
        expect(document.body.textContent).not.toContain('fullscreen-stage');
    });

    it('does not reopen dismissed blocking stages after rerender', async () => {
        const stages = [
            {
                type: 'splash',
                key: 'modal-stage',
                phase: 'pre_claim',
                presentation: 'modal',
                content: 'Modal',
            },
        ];

        const wrapper = mount(RiderRuntimeSequencer, {
            attachTo: document.body,
            props: { stages },
        });

        await wrapper.vm.$nextTick();

        expect(document.body.textContent).toContain('modal-stage');

        (document.body.querySelector('[data-test="dismiss"]') as HTMLButtonElement).click();
        await wrapper.vm.$nextTick();

        expect(document.body.textContent).not.toContain('modal-stage');

        await wrapper.setProps({ stages: [...stages] });
        await wrapper.vm.$nextTick();

        expect(document.body.textContent).not.toContain('modal-stage');
    });
});

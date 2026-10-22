import { afterEach, describe, expect, it } from 'vitest';
import { mount, type VueWrapper } from '@vue/test-utils';
import RiderStagePresenter from '../../resources/js/components/x-rider/RiderStagePresenter.vue';
import RiderRuntimeSequencer from '../../resources/js/components/x-rider/RiderRuntimeSequencer.vue';
import RiderModalRuntime from '../../resources/js/components/x-rider/RiderModalRuntime.vue';
import RiderFullscreenRuntime from '../../resources/js/components/x-rider/RiderFullscreenRuntime.vue';

const mounted: VueWrapper[] = [];

afterEach(() => {
    mounted.forEach((wrapper) => wrapper.unmount());
    mounted.length = 0;
    document.body.innerHTML = '';
});

function splash(presentation: 'modal' | 'fullscreen') {
    return {
        type: 'splash',
        key: `${presentation}-splash`,
        presentation,
        content: 'Rider introduction',
        content_type: 'markdown',
        payload: { aria_label: 'Rider introduction' },
    };
}

describe('blocking rider actions', () => {
    it.each(['modal', 'fullscreen'] as const)(
        'renders one %s sequencer dialog and one Continue action',
        async (presentation) => {
            const wrapper = mount(RiderRuntimeSequencer, {
                attachTo: document.body,
                props: { stages: [splash(presentation)] },
            });
            mounted.push(wrapper);

            const dialog = document.body.querySelector('[role="dialog"]');
            expect(dialog?.parentElement).toBe(document.body);
            expect(document.body.querySelectorAll('[role="dialog"]')).toHaveLength(1);
            expect(dialog?.querySelectorAll('button')).toHaveLength(1);
            expect(dialog?.getAttribute('aria-label')).toBe('Rider introduction');

            (dialog?.querySelector('button') as HTMLButtonElement).click();
            await wrapper.vm.$nextTick();

            expect(document.body.querySelectorAll('[role="dialog"]')).toHaveLength(0);
            await wrapper.setProps({ stages: [splash(presentation)] });
            expect(document.body.querySelectorAll('[role="dialog"]')).toHaveLength(0);
        },
    );

    it.each([
        ['modal', RiderModalRuntime],
        ['fullscreen', RiderFullscreenRuntime],
    ] as const)('renders one %s runtime dialog and one Continue action', async (presentation, component) => {
        const wrapper = mount(component, {
            attachTo: document.body,
            props: { stages: [splash(presentation)] },
        });
        mounted.push(wrapper);

        expect(document.body.querySelectorAll('[role="dialog"]')).toHaveLength(1);
        expect(document.body.querySelectorAll('[role="dialog"] button')).toHaveLength(1);
        expect(document.body.querySelector('[role="dialog"]')?.getAttribute('aria-label')).toBe('Rider introduction');

        (document.body.querySelector('[role="dialog"] button') as HTMLButtonElement).click();
        await wrapper.vm.$nextTick();

        expect(document.body.querySelectorAll('[role="dialog"]')).toHaveLength(0);
    });

    it('preserves the standalone presenter dismissal', async () => {
        const wrapper = mount(RiderStagePresenter, {
            props: { stage: splash('fullscreen') },
        });
        mounted.push(wrapper);

        expect(wrapper.findAll('[role="dialog"]')).toHaveLength(1);
        expect(wrapper.findAll('button')).toHaveLength(1);

        await wrapper.get('button').trigger('click');

        expect(wrapper.emitted('dismissed')).toHaveLength(1);
        expect(wrapper.findAll('[role="dialog"]')).toHaveLength(0);
    });
});

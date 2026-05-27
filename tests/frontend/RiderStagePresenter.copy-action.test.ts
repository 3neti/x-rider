import { describe, expect, it, vi } from 'vitest';
import { mount } from '@vue/test-utils';
import RiderStagePresenter from '../../resources/js/components/x-rider/RiderStagePresenter.vue';

vi.mock('../../resources/js/components/x-rider/RiderRenderer.vue', () => ({
    default: {
        props: ['content'],
        template: '<div data-test="renderer">{{ content.content }}</div>',
    },
}));

describe('RiderStagePresenter copy action support', () => {
    it('renders a copy button for copy_to_clipboard actions', () => {
        const wrapper = mount(RiderStagePresenter, {
            props: {
                stage: {
                    type: 'message',
                    key: 'copy-stage',
                    actions: [
                        {
                            type: 'copy_to_clipboard',
                            timing: 'on_click',
                            payload: {
                                text: 'ABC123',
                                label: 'Copy Code',
                            },
                        },
                    ],
                },
            },
        });

        expect(wrapper.text()).toContain('Copy Code');
    });

    it('executes copy action on click', async () => {
        const writeText = vi.fn().mockResolvedValue(undefined);

        Object.assign(navigator, {
            clipboard: {
                writeText,
            },
        });

        const wrapper = mount(RiderStagePresenter, {
            props: {
                stage: {
                    type: 'message',
                    key: 'copy-stage',
                    actions: [
                        {
                            type: 'copy_to_clipboard',
                            timing: 'on_click',
                            payload: {
                                text: 'ABC123',
                                label: 'Copy Code',
                            },
                        },
                    ],
                },
            },
        });

        await wrapper.find('button').trigger('click');

        expect(writeText).toHaveBeenCalledWith('ABC123');
    });
});
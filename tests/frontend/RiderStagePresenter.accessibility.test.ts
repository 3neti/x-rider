import { describe, expect, it } from 'vitest';
import { mount } from '@vue/test-utils';
import RiderStagePresenter from '../../resources/js/components/x-rider/RiderStagePresenter.vue';

describe('RiderStagePresenter accessibility semantics', () => {
    it('adds dialog semantics for modal stages', () => {
        const wrapper = mount(RiderStagePresenter, {
            props: {
                stage: {
                    type: 'splash',
                    key: 'modal-stage',
                    presentation: 'modal',
                    content: 'Modal content',
                    content_type: 'markdown',
                    payload: {
                        aria_label: 'Important modal',
                    },
                },
            },
        });

        const dialog = wrapper.find('[role="dialog"]');

        expect(dialog.exists()).toBe(true);
        expect(dialog.attributes('aria-modal')).toBe('true');
        expect(dialog.attributes('aria-label')).toBe('Important modal');
    });

    it('adds dialog semantics for fullscreen stages', () => {
        const wrapper = mount(RiderStagePresenter, {
            props: {
                stage: {
                    type: 'splash',
                    key: 'fullscreen-stage',
                    presentation: 'fullscreen',
                    content: 'Fullscreen content',
                    content_type: 'markdown',
                },
            },
        });

        const dialog = wrapper.find('[role="dialog"]');

        expect(dialog.exists()).toBe(true);
        expect(dialog.attributes('aria-modal')).toBe('true');
        expect(dialog.attributes('aria-label')).toBe('fullscreen-stage');
    });

    it('does not add dialog semantics for inline stages', () => {
        const wrapper = mount(RiderStagePresenter, {
            props: {
                stage: {
                    type: 'splash',
                    key: 'inline-stage',
                    presentation: 'inline',
                    content: 'Inline content',
                    content_type: 'markdown',
                },
            },
        });

        expect(wrapper.find('[role="dialog"]').exists()).toBe(false);
    });

    it('marks redirect countdown as polite live region', () => {
        const wrapper = mount(RiderStagePresenter, {
            props: {
                stage: {
                    type: 'redirect',
                    key: 'redirect-stage',
                    payload: {
                        timeout: 5,
                    },
                },
            },
        });

        const liveRegion = wrapper.find('[aria-live="polite"]');

        expect(liveRegion.exists()).toBe(true);
        expect(liveRegion.text()).toContain('Redirecting in');
    });

    it('adds accessible label to copy action button', () => {
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
                                label: 'Copy Voucher Code',
                            },
                        },
                    ],
                },
            },
        });

        const button = wrapper.find('button');

        expect(button.exists()).toBe(true);
        expect(button.attributes('aria-label')).toBe('Copy Voucher Code');
    });
});
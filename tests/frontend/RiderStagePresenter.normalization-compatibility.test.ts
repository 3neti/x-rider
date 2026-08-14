import { describe, expect, it } from 'vitest';
import { mount } from '@vue/test-utils';
import RiderStagePresenter from '../../resources/js/components/x-rider/RiderStagePresenter.vue';

describe('RiderStagePresenter normalization compatibility', () => {
    it('renders top-level stage content', () => {
        const wrapper = mount(RiderStagePresenter, {
            props: {
                stage: {
                    type: 'splash',
                    key: 'top-level-content',
                    content: 'Top level content',
                    content_type: 'markdown',
                },
            },
        });

        expect(wrapper.text()).toContain('Top level content');
    });

    it('renders normalized payload content', () => {
        const wrapper = mount(RiderStagePresenter, {
            props: {
                stage: {
                    type: 'splash',
                    key: 'payload-content',
                    payload: {
                        content: 'Payload content',
                        content_type: 'markdown',
                    },
                },
            },
        });

        expect(wrapper.text()).toContain('Payload content');
    });

    it('renders sanitized normalized payload html content', () => {
        const wrapper = mount(RiderStagePresenter, {
            props: {
                stage: {
                    type: 'splash',
                    key: 'payload-html-content',
                    payload: {
                        content_type: 'html',
                        content: '<div><h2>Für Anaïs</h2><p>cushla machree</p></div>',
                    },
                    meta: {
                        sanitized: true,
                    },
                },
            },
        });

        expect(wrapper.html()).toContain('<h2>Für Anaïs</h2>');
        expect(wrapper.text()).toContain('cushla machree');
    });

    it('prefers top-level content over payload content', () => {
        const wrapper = mount(RiderStagePresenter, {
            props: {
                stage: {
                    type: 'splash',
                    key: 'mixed-content',
                    content: 'Top level wins',
                    content_type: 'markdown',
                    payload: {
                        content: 'Payload fallback',
                        content_type: 'html',
                    },
                },
            },
        });

        expect(wrapper.text()).toContain('Top level wins');
        expect(wrapper.text()).not.toContain('Payload fallback');
    });

    it('renders redirect payload content inside the redirect surface', async () => {
        const wrapper = mount(RiderStagePresenter, {
            props: {
                stage: {
                    type: 'redirect',
                    key: 'redirect-payload-content',
                    payload: {
                        timeout: 5,
                        content: 'Redirecting with payload content',
                        content_type: 'markdown',
                    },
                },
            },
        });

        await wrapper.vm.$nextTick();

        expect(wrapper.text()).toContain('Redirecting with payload content');
        expect(wrapper.text()).toContain('Redirecting in 5 seconds.');
    });

    it('falls back safely when content is missing', () => {
        const wrapper = mount(RiderStagePresenter, {
            props: {
                stage: {
                    type: 'splash',
                    key: 'missing-content',
                    payload: {},
                },
            },
        });

        expect(wrapper.exists()).toBe(true);
        expect(wrapper.text()).toBe('');
    });

    it('renders optional context code prominently for fullscreen stages', () => {
        const wrapper = mount(RiderStagePresenter, {
            props: {
                stage: {
                    type: 'splash',
                    key: 'payload-context-code',
                    presentation: 'fullscreen',
                    payload: {
                        context_label: 'Pay Code',
                        context_code: '86QD',
                        content: 'Please review this before continuing.',
                        content_type: 'markdown',
                    },
                },
            },
        });

        const context = wrapper.find('[data-testid="rider-stage-context-code"]');

        expect(context.exists()).toBe(true);
        expect(context.text()).toContain('Pay Code');
        expect(context.text()).toContain('86QD');
        expect(context.find('.text-6xl').exists()).toBe(true);
    });

    it('uses a smaller context code treatment for long references', () => {
        const wrapper = mount(RiderStagePresenter, {
            props: {
                stage: {
                    type: 'splash',
                    key: 'long-context-code',
                    presentation: 'fullscreen',
                    payload: {
                        context_code: 'LONG-PAY-CODE-REFERENCE-2026',
                    },
                },
            },
        });

        const context = wrapper.find('[data-testid="rider-stage-context-code"]');

        expect(context.exists()).toBe(true);
        expect(context.find('.text-2xl').exists()).toBe(true);
        expect(context.classes()).not.toContain('text-muted-foreground');
    });
});

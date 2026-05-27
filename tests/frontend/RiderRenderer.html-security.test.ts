import { describe, expect, it } from 'vitest';
import { mount } from '@vue/test-utils';
import RiderRenderer from '../../resources/js/components/x-rider/RiderRenderer.vue';
import RiderStagePresenter from "../../resources/js/components/x-rider/RiderStagePresenter.vue";

describe('RiderRenderer HTML security', () => {
    it('renders markdown/plain content as escaped text', () => {
        const wrapper = mount(RiderRenderer, {
            props: {
                content: {
                    enabled: true,
                    type: 'markdown',
                    content: '**Hello**',
                },
            },
        });

        expect(wrapper.text()).toContain('**Hello**');
    });

    it('does not render unsanitized html as html', () => {
        const wrapper = mount(RiderRenderer, {
            props: {
                content: {
                    enabled: true,
                    type: 'html',
                    content: '<strong>Hello</strong>',
                },
            },
        });

        expect(wrapper.html()).not.toContain('<strong>Hello</strong>');
        expect(wrapper.text()).toContain('<strong>Hello</strong>');
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

    it('renders trusted html as html', () => {
        const wrapper = mount(RiderRenderer, {
            props: {
                content: {
                    enabled: true,
                    type: 'html',
                    content: '<em>Trusted</em>',
                    meta: {
                        trusted_html: true,
                    },
                },
            },
        });

        expect(wrapper.html()).toContain('<em>Trusted</em>');
        expect(wrapper.text()).toContain('Trusted');
    });

    it('does not render disabled content', () => {
        const wrapper = mount(RiderRenderer, {
            props: {
                content: {
                    enabled: false,
                    type: 'html',
                    content: '<strong>Hello</strong>',
                    meta: {
                        sanitized: true,
                    },
                },
            },
        });

        expect(wrapper.text()).toBe('');
    });
});
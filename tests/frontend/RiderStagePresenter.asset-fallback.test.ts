import { describe, expect, it } from 'vitest';
import { mount } from '@vue/test-utils';
import RiderStagePresenter from '../../resources/js/components/x-rider/RiderStagePresenter.vue';

describe('RiderStagePresenter asset fallback', () => {
    it('renders fallback state when image fails to load', async () => {
        const wrapper = mount(RiderStagePresenter, {
            props: {
                stage: {
                    type: 'image',
                    key: 'broken-image',
                    payload: {
                        src: 'https://example.com/missing.jpg',
                        alt: 'Broken campaign image',
                    },
                },
            },
        });

        await wrapper.find('img').trigger('error');

        expect(wrapper.find('[data-test="image-fallback"]').exists()).toBe(true);
        expect(wrapper.text()).toContain('Unable to load image.');
    });

    it('preserves dismiss controls after fullscreen image failure', async () => {
        const wrapper = mount(RiderStagePresenter, {
            props: {
                stage: {
                    type: 'image',
                    key: 'fullscreen-broken-image',
                    presentation: 'fullscreen',
                    payload: {
                        src: 'https://example.com/missing.jpg',
                        alt: 'Fullscreen broken image',
                    },
                },
            },
        });

        await wrapper.find('img').trigger('error');

        expect(wrapper.find('[data-test="image-fallback"]').exists()).toBe(true);
        expect(wrapper.find('[data-test="dismiss"]').exists()).toBe(true);
    });

    it('preserves accessibility context during fallback rendering', async () => {
        const wrapper = mount(RiderStagePresenter, {
            props: {
                stage: {
                    type: 'image',
                    key: 'accessible-broken-image',
                    payload: {
                        src: 'https://example.com/missing.jpg',
                        alt: 'Accessible campaign image',
                    },
                },
            },
        });

        await wrapper.find('img').trigger('error');

        const fallback = wrapper.find('[data-test="image-fallback"]');

        expect(fallback.attributes('role')).toBe('img');
        expect(fallback.attributes('aria-label')).toBe('Accessible campaign image');
    });

    it('does not infinitely retry failed images', async () => {
        const wrapper = mount(RiderStagePresenter, {
            props: {
                stage: {
                    type: 'image',
                    key: 'retry-safe-image',
                    payload: {
                        src: 'https://example.com/missing.jpg',
                        alt: 'Retry safe image',
                    },
                },
            },
        });

        const image = wrapper.find('img');

        await image.trigger('error');
        await wrapper.vm.$nextTick();

        expect(wrapper.find('img').exists()).toBe(false);
        expect(wrapper.find('[data-test="image-fallback"]').exists()).toBe(true);
    });
});
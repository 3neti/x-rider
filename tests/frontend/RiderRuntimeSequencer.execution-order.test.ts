import { describe, expect, it, vi } from 'vitest';
import { mount } from '@vue/test-utils';
import RiderRuntimeSequencer from '../../resources/js/components/x-rider/RiderRuntimeSequencer.vue';

describe('RiderRuntimeSequencer execution order', () => {
    it('executes stage actions in mount delay complete order', async () => {
        const events: string[] = [];

        vi.spyOn(console, 'debug').mockImplementation((...args: unknown[]) => {
            if (args[0] === '[x-rider] runtime event') {
                events.push(String(args[1]));
            }
        });

        mount(RiderRuntimeSequencer, {
            props: {
                stages: [
                    {
                        type: 'message',
                        key: 'ordered-stage',
                        phase: 'runtime',
                        actions: [
                            {
                                key: 'mount-event',
                                type: 'track_event',
                                timing: 'on_mount',
                                payload: {
                                    event: 'mount-event',
                                    meta: { order: 1 },
                                },
                            },
                            {
                                key: 'delay',
                                type: 'delay',
                                timing: 'after_delay',
                                payload: {
                                    delay_ms: 1,
                                },
                            },
                            {
                                key: 'complete-event',
                                type: 'track_event',
                                timing: 'on_complete',
                                payload: {
                                    event: 'complete-event',
                                    meta: { order: 3 },
                                },
                            },
                        ],
                    },
                ],
            },
            global: {
                stubs: {
                    RiderStagePresenter: {
                        props: ['stage'],
                        template: '<div>{{ stage.key }}</div>',
                    },
                },
            },
        });

        await new Promise((resolve) => window.setTimeout(resolve, 5));

        expect(events).toEqual([
            'mount-event',
            'complete-event',
        ]);
    });

    it('executes stages sequentially', async () => {
        const events: string[] = [];

        vi.spyOn(console, 'debug').mockImplementation((...args: unknown[]) => {
            if (args[0] === '[x-rider] runtime event') {
                events.push(String(args[1]));
            }
        });

        mount(RiderRuntimeSequencer, {
            props: {
                stages: [
                    {
                        type: 'message',
                        key: 'first-stage',
                        phase: 'runtime',
                        actions: [
                            {
                                key: 'first-event',
                                type: 'track_event',
                                timing: 'on_complete',
                                payload: {
                                    event: 'first-event',
                                },
                            },
                        ],
                    },
                    {
                        type: 'message',
                        key: 'second-stage',
                        phase: 'runtime',
                        actions: [
                            {
                                key: 'second-event',
                                type: 'track_event',
                                timing: 'on_complete',
                                payload: {
                                    event: 'second-event',
                                },
                            },
                        ],
                    },
                ],
            },
            global: {
                stubs: {
                    RiderStagePresenter: {
                        props: ['stage'],
                        template: '<div>{{ stage.key }}</div>',
                    },
                },
            },
        });

        await new Promise((resolve) => window.setTimeout(resolve, 5));

        expect(events).toEqual([
            'first-event',
            'second-event',
        ]);
    });

    it('does not execute disabled stage actions', async () => {
        const events: string[] = [];

        vi.spyOn(console, 'debug').mockImplementation((...args: unknown[]) => {
            if (args[0] === '[x-rider] runtime event') {
                events.push(String(args[1]));
            }
        });

        mount(RiderRuntimeSequencer, {
            props: {
                stages: [
                    {
                        type: 'message',
                        key: 'stage',
                        phase: 'runtime',
                        actions: [
                            {
                                key: 'disabled-event',
                                type: 'track_event',
                                timing: 'on_complete',
                                enabled: false,
                                payload: {
                                    event: 'disabled-event',
                                },
                            },
                            {
                                key: 'enabled-event',
                                type: 'track_event',
                                timing: 'on_complete',
                                payload: {
                                    event: 'enabled-event',
                                },
                            },
                        ],
                    },
                ],
            },
            global: {
                stubs: {
                    RiderStagePresenter: {
                        props: ['stage'],
                        template: '<div>{{ stage.key }}</div>',
                    },
                },
            },
        });

        await new Promise((resolve) => window.setTimeout(resolve, 5));

        expect(events).toEqual([
            'enabled-event',
        ]);
    });
});
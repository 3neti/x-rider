<?php

use LBHurtado\XRider\Data\RiderRuntimeActionData;
use LBHurtado\XRider\Data\RiderStageCollectionData;
use LBHurtado\XRider\Data\RiderStageData;
use LBHurtado\XRider\Enums\RiderStageType;

it('creates collection from stage arrays', function () {
    $collection = RiderStageCollectionData::fromArray([
        [
            'type' => 'message',
            'payload' => [
                'content' => 'Hello',
            ],
        ],
        [
            'type' => 'redirect',
            'payload' => [
                'url' => 'https://merchant.example.com',
            ],
        ],
    ]);

    expect($collection->stages)->toHaveCount(2)
        ->and($collection->stages[0])->toBeInstanceOf(RiderStageData::class)
        ->and($collection->stages[0]->normalizedType())->toBe('message')
        ->and($collection->stages[1]->normalizedType())->toBe('redirect');
});

it('filters enabled stages', function () {
    $collection = new RiderStageCollectionData([
        new RiderStageData(type: RiderStageType::Message),
        new RiderStageData(type: RiderStageType::Image, enabled: false),
    ]);

    expect($collection->enabled())->toHaveCount(1)
        ->and($collection->enabled()[0]->normalizedType())->toBe('message');
});

it('filters renderable stages', function () {
    $collection = new RiderStageCollectionData([
        new RiderStageData(type: RiderStageType::Message),
        new RiderStageData(type: RiderStageType::Redirect),
        new RiderStageData(type: RiderStageType::Image),
    ]);

    expect($collection->renderable())->toHaveCount(2);
});

it('filters redirect-like stages', function () {
    $collection = new RiderStageCollectionData([
        new RiderStageData(type: RiderStageType::Message),
        new RiderStageData(type: RiderStageType::Redirect),
    ]);

    expect($collection->redirectLike())->toHaveCount(1)
        ->and($collection->redirectLike()[0]->normalizedType())->toBe('redirect');
});

it('finds the first stage of a type', function () {
    $collection = new RiderStageCollectionData([
        new RiderStageData(type: RiderStageType::Message, key: 'message-1'),
        new RiderStageData(type: RiderStageType::Message, key: 'message-2'),
    ]);

    expect($collection->firstOfType('message')?->key)->toBe('message-1');
});

it('normalizes runtime actions for stages inside a collection', function () {
    $collection = RiderStageCollectionData::fromArray([
        'stages' => [
            [
                'type' => 'splash',
                'key' => 'intro',
                'content' => 'Welcome',
            ],
            [
                'type' => 'cta',
                'key' => 'open-reward',
                'payload' => [
                    'label' => 'Open Reward',
                    'url' => 'https://example.com/reward',
                ],
                'actions' => [
                    [
                        'type' => 'open_url',
                        'timing' => 'on_click',
                        'requires_user_gesture' => true,
                        'payload' => [
                            'url' => 'https://example.com/reward',
                            'target' => '_blank',
                        ],
                    ],
                ],
            ],
        ],
    ]);

    expect($collection->stages)->toHaveCount(2)
        ->and($collection->stages[1]->actions)->toHaveCount(1)
        ->and($collection->stages[1]->actions[0])->toBeInstanceOf(RiderRuntimeActionData::class)
        ->and($collection->stages[1]->actions[0]->type)->toBe('open_url')
        ->and($collection->stages[1]->actions[0]->timing)->toBe('on_click')
        ->and($collection->stages[1]->actions[0]->requires_user_gesture)->toBeTrue()
        ->and($collection->stages[1]->actions[0]->payload['url'])->toBe('https://example.com/reward');
});

it('carries normalized runtime actions through stage collections', function () {
    $collection = RiderStageCollectionData::fromArray([
        'stages' => [
            [
                'type' => 'cta',
                'key' => 'demo-cta',
                'actions' => [
                    [
                        'type' => 'open_url',
                        'timing' => 'on_click',
                        'payload' => [
                            'url' => 'https://example.com/reward',
                        ],
                    ],
                ],
            ],
        ],
        'meta' => [
            'source' => 'test',
        ],
    ]);

    expect($collection->stages)->toHaveCount(1)
        ->and($collection->meta)->toBe(['source' => 'test'])
        ->and($collection->stages[0]->normalizedType())->toBe('cta')
        ->and($collection->stages[0]->key)->toBe('demo-cta')
        ->and($collection->stages[0]->actions)->toHaveCount(1)
        ->and($collection->stages[0]->actions[0])->toBeInstanceOf(RiderRuntimeActionData::class)
        ->and($collection->stages[0]->actions[0]->type)->toBe('open_url')
        ->and($collection->stages[0]->actions[0]->timing)->toBe('on_click')
        ->and($collection->stages[0]->actions[0]->payload)->toMatchArray([
            'url' => 'https://example.com/reward',
            'target' => '_blank',
            'label' => null,
            'meta' => [],
        ]);
});

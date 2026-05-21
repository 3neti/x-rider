<?php

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
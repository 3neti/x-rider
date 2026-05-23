<?php

use LBHurtado\XRider\Enums\RiderStageType;
use LBHurtado\XRider\StageDrivers\LinkStageDriver;

it('creates a link stage from payload', function () {
    $stage = (new LinkStageDriver())->make([
        'key' => 'learn-more',
        'payload' => [
            'label' => 'Learn more',
            'url' => 'https://example.com',
        ],
    ]);

    expect($stage->normalizedType())->toBe(RiderStageType::Link->value)
        ->and($stage->enabled)->toBeTrue()
        ->and($stage->key)->toBe('learn-more')
        ->and($stage->payload['label'])->toBe('Learn more')
        ->and($stage->payload['url'])->toBe('https://example.com')
        ->and($stage->payload['presentation'])->toBe('inline');
});

it('creates a disabled link stage when url is missing', function () {
    $stage = (new LinkStageDriver())->make([
        'payload' => [
            'label' => 'Learn more',
        ],
    ]);

    expect($stage->enabled)->toBeFalse()
        ->and($stage->payload['label'])->toBe('Learn more')
        ->and($stage->payload)->not->toHaveKey('url');
});

it('supports top-level link fields for simple yaml', function () {
    $stage = (new LinkStageDriver())->make([
        'label' => 'Open promo',
        'url' => 'https://example.com/promo',
        'presentation' => 'inline',
    ]);

    expect($stage->enabled)->toBeTrue()
        ->and($stage->payload['label'])->toBe('Open promo')
        ->and($stage->payload['url'])->toBe('https://example.com/promo')
        ->and($stage->payload['presentation'])->toBe('inline');
});
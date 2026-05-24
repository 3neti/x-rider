<?php

use LBHurtado\XRider\Enums\RiderStageType;
use LBHurtado\XRider\StageDrivers\RedirectStageDriver;

it('creates a redirect stage from url', function () {
    $stage = (new RedirectStageDriver())->make([
        'url' => 'https://merchant.example.com',
        'timeout' => 7,
        'fallback_url' => '/fallback',
    ]);

    expect($stage->normalizedType())->toBe(RiderStageType::Redirect->value)
        ->and($stage->enabled)->toBeTrue()
        ->and($stage->payload['url'])->toBe('https://merchant.example.com')
        ->and($stage->payload['timeout'])->toBe(7)
        ->and($stage->payload['fallback_url'])->toBe('/fallback');
});

it('creates a disabled redirect stage when url is missing', function () {
    $stage = (new RedirectStageDriver())->make();

    expect($stage->enabled)->toBeFalse()
        ->and($stage->payload['url'])->toBeNull();
});

it('supports legacy redirect timeout from context', function () {
    $stage = (new RedirectStageDriver())->make(
        config: [
            'url' => 'https://merchant.example.com',
        ],
        context: [
            'redirect_timeout' => 3,
        ],
    );

    expect($stage->payload['timeout'])->toBe(3);
});

it('creates redirect payload for runtime execution', function () {
    $stage = (new RedirectStageDriver())->make([
        'payload' => [
            'url' => 'https://example.com/success',
            'timeout' => 5,
            'external' => true,
        ],
    ]);

    expect($stage->payload['url'])
        ->toBe('https://example.com/success')
        ->and($stage->payload['timeout'])
        ->toBe(5)
        ->and($stage->payload['external'])
        ->toBeTrue();
});
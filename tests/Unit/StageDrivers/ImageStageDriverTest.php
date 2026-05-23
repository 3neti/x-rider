<?php

use LBHurtado\XRider\Enums\RiderStageType;
use LBHurtado\XRider\StageDrivers\ImageStageDriver;

it('creates an image stage from top level config', function () {
    $stage = (new ImageStageDriver())->make([
        'key' => 'demo-image',
        'src' => 'https://example.com/banner.png',
        'alt' => 'Demo banner',
    ]);

    expect($stage->normalizedType())->toBe(RiderStageType::Image->value)
        ->and($stage->enabled)->toBeTrue()
        ->and($stage->key)->toBe('demo-image')
        ->and($stage->payload['src'])->toBe('https://example.com/banner.png')
        ->and($stage->payload['alt'])->toBe('Demo banner')
        ->and($stage->payload['presentation'])->toBe('inline');
});

it('creates an image stage from payload config', function () {
    $stage = (new ImageStageDriver())->make([
        'payload' => [
            'src' => 'https://example.com/banner.png',
            'alt' => 'Demo banner',
            'presentation' => 'fullscreen',
        ],
    ]);

    expect($stage->enabled)->toBeTrue()
        ->and($stage->payload['src'])->toBe('https://example.com/banner.png')
        ->and($stage->payload['presentation'])->toBe('fullscreen');
});

it('creates a disabled image stage when src is missing', function () {
    $stage = (new ImageStageDriver())->make([
        'alt' => 'Missing image',
    ]);

    expect($stage->enabled)->toBeFalse()
        ->and($stage->payload)->not->toHaveKey('src');
});
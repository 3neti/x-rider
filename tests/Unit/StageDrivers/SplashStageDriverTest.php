<?php

use LBHurtado\XRider\Enums\RiderStageType;
use LBHurtado\XRider\StageDrivers\SplashStageDriver;

it('creates a splash stage from content', function () {
    $stage = (new SplashStageDriver())->make([
        'content' => 'Welcome!',
        'timeout' => 2,
    ]);

    expect($stage->normalizedType())->toBe(RiderStageType::Splash->value)
        ->and($stage->enabled)->toBeTrue()
        ->and($stage->payload['content'])->toBe('Welcome!')
        ->and($stage->payload['timeout'])->toBe(2);
});

it('supports legacy splash key', function () {
    $stage = (new SplashStageDriver())->make([
        'splash' => 'Legacy splash.',
    ]);

    expect($stage->enabled)->toBeTrue()
        ->and($stage->payload['content'])->toBe('Legacy splash.');
});

it('creates a disabled splash stage when no content exists', function () {
    $stage = (new SplashStageDriver())->make();

    expect($stage->enabled)->toBeFalse()
        ->and($stage->payload['content'])->toBeNull();
});
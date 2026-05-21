<?php

use LBHurtado\XRider\Data\RiderStageData;
use LBHurtado\XRider\Enums\RiderStageType;

it('normalizes enum stage type', function () {
    $stage = new RiderStageData(
        type: RiderStageType::Message,
    );

    expect($stage->normalizedType())->toBe('message')
        ->and($stage->typeEnum())->toBe(RiderStageType::Message);
});

it('normalizes string stage type', function () {
    $stage = new RiderStageData(
        type: 'redirect',
    );

    expect($stage->normalizedType())->toBe('redirect')
        ->and($stage->typeEnum())->toBe(RiderStageType::Redirect);
});

it('marks message stage as renderable', function () {
    $stage = new RiderStageData(
        type: RiderStageType::Message,
    );

    expect($stage->isRenderable())->toBeTrue()
        ->and($stage->isRedirectLike())->toBeFalse();
});

it('marks redirect stage as redirect-like but not renderable', function () {
    $stage = new RiderStageData(
        type: RiderStageType::Redirect,
    );

    expect($stage->isRedirectLike())->toBeTrue()
        ->and($stage->isRenderable())->toBeFalse();
});

it('does not render disabled stages', function () {
    $stage = new RiderStageData(
        type: RiderStageType::Message,
        enabled: false,
    );

    expect($stage->isRenderable())->toBeFalse();
});

it('safely handles unknown stage types', function () {
    $stage = new RiderStageData(
        type: 'future-stage',
    );

    expect($stage->normalizedType())->toBe('future-stage')
        ->and($stage->typeEnum())->toBeNull()
        ->and($stage->isRenderable())->toBeFalse()
        ->and($stage->isRedirectLike())->toBeFalse();
});
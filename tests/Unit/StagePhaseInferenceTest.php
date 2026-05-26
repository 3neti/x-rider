<?php

use LBHurtado\XRider\Data\RiderStageData;
use LBHurtado\XRider\Enums\RiderStageType;

function inferTestPhase(RiderStageData $stage): string
{
    $payload = $stage->payload;

    if (isset($payload['phase']) && in_array($payload['phase'], [
            'pre_claim',
            'runtime',
            'success',
            'redirect',
            'post_claim',
        ], true)) {
        return $payload['phase'];
    }

    $presentation = $payload['presentation'] ?? 'inline';

    if ($stage->normalizedType() === 'redirect') {
        return 'redirect';
    }

    if (in_array($presentation, ['modal', 'fullscreen'], true)) {
        return 'runtime';
    }

    if ($stage->normalizedType() === 'message') {
        return 'success';
    }

    return 'pre_claim';
}

it('infers inline splash as pre claim', function () {
    $stage = new RiderStageData(
        type: RiderStageType::Splash,
        enabled: true,
        key: 'inline-splash',
        payload: [
            'presentation' => 'inline',
        ],
    );

    expect(inferTestPhase($stage))->toBe('pre_claim');
});

it('infers message as success', function () {
    $stage = new RiderStageData(
        type: RiderStageType::Message,
        enabled: true,
        key: 'success-message',
        payload: [],
    );

    expect(inferTestPhase($stage))->toBe('success');
});

it('infers modal splash as runtime', function () {
    $stage = new RiderStageData(
        type: RiderStageType::Splash,
        enabled: true,
        key: 'modal-splash',
        payload: [
            'presentation' => 'modal',
        ],
    );

    expect(inferTestPhase($stage))->toBe('runtime');
});

it('infers redirect as redirect', function () {
    $stage = new RiderStageData(
        type: RiderStageType::Redirect,
        enabled: true,
        key: 'redirect',
        payload: [],
    );

    expect(inferTestPhase($stage))->toBe('redirect');
});

it('allows explicit phase override', function () {
    $stage = new RiderStageData(
        type: RiderStageType::Image,
        enabled: true,
        key: 'success-image',
        payload: [
            'presentation' => 'inline',
            'phase' => 'success',
        ],
    );

    expect(inferTestPhase($stage))->toBe('success');
});
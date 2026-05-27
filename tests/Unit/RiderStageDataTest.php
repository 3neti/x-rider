<?php

use LBHurtado\XRider\Data\RiderRuntimeActionData;
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

it('normalizes runtime actions on stage data', function () {
    $stage = RiderStageData::fromArray([
        'type' => 'cta',
        'key' => 'reward-cta',
        'actions' => [
            [
                'type' => 'open_url',
                'timing' => 'on_click',
                'requires_user_gesture' => true,
                'payload' => [
                    'url' => 'https://example.com/reward',
                ],
            ],
        ],
    ]);

    expect($stage->actions)->toHaveCount(1)
        ->and($stage->actions[0])->toBeInstanceOf(RiderRuntimeActionData::class)
        ->and($stage->actions[0]->type)->toBe('open_url')
        ->and($stage->actions[0]->timing)->toBe('on_click')
        ->and($stage->actions[0]->requires_user_gesture)->toBeTrue()
        ->and($stage->actions[0]->payload['url'])->toBe('https://example.com/reward');
});

it('carries normalized runtime actions with stage data', function () {
    $stage = RiderStageData::fromArray([
        'type' => 'cta',
        'key' => 'demo-cta',
        'phase' => 'pre_claim',
        'presentation' => 'inline',
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
    ]);

    expect($stage->normalizedType())->toBe('cta')
        ->and($stage->key)->toBe('demo-cta')
        ->and($stage->phase)->toBe('pre_claim')
        ->and($stage->presentation)->toBe('inline')
        ->and($stage->payload)->toMatchArray([
            'label' => 'Open Reward',
            'url' => 'https://example.com/reward',
        ])
        ->and($stage->actions)->toHaveCount(1)
        ->and($stage->actions[0])->toBeInstanceOf(RiderRuntimeActionData::class)
        ->and($stage->actions[0]->type)->toBe('open_url')
        ->and($stage->actions[0]->timing)->toBe('on_click')
        ->and($stage->actions[0]->payload)->toMatchArray([
            'url' => 'https://example.com/reward',
            'target' => '_blank',
            'label' => null,
            'meta' => [],
        ]);
});
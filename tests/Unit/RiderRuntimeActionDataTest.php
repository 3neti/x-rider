<?php

use LBHurtado\XRider\Data\RiderRuntimeActionData;

it('normalizes a valid open url runtime action', function () {
    $action = RiderRuntimeActionData::fromArray([
        'type' => 'open_url',
        'key' => 'open-reward',
        'timing' => 'on_click',
        'requires_user_gesture' => true,
        'external' => true,
        'payload' => [
            'url' => 'https://example.com/reward',
            'target' => '_blank',
            'label' => 'Open Reward',
            'meta' => [
                'source' => 'demo',
            ],
        ],
    ]);

    expect($action->type)->toBe('open_url')
        ->and($action->key)->toBe('open-reward')
        ->and($action->timing)->toBe('on_click')
        ->and($action->enabled)->toBeTrue()
        ->and($action->requires_user_gesture)->toBeTrue()
        ->and($action->external)->toBeTrue()
        ->and($action->payload['url'])->toBe('https://example.com/reward')
        ->and($action->payload['target'])->toBe('_blank')
        ->and($action->payload['label'])->toBe('Open Reward')
        ->and($action->payload['meta'])->toBe(['source' => 'demo'])
        ->and($action->isExecutable())->toBeTrue();
});

it('defaults invalid action type to a safe track event action', function () {
    $action = RiderRuntimeActionData::fromArray([
        'type' => 'dangerous_action',
        'timing' => 'on_click',
        'payload' => [
            'url' => 'javascript:alert(1)',
        ],
    ]);

    expect($action->type)->toBe('track_event')
        ->and($action->timing)->toBe('on_click')
        ->and($action->payload['event'])->toBe('rider.runtime.action')
        ->and($action->isExecutable())->toBeTrue();
});

it('defaults invalid timing to on click', function () {
    $action = RiderRuntimeActionData::fromArray([
        'type' => 'copy_to_clipboard',
        'timing' => 'whenever',
        'payload' => [
            'text' => 'ABC123',
        ],
    ]);

    expect($action->type)->toBe('copy_to_clipboard')
        ->and($action->timing)->toBe('on_click')
        ->and($action->payload['text'])->toBe('ABC123');
});

it('normalizes redirect payload safely', function () {
    $action = RiderRuntimeActionData::fromArray([
        'type' => 'redirect',
        'timing' => 'on_complete',
        'payload' => [
            'url' => '/x/claim/ABC123/redirect',
        ],
    ]);

    expect($action->type)->toBe('redirect')
        ->and($action->timing)->toBe('on_complete')
        ->and($action->payload)->toMatchArray([
            'url' => '/x/claim/ABC123/redirect',
            'target' => '_blank',
            'label' => null,
            'meta' => [],
        ]);
});

it('normalizes delay payload to non negative milliseconds', function () {
    $action = RiderRuntimeActionData::fromArray([
        'type' => 'delay',
        'timing' => 'on_complete',
        'payload' => [
            'delay_ms' => -500,
        ],
    ]);

    expect($action->payload['delay_ms'])->toBe(0);
});

it('normalizes show stage payload', function () {
    $action = RiderRuntimeActionData::fromArray([
        'type' => 'show_stage',
        'payload' => [
            'stage_key' => 'next-stage',
        ],
    ]);

    expect($action->type)->toBe('show_stage')
        ->and($action->payload['stage_key'])->toBe('next-stage')
        ->and($action->timing)->toBe('on_click');
});

it('normalizes close payload', function () {
    $action = RiderRuntimeActionData::fromArray([
        'type' => 'close',
        'payload' => [
            'ignored' => 'value',
        ],
    ]);

    expect($action->type)->toBe('close')
        ->and($action->payload)->toBe([
            'meta' => [],
        ]);
});

it('marks disabled actions as not executable', function () {
    $action = RiderRuntimeActionData::fromArray([
        'type' => 'open_url',
        'enabled' => false,
        'payload' => [
            'url' => 'https://example.com',
        ],
    ]);

    expect($action->enabled)->toBeFalse()
        ->and($action->isExecutable())->toBeFalse();
});
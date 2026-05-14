<?php

use LBHurtado\XRider\Data\RiderContentData;
use LBHurtado\XRider\Data\RiderExperienceData;
use LBHurtado\XRider\Data\RiderRedirectData;
use LBHurtado\XRider\Data\RiderSubjectData;
use LBHurtado\XRider\Enums\RiderOutcomeState;
use LBHurtado\XRider\Services\DefaultSuccessRedirectResolver;

function xRiderRedirectExperience(array $redirect): RiderExperienceData
{
    return new RiderExperienceData(
        state: RiderOutcomeState::AcceptedSuccess,
        subject: new RiderSubjectData(
            type: 'voucher',
            id: 'ABC123',
            code: 'ABC123',
            meta: [],
        ),
        success: new RiderContentData(content: 'OK'),
        redirect: new RiderRedirectData(...$redirect),
    );
}

it('returns fallback when redirect is disabled', function () {
    $resolver = new DefaultSuccessRedirectResolver;

    $experience = xRiderRedirectExperience([
        'enabled' => false,
        'url' => 'https://merchant.example.com',
        'fallbackUrl' => '/fallback',
    ]);

    expect($resolver->resolve($experience))->toBe('/fallback');
});

it('blocks unsafe javascript urls', function () {
    $resolver = new DefaultSuccessRedirectResolver;

    $experience = xRiderRedirectExperience([
        'enabled' => true,
        'url' => 'javascript:alert(1)',
        'fallbackUrl' => '/fallback',
    ]);

    expect($resolver->resolve($experience))->toBe('/fallback');
});

it('allows configured hosts', function () {
    config()->set('x-rider.redirects.allowed_hosts', ['merchant.example.com']);

    $resolver = new DefaultSuccessRedirectResolver;

    $experience = xRiderRedirectExperience([
        'enabled' => true,
        'url' => 'https://merchant.example.com/thank-you',
        'fallbackUrl' => '/fallback',
    ]);

    expect($resolver->resolve($experience))->toBe('https://merchant.example.com/thank-you');
});

it('blocks unconfigured hosts by default', function () {
    config()->set('x-rider.redirects.allowed_hosts', ['merchant.example.com']);

    $resolver = new DefaultSuccessRedirectResolver;

    $experience = xRiderRedirectExperience([
        'enabled' => true,
        'url' => 'https://evil.example.com',
        'fallbackUrl' => '/fallback',
    ]);

    expect($resolver->resolve($experience))->toBe('/fallback');
});

it('allows any host only when explicitly enabled', function () {
    config()->set('x-rider.redirects.allow_any_host', true);

    $resolver = new DefaultSuccessRedirectResolver;

    $experience = xRiderRedirectExperience([
        'enabled' => true,
        'url' => 'https://merchant.example.com/thank-you',
        'fallbackUrl' => '/fallback',
    ]);

    expect($resolver->resolve($experience))->toBe('https://merchant.example.com/thank-you');
});
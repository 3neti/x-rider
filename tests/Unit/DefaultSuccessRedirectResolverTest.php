<?php

use LBHurtado\XRider\Data\RiderExperienceData;
use LBHurtado\XRider\Data\RiderRedirectData;
use LBHurtado\XRider\Data\RiderSubjectData;
use LBHurtado\XRider\Enums\RiderOutcomeState;
use LBHurtado\XRider\Services\DefaultSuccessRedirectResolver;

it('returns fallback when redirect is missing', function () {
    $resolver = new DefaultSuccessRedirectResolver();

    $experience = new RiderExperienceData(
        state: RiderOutcomeState::AcceptedSuccess,
        subject: new RiderSubjectData(reference: 'claim-ABC'),
        redirect: new RiderRedirectData(enabled: false, fallbackUrl: '/fallback'),
    );

    expect($resolver->resolve($experience))->toBe('/fallback');
});

it('blocks unsafe schemes', function () {
    $resolver = new DefaultSuccessRedirectResolver();

    $experience = new RiderExperienceData(
        state: RiderOutcomeState::AcceptedSuccess,
        subject: new RiderSubjectData(reference: 'claim-ABC'),
        redirect: new RiderRedirectData(enabled: true, url: 'javascript:alert(1)', fallbackUrl: '/fallback'),
    );

    expect($resolver->resolve($experience))->toBe('/fallback');
});

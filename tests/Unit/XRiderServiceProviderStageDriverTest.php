<?php

use LBHurtado\XRider\Support\RiderStageDriverRegistry;

it('registers default stage drivers through the service provider', function () {
    $registry = app(RiderStageDriverRegistry::class);

    expect($registry->has('message'))->toBeTrue()
        ->and($registry->has('redirect'))->toBeTrue()
        ->and($registry->has('splash'))->toBeTrue();
});
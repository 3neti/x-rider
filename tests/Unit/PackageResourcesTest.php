<?php

it('ships its standalone configuration, routes, drivers, and Vue runtime', function () {
    $packageRoot = dirname(__DIR__, 2);

    expect($packageRoot.'/config/x-rider.php')->toBeFile()
        ->and($packageRoot.'/routes/x-rider.php')->toBeFile()
        ->and($packageRoot.'/resources/rider-drivers/default.yaml')->toBeFile()
        ->and($packageRoot.'/resources/js/pages/x-rider/Success.vue')->toBeFile()
        ->and($packageRoot.'/resources/js/components/x-rider/RiderRuntimeSequencer.vue')->toBeFile()
        ->and(config('x-rider.routes.enabled'))->toBeTrue();
});

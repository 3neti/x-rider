<?php

namespace LBHurtado\XRider\Tests;

use LBHurtado\XRider\XRiderServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

abstract class TestCase extends Orchestra
{
    protected function getPackageProviders($app): array
    {
        return [XRiderServiceProvider::class];
    }
}

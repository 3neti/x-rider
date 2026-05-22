<?php

namespace LBHurtado\XRider\Contracts;

use LBHurtado\XRider\Data\RiderStageData;

interface RiderStageDriverContract
{
    public function type(): string;

    public function make(array $config = [], array $context = []): RiderStageData;
}
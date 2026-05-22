<?php

namespace LBHurtado\XRider\Contracts;

use LBHurtado\XRider\Data\RiderStageCollectionData;

interface RiderStageResolverContract
{
    public function resolve(array $rider = [], array $context = []): RiderStageCollectionData;
}
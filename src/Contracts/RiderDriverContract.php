<?php

namespace LBHurtado\XRider\Contracts;

use LBHurtado\XRider\Data\RiderContentData;
use LBHurtado\XRider\Data\RiderSubjectData;

interface RiderDriverContract
{
    public function key(): string;

    public function resolve(RiderSubjectData $subject, array $config = [], array $context = []): RiderContentData;
}

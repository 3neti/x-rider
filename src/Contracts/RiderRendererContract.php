<?php

namespace LBHurtado\XRider\Contracts;

use LBHurtado\XRider\Data\RiderContentData;

interface RiderRendererContract
{
    public function render(RiderContentData $content): array;
}

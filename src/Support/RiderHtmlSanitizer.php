<?php

namespace LBHurtado\XRider\Support;

use Mews\Purifier\Facades\Purifier;

class RiderHtmlSanitizer
{
    public function sanitizeSplash(string $html): string
    {
        return Purifier::clean($html, 'rider_splash');
    }
}
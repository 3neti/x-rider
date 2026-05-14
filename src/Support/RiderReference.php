<?php

namespace LBHurtado\XRider\Support;

use Illuminate\Support\Str;

class RiderReference
{
    public static function make(string $prefix = 'rider'): string
    {
        return $prefix.'-'.Str::orderedUuid()->toString();
    }
}

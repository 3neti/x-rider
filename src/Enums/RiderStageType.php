<?php

namespace LBHurtado\XRider\Enums;

enum RiderStageType: string
{
    case Message = 'message';
    case Redirect = 'redirect';
    case Splash = 'splash';
    case Image = 'image';
    case Link = 'link';
    case Cta = 'cta';

    public function isRenderable(): bool
    {
        return match ($this) {
            self::Message,
            self::Splash,
            self::Image,
            self::Link,
            self::Cta => true,

            self::Redirect => false,
        };
    }

    public function isRedirectLike(): bool
    {
        return $this === self::Redirect;
    }
}

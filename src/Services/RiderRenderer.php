<?php

namespace LBHurtado\XRider\Services;

use LBHurtado\XRider\Contracts\RiderRendererContract;
use LBHurtado\XRider\Data\RiderContentData;

class RiderRenderer implements RiderRendererContract
{
    public function render(RiderContentData $content): array
    {
        return [
            'enabled' => $content->enabled,
            'type' => $content->normalizedType(),
            'content' => $content->content,
            'meta' => $content->meta,
        ];
    }
}

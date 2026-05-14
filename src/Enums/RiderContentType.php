<?php

namespace LBHurtado\XRider\Enums;

enum RiderContentType: string
{
    case Markdown = 'markdown';
    case Text = 'text';
    case Html = 'html';
    case Image = 'image';
    case Svg = 'svg';
    case Url = 'url';
    case Video = 'video';
    case DeepLink = 'deep_link';
}

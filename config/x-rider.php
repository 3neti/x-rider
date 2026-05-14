<?php

use LBHurtado\XRider\Enums\RiderContentType;
use LBHurtado\XRider\Enums\RiderOutcomeState;

return [
    'routes' => [
        'enabled' => true,
        'prefix' => 'x-rider',
        'middleware' => ['web'],
    ],

    'defaults' => [
        'outcome_state' => RiderOutcomeState::AcceptedSuccess->value,
        'success_type' => RiderContentType::Markdown->value,
        'success_message' => 'Your claim has been received.',
        'pending_message' => 'Your claim has been received. Your disbursement is currently being processed.',
        'redirect_timeout' => 5,
        'fallback_url' => '/',
    ],

    'redirects' => [
        'allowed_schemes' => ['http', 'https'],
        'allowed_hosts' => [],
        'allow_any_host' => env('X_RIDER_ALLOW_ANY_REDIRECT_HOST', true),
        'fallback_url' => env('X_RIDER_FALLBACK_URL', '/'),
    ],

    'rendering' => [
        'markdown_enabled' => true,
        'html_enabled' => false,
        'iframe_enabled' => false,
    ],

    'analytics' => [
        'enabled' => true,
        'logger' => 'stack',
    ],

    'drivers' => [
        // Future driver runtime registry. Example:
        // 'markdown' => \Vendor\Package\Rider\MarkdownDriver::class,
    ],
];

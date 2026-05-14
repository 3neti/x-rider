<?php

return [

    'driver' => env('X_RIDER_DRIVER', 'default'),

    'drivers_path' => env(
        'X_RIDER_DRIVERS_PATH',
        config_path('x-rider-drivers')
    ),

    'package_drivers_path' => __DIR__.'/../resources/rider-drivers',

    'defaults' => [
        'outcome_state' => env('X_RIDER_DEFAULT_OUTCOME_STATE', 'accepted_success'),

        'success_message' => 'Thank you. Your claim has been received.',
        'pending_message' => 'Your claim has been received and is being processed.',

        'success_type' => 'markdown',
        'redirect_timeout' => 5,
    ],

    'redirects' => [
        'fallback_url' => env('X_RIDER_FALLBACK_URL', '/'),

        'allowed_schemes' => [
            'http',
            'https',
        ],

        'allowed_hosts' => [
            // 'merchant.example.com',
        ],

        'allow_any_host' => env('X_RIDER_ALLOW_ANY_REDIRECT_HOST', false),
    ],

    'routes' => [
        'enabled' => env('X_RIDER_ROUTES_ENABLED', true),
        'prefix' => env('X_RIDER_ROUTE_PREFIX', 'x-rider'),
        'middleware' => ['web'],
    ],

];
<?php

use App\Models\Staff;
use App\Models\Client;

return [

    'defaults' => [
        'guard' => 'staff',
        'passwords' => 'staff',
    ],

    'guards' => [
        'staff' => [
            'driver' => 'session',
            'provider' => 'staff',
        ],
        'client' => [
            'driver' => 'session',
            'provider' => 'clients',
        ],
    ],

    'providers' => [
        'staff' => [
            'driver' => 'eloquent',
            'model' => Staff::class,
        ],
        'clients' => [
            'driver' => 'eloquent',
            'model' => Client::class,
        ],
    ],

    'passwords' => [
        'staff' => [
            'provider' => 'staff',
            'table' => 'password_reset_tokens',
            'expire' => 60,
            'throttle' => 60,
        ],
        'clients' => [
            'provider' => 'clients',
            'table' => 'client_password_reset_tokens',
            'expire' => 60,
            'throttle' => 60,
        ],
    ],

    'password_timeout' => env('AUTH_PASSWORD_TIMEOUT', 10800),

];

<?php

return [
    'defaults' => [
        'guard' => 'api',
        'passwords' => 'users',
    ],

    'guards' => [
        'api' => [
            'driver' => 'jwt',
            'provider' => 'users',
        ],
        'delivery_drivers' => [
            'driver' => 'jwt',
            'provider' => 'delivery_drivers',
        ],
        'resturants' => [
            'driver' => 'jwt',
            'provider' => 'resturants',
        ],
        'admin' => [
            'driver' => 'jwt',
            'provider' => 'admin',
        ],
    ],

    'providers' => [
        'users' => [
            'driver' => 'eloquent',
            'model' => \App\Models\User::class
        ],
        'delivery_drivers' => [
            'driver' => 'eloquent',
            'model' => \App\Models\delivery_drivers::class
        ],
        'resturants' => [
            'driver' => 'eloquent',
            'model' => \App\Models\resturants::class
        ],
        'admin' => [
            'driver' => 'eloquent',
            'model' => \App\Models\User::class
        ]
    ]
];
<?php

return [
    'plans' => [
        'free' => [
            'name' => 'Бесплатный',
            'price' => 0,
            'certificates_limit' => 50,
            'commission_rate' => 3.0,
            'analytics' => 'basic',
            'api_enabled' => false,
        ],
        'start' => [
            'name' => 'Старт',
            'price' => 990,
            'certificates_limit' => 500,
            'commission_rate' => 2.0,
            'analytics' => 'medium',
            'api_enabled' => true,
        ],
        'pro' => [
            'name' => 'Про',
            'price' => 2990,
            'certificates_limit' => null,
            'commission_rate' => 1.5,
            'analytics' => 'full',
            'api_enabled' => true,
        ],
    ],

    'qr_code' => [
        'size' => 300,
        'margin' => 10,
        'format' => 'png',
    ],

    'certificate' => [
        'prefix' => 'CERT',
        'expiration_days' => 365,
        'pin_length' => 6,
    ],

    'payment' => [
        'yookassa' => [
            'shop_id' => env('YOOKASSA_SHOP_ID'),
            'secret_key' => env('YOOKASSA_SECRET_KEY'),
        ],
        'cloudpayments' => [
            'public_id' => env('CLOUDPAYMENTS_PUBLIC_ID'),
            'api_secret' => env('CLOUDPAYMENTS_API_SECRET'),
        ],
    ],
];

<?php
// config/cors.php

return [
    'paths' => [
        'api/*',
        'sanctum/csrf-cookie',
        'storage/*',        // ✅ Allow images from storage
        'v1/*',             // ✅ Your API version
    ],

    'allowed_methods' => ['*'],

    'allowed_origins' => ['*'],
    // Or for production, restrict to your Flutter URL:
    // 'allowed_origins' => ['http://localhost:8080'],

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 0,

    'supports_credentials' => false,
];
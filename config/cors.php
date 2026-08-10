<?php

return [
    'paths' => [
        'api/*',
        'sanctum/csrf-cookie',
        'storage/*',
        'v1/*',
    ],

    'allowed_methods' => ['*'],

    // ✅ Empty when using patterns
    'allowed_origins' => [],

    // ✅ Regex — matches any localhost port
    'allowed_origins_patterns' => [
        '#^http://localhost(:\d+)?$#',
        '#^http://127\.0\.0\.1(:\d+)?$#',
    ],

    'allowed_headers' => ['*'],

    'exposed_headers' => ['Content-Disposition'],

    'max_age' => 86400,

    // ✅ Keep false — we're using Bearer tokens, not cookies
    'supports_credentials' => false,
];
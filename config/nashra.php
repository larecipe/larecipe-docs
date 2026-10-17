<?php

// The Nashra SDK's config (php artisan vendor:publish --tag=nashra-config
// writes the same file). The newsletter form on the docs sends sign-ups to
// Nashra with this key; without one, it says sign-ups are closed for now.

return [
    'api_key' => env('NASHRA_API_KEY'),
    'base_url' => env('NASHRA_BASE_URL', 'https://app.nashra.ai/api/v1'),
    'timeout' => env('NASHRA_TIMEOUT', 30),
    'max_retries' => env('NASHRA_MAX_RETRIES', 3),
];

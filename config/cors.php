<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Cross-Origin Resource Sharing (CORS) Configuration
    |--------------------------------------------------------------------------
    |
    | Here you may configure your settings for cross-origin resource sharing
    | or "CORS". This determines what cross-origin operations may execute
    | in web browsers. You are free to adjust these settings as needed.
    |
    | To learn more: https://developer.mozilla.org/en-US/docs/Web/HTTP/CORS
    |
    */

    'paths' => ['api/*', 'sanctum/csrf-cookie', 'contact', 'portfolio', 'portfolio/*'],

    'allowed_methods' => ['*'],

    // Allow multiple frontend origins for development.
    // In production, set FRONTEND_URL in .env to your exact frontend domain.
    'allowed_origins' => env('APP_ENV') === 'production' 
        ? [env('FRONTEND_URL', 'https://yourdomain.com')]
        : [
            'http://localhost:3000',
            'http://localhost:8080',
            'http://localhost:5173',
            'http://127.0.0.1:3000',
            'http://127.0.0.1:8080',
            'http://127.0.0.1:5173',
            env('FRONTEND_URL'),
        ],

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 0,

    // Allow cookies to be sent when the browser issues credentialed requests
    // (axios.withCredentials = true). Make sure the frontend origin above is
    // set to the exact origin of your dev server (including port).
    'supports_credentials' => true,

];

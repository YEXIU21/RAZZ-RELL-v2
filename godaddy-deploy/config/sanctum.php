<?php

return [
    'stateful' => explode(',', env('SANCTUM_STATEFUL_DOMAINS', sprintf(
        '%s%s%s',
        'razzrelevents.vercel.app,p3plzcpnl508725.prod.phx3.secureserver.net',
        env('APP_URL') ? ','.parse_url(env('APP_URL'), PHP_URL_HOST) : '',
        env('FRONTEND_URL') ? ','.parse_url(env('FRONTEND_URL'), PHP_URL_HOST) : ''
    ))),

    'guard' => ['web'],

    'expiration' => null,

    'middleware' => [
        'verify_csrf_token' => App\Http\Middleware\VerifyCsrfToken::class,
        'encrypt_cookies' => App\Http\Middleware\EncryptCookies::class,
    ],
];

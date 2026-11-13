<?php

use App\Models\AkunPengguna;

return [

    // Authentication Defaults: This option defines the default authentication "guard" and password reset "broker" for your application.

    'defaults' => [
        'guard' => env('AUTH_GUARD', 'web'),
        'passwords' => env('AUTH_PASSWORD_BROKER', 'users'),
    ],

    // Authentication Guards: Next, you may define every authentication guard for your application.

    'guards' => [
        'web' => [
            'driver' => 'session',
            'provider' => 'users',
        ],
    ],

    // User Providers: All authentication guards have a user provider, which defines how the users are actually retrieved out of your database or other storage system used by the application.

    'providers' => [
        'users' => [
            'driver' => 'eloquent',
            'model' => env('AUTH_MODEL', AkunPengguna::class),
        ],

        // 'users' => [
        //     'driver' => 'database',
        //     'table' => 'users',
        // ],
    ],

    // Resetting Passwords: These configuration options specify the behavior of Laravel's password reset functionality, including the table utilized for token storage and the user provider that is invoked to actually retrieve users.

    'passwords' => [
        'users' => [
            'provider' => 'users',
            'table' => env('AUTH_PASSWORD_RESET_TOKEN_TABLE', 'password_reset_tokens'),
            'expire' => 60,
            'throttle' => 60,
        ],
    ],

    // Password Confirmation Timeout: Here you may define the number of seconds before a password confirmation window expires and users are asked to re-enter their password via the confirmation screen.

    'password_timeout' => env('AUTH_PASSWORD_TIMEOUT', 10800),

];

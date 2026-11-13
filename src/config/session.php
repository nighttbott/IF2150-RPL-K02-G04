<?php

use Illuminate\Support\Str;

return [

    // Default Session Driver: This option determines the default session driver that is utilized for incoming requests.

    'driver' => env('SESSION_DRIVER', 'database'),

    // Session Lifetime: Here you may specify the number of minutes that you wish the session to be allowed to remain idle before it expires.

    'lifetime' => (int) env('SESSION_LIFETIME', 120),

    'expire_on_close' => env('SESSION_EXPIRE_ON_CLOSE', false),

    // Session Encryption: This option allows you to easily specify that all of your session data should be encrypted before it's stored.

    'encrypt' => env('SESSION_ENCRYPT', false),

    // Session File Location: When utilizing the "file" session driver, the session files are placed on disk.

    'files' => storage_path('framework/sessions'),

    // Session Database Connection: When using the "database" or "redis" session drivers, you may specify a connection that should be used to manage these sessions.

    'connection' => env('SESSION_CONNECTION'),

    // Session Database Table: When using the "database" session driver, you may specify the table to be used to store sessions.

    'table' => env('SESSION_TABLE', 'sessions'),

    // Session Cache Store: When using one of the framework's cache driven session backends, you may define the cache store which should be used to store the session data between requests.

    'store' => env('SESSION_STORE'),

    // Session Sweeping Lottery: Some session drivers must manually sweep their storage location to get rid of old sessions from storage.

    'lottery' => [2, 100],

    // Session Cookie Name: Here you may change the name of the session cookie that is created by the framework.

    'cookie' => env(
        'SESSION_COOKIE',
        Str::slug((string) env('APP_NAME', 'laravel')).'-session'
    ),

    // Session Cookie Path: The session cookie path determines the path for which the cookie will be regarded as available.

    'path' => env('SESSION_PATH', '/'),

    // Session Cookie Domain: This value determines the domain and subdomains the session cookie is available to.

    'domain' => env('SESSION_DOMAIN'),

    // HTTPS Only Cookies: By setting this option to true, session cookies will only be sent back to the server if the browser has a HTTPS connection.

    'secure' => env('SESSION_SECURE_COOKIE'),

    // HTTP Access Only: Setting this value to true will prevent JavaScript from accessing the value of the cookie and the cookie will only be accessible through the HTTP protocol.

    'http_only' => env('SESSION_HTTP_ONLY', true),

    // Same-Site Cookies: This option determines how your cookies behave when cross-site requests take place, and can be used to mitigate CSRF attacks.

    'same_site' => env('SESSION_SAME_SITE', 'lax'),

    // Partitioned Cookies: Setting this value to true will tie the cookie to the top-level site for a cross-site context.

    'partitioned' => env('SESSION_PARTITIONED_COOKIE', false),

    // Session Serialization: This value controls the serialization strategy for session data, which is JSON by default.

    'serialization' => 'json',

];

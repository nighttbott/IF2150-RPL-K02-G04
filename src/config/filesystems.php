<?php

return [

    // Default Filesystem Disk: Here you may specify the default filesystem disk that should be used by the framework.

    'default' => env('FILESYSTEM_DISK', 'local'),

    // Filesystem Disks: Below you may configure as many filesystem disks as necessary, and you may even configure multiple disks for the same driver.

    'disks' => [

        'local' => [
            'driver' => 'local',
            'root' => storage_path('app/private'),
            'serve' => true,
            'throw' => false,
            'report' => false,
        ],

        'public' => [
            'driver' => 'local',
            'root' => storage_path('app/public'),
            'url' => rtrim(env('APP_URL', 'http://localhost'), '/').'/storage',
            'visibility' => 'public',
            'throw' => false,
            'report' => false,
        ],

        's3' => [
            'driver' => 's3',
            'key' => env('AWS_ACCESS_KEY_ID'),
            'secret' => env('AWS_SECRET_ACCESS_KEY'),
            'region' => env('AWS_DEFAULT_REGION'),
            'bucket' => env('AWS_BUCKET'),
            'url' => env('AWS_URL'),
            'endpoint' => env('AWS_ENDPOINT'),
            'use_path_style_endpoint' => env('AWS_USE_PATH_STYLE_ENDPOINT', false),
            'throw' => false,
            'report' => false,
        ],

    ],

    // Symbolic Links: Here you may configure the symbolic links that will be created when the `storage:link` Artisan command is executed.

    'links' => [
        public_path('storage') => storage_path('app/public'),
    ],

];

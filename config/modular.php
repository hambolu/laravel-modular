<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Modules Path
    |--------------------------------------------------------------------------
    | Where your modules live. Default: app/Modules
    */
    'path' => app_path('Modules'),

    /*
    |--------------------------------------------------------------------------
    | Modules Namespace
    |--------------------------------------------------------------------------
    */
    'namespace' => 'App\\Modules',

    /*
    |--------------------------------------------------------------------------
    | Auto-Discovery
    |--------------------------------------------------------------------------
    | Automatically discover and register modules on boot.
    */
    'auto_discover' => true,

    /*
    |--------------------------------------------------------------------------
    | Disabled Modules
    |--------------------------------------------------------------------------
    | List module names here to disable them without deleting them.
    */
    'disabled' => [],

    /*
    |--------------------------------------------------------------------------
    | Module Structure
    |--------------------------------------------------------------------------
    | Toggle which directories are scaffolded when running module:make.
    */
    'structure' => [
        'Controllers'  => true,
        'Services'     => true,
        'Repositories' => true,
        'Models'       => true,
        'Actions'      => true,
        'DTOs'         => true,
        'Events'       => true,
        'Listeners'    => true,
        'Jobs'         => true,
        'Policies'     => true,
        'Middleware'   => true,
        'Resources'    => true,
        'Requests'     => true,
        'Routes'       => true,
        'Database'     => true,
        'Tests'        => true,
        'Config'       => true,
        'Lang'         => true,
        'Observers'    => true,
        'Notifications' => true,
        'Rules'        => true,
        'Casts'        => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | Inter-Module Communication
    |--------------------------------------------------------------------------
    | driver: sync | queue | event
    */
    'imc' => [
        'driver' => 'sync',
    ],

    /*
    |--------------------------------------------------------------------------
    | Module Versioning
    |--------------------------------------------------------------------------
    | Enable API versioning per module. When enabled, routes will be prefixed
    | with the version e.g. /api/v1/users
    */
    'versioning' => [
        'enabled' => false,
        'default' => 'v1',
    ],

    /*
    |--------------------------------------------------------------------------
    | Health Checks
    |--------------------------------------------------------------------------
    | Register a /modular/health endpoint listing all loaded modules.
    */
    'health' => [
        'enabled'    => false,
        'route'      => '/modular/health',
        'middleware' => ['api'],
    ],

    /*
    |--------------------------------------------------------------------------
    | Cache
    |--------------------------------------------------------------------------
    | Default TTL (seconds) for HasCaching trait. Override per service.
    */
    'cache' => [
        'ttl'    => 3600,
        'prefix' => 'modular',
    ],

    /*
    |--------------------------------------------------------------------------
    | Repository Settings
    |--------------------------------------------------------------------------
    */
    'repository' => [
        'default_per_page' => 15,
    ],
];

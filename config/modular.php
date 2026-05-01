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
    | Automatically discover and register modules
    */
    'auto_discover' => true,

    /*
    |--------------------------------------------------------------------------
    | Disabled Modules
    |--------------------------------------------------------------------------
    */
    'disabled' => [],

    /*
    |--------------------------------------------------------------------------
    | Transpiler
    |--------------------------------------------------------------------------
    | Enable the JS-like transpiler (write without $ prefix)
    */
    'transpiler' => [
        'enabled' => false, // opt-in feature
        'extensions' => ['.mod.php'],
    ],

    /*
    |--------------------------------------------------------------------------
    | Module Structure
    |--------------------------------------------------------------------------
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
    ],

    /*
    |--------------------------------------------------------------------------
    | Inter-Module Communication
    |--------------------------------------------------------------------------
    */
    'imc' => [
        'driver' => 'sync', // sync | queue | event
    ],
];

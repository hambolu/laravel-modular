<?php

use LaravelModular\Module\ModuleRegistry;

if (!function_exists('module')) {
    /**
     * Get a service from another module, or call a method on it.
     *
     * Usage:
     *   module('User@UserService')->findById(1)
     *   module('User@UserService', 'findById', [1])
     */
    function module(string $target, ?string $method = null, array $args = []): mixed
    {
        $registry = app(ModuleRegistry::class);

        if ($method !== null) {
            return $registry->call($target, $method, $args);
        }

        return $registry->service($target);
    }
}

if (!function_exists('module_path')) {
    /**
     * Get the path to the modules directory or a specific module path.
     */
    function module_path(string $module = '', string $path = ''): string
    {
        $base = config('modular.path', app_path('Modules'));
        return $base
            . ($module ? DIRECTORY_SEPARATOR . $module : '')
            . ($path   ? DIRECTORY_SEPARATOR . ltrim($path, '/\\') : '');
    }
}

if (!function_exists('dto')) {
    /**
     * Create a DTO from data.
     *
     * Usage: dto(CreateUserDto::class, $request->validated())
     */
    function dto(string $class, array|object $data): object
    {
        return $class::from($data);
    }
}

if (!function_exists('action')) {
    /**
     * Resolve and execute an action.
     *
     * Usage: action(CreateUserAction::class, $dto)
     */
    function action(string $class, mixed ...$args): mixed
    {
        return app($class)->execute(...$args);
    }
}

if (!function_exists('module_config')) {
    /**
     * Get configuration from a module's own config file.
     *
     * Usage: module_config('user', 'pagination.per_page', 15)
     */
    function module_config(string $module, string $key = '', mixed $default = null): mixed
    {
        $base = strtolower($module);
        $full = $key ? "{$base}.{$key}" : $base;
        return config($full, $default);
    }
}

if (!function_exists('is_module_enabled')) {
    /**
     * Check if a module is enabled.
     */
    function is_module_enabled(string $name): bool
    {
        return app(ModuleRegistry::class)->isEnabled($name);
    }
}

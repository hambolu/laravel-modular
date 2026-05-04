<?php

namespace LaravelModular\Facades;

use Illuminate\Support\Facades\Facade;
use LaravelModular\Module\ModuleRegistry;

/**
 * @method static mixed call(string $target, string $method, array $args = [])
 * @method static object service(string $target)
 * @method static bool has(string $name)
 * @method static bool isEnabled(string $name)
 * @method static array all()
 * @method static array enabled()
 * @method static array disabled()
 * @method static mixed whenEnabled(string $name, callable $callback)
 * @method static bool exports(string $moduleName, string $serviceName)
 *
 * @see \LaravelModular\Module\ModuleRegistry
 */
class Module extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return ModuleRegistry::class;
    }
}

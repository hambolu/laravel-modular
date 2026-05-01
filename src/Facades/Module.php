<?php

namespace LaravelModular\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * Module Facade
 *
 * @method static mixed call(string $target, string $method, array $args = [])
 * @method static object service(string $target)
 * @method static \LaravelModular\Contracts\ModuleInterface|null get(string $name)
 * @method static array all()
 * @method static bool isEnabled(string $name)
 */
class Module extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'module';
    }
}

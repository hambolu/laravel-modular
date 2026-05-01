<?php

namespace LaravelModular\Traits;

/**
 * Mark a class as injectable — provides a static make() factory
 * mirroring NestJS-style instantiation.
 */
trait Injectable
{
    public static function make(...$args): static
    {
        if (empty($args)) {
            return app(static::class);
        }
        return new static(...$args);
    }

    public static function inject(): static
    {
        return app(static::class);
    }
}

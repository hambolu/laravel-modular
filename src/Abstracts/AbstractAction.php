<?php

namespace LaravelModular\Abstracts;

use LaravelModular\Traits\Injectable;
use LaravelModular\Traits\EmitsEvents;

/**
 * Base Action — single-responsibility classes.
 * Inspired by NestJS use-case / service patterns.
 *
 * Usage: UserCreateAction::make()->execute($dto)
 *        app(UserCreateAction::class)->execute($dto)
 */
abstract class AbstractAction
{
    use Injectable, EmitsEvents;

    abstract public function execute(mixed ...$args): mixed;

    /**
     * Invoke support: allows calling the action as a function
     */
    public function __invoke(mixed ...$args): mixed
    {
        return $this->execute(...$args);
    }
}

<?php

namespace LaravelModular\Traits;

use Illuminate\Pipeline\Pipeline;

/**
 * Adds pipeline support to services — chain your actions/pipes cleanly.
 */
trait HasPipeline
{
    public function pipeline(mixed $payload, array $pipes): mixed
    {
        return app(Pipeline::class)
            ->send($payload)
            ->through($pipes)
            ->thenReturn();
    }

    public function pipeThrough(mixed $payload, array $pipes, callable $destination): mixed
    {
        return app(Pipeline::class)
            ->send($payload)
            ->through($pipes)
            ->then($destination);
    }
}

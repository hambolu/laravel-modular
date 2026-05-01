<?php

namespace LaravelModular\Traits;

/**
 * Easy event emission from any class — one-line event dispatch.
 */
trait EmitsEvents
{
    public function emit(string|object $event, mixed ...$payload): void
    {
        if (is_string($event) && !empty($payload)) {
            event(new $event(...$payload));
        } else {
            event($event);
        }
    }

    public function emitIf(bool $condition, string|object $event, mixed ...$payload): void
    {
        if ($condition) {
            $this->emit($event, ...$payload);
        }
    }
}

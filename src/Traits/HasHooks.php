<?php

namespace LaravelModular\Traits;

/**
 * Lifecycle hooks for services — run logic before/after operations.
 *
 * Usage in a service:
 *   protected function beforeCreate(array $data): array { ... }
 *   protected function afterCreate(mixed $model): void { ... }
 */
trait HasHooks
{
    protected function runBeforeHook(string $operation, mixed $payload): mixed
    {
        $method = 'before' . ucfirst($operation);
        if (method_exists($this, $method)) {
            return $this->{$method}($payload);
        }
        return $payload;
    }

    protected function runAfterHook(string $operation, mixed $result): void
    {
        $method = 'after' . ucfirst($operation);
        if (method_exists($this, $method)) {
            $this->{$method}($result);
        }
    }

    /**
     * Wrap an operation with before/after hooks automatically.
     */
    protected function withHooks(string $operation, callable $callback, mixed $payload): mixed
    {
        $payload = $this->runBeforeHook($operation, $payload);
        $result  = $callback($payload);
        $this->runAfterHook($operation, $result);
        return $result;
    }
}

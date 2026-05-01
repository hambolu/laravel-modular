<?php

namespace LaravelModular\Traits;

use Illuminate\Support\Facades\Cache;

/**
 * Drop-in caching for services — one method call.
 */
trait HasCaching
{
    protected int $cacheTtl = 3600;

    public function cached(string $key, callable $callback, ?int $ttl = null): mixed
    {
        return Cache::remember($key, $ttl ?? $this->cacheTtl, $callback);
    }

    public function cachedForever(string $key, callable $callback): mixed
    {
        return Cache::rememberForever($key, $callback);
    }

    public function invalidateCache(string|array $keys): void
    {
        foreach ((array) $keys as $key) {
            Cache::forget($key);
        }
    }

    public function cacheKey(string ...$parts): string
    {
        return implode(':', array_filter($parts));
    }
}

<?php

namespace LaravelModular\Traits;

use Illuminate\Support\Facades\Cache;

/**
 * Drop-in caching for services and repositories.
 */
trait HasCaching
{
    protected int $cacheTtl = 3600;

    public function cached(string $key, callable $callback, ?int $ttl = null): mixed
    {
        $ttl = $ttl ?? config('modular.cache.ttl', $this->cacheTtl);
        return Cache::remember($this->prefixedKey($key), $ttl, $callback);
    }

    public function cachedForever(string $key, callable $callback): mixed
    {
        return Cache::rememberForever($this->prefixedKey($key), $callback);
    }

    public function invalidateCache(string|array $keys): void
    {
        foreach ((array) $keys as $key) {
            Cache::forget($this->prefixedKey($key));
        }
    }

    public function cacheKey(string ...$parts): string
    {
        return implode(':', array_filter($parts));
    }

    protected function prefixedKey(string $key): string
    {
        $prefix = config('modular.cache.prefix', 'modular');
        return $prefix . ':' . $key;
    }

    protected function cacheTag(string $suffix = ''): string
    {
        $class = class_basename(static::class);
        return $suffix ? "{$class}.{$suffix}" : $class;
    }
}

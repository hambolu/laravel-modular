<?php

namespace LaravelModular\Macros;

use Illuminate\Support\Collection;

/**
 * Adds extra collection macros for one-liner data transformations.
 */
class CollectionMacros
{
    public static function register(): void
    {
        // Map to DTO
        Collection::macro('toDto', function (string $dtoClass) {
            return $this->map(fn($item) => $dtoClass::from($item));
        });

        // Paginate a collection manually
        Collection::macro('paginate', function (int $perPage = 15, int $page = 1) {
            $page  = max(1, $page);
            $items = $this->slice(($page - 1) * $perPage, $perPage)->values();
            return new \Illuminate\Pagination\LengthAwarePaginator(
                $items, $this->count(), $perPage, $page
            );
        });

        // Group by multiple keys
        Collection::macro('groupByMany', function (array $keys) {
            return collect($keys)->reduce(
                fn($carry, $key) => $carry->groupBy($key),
                $this
            );
        });

        // Chunk and process
        Collection::macro('chunkProcess', function (int $size, callable $callback) {
            $this->chunk($size)->each($callback);
        });

        // Find first by key-value
        Collection::macro('findWhere', function (string $key, mixed $value) {
            return $this->firstWhere($key, $value);
        });
    }
}

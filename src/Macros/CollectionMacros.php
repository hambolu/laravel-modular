<?php

namespace LaravelModular\Macros;

use Illuminate\Support\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class CollectionMacros
{
    public static function register(): void
    {
        // Map items to DTOs
        Collection::macro('toDto', function (string $dtoClass): Collection {
            /** @var Collection $this */
            return $this->map(fn($item) => $dtoClass::from($item));
        });

        // In-memory pagination
        Collection::macro('paginate', function (int $perPage = 15, int $page = 1, string $pageName = 'page'): LengthAwarePaginator {
            /** @var Collection $this */
            $page    = request()->input($pageName, $page);
            $total   = $this->count();
            $results = $this->forPage($page, $perPage)->values();
            return new LengthAwarePaginator(
                $results,
                $total,
                $perPage,
                $page,
                ['path' => request()->url(), 'pageName' => $pageName]
            );
        });

        // Group by multiple keys
        Collection::macro('groupByMany', function (array $keys): Collection {
            /** @var Collection $this */
            if (empty($keys)) {
                return $this;
            }
            $key = array_shift($keys);
            $grouped = $this->groupBy($key);
            if (!empty($keys)) {
                return $grouped->map(fn($group) => $group->groupByMany($keys));
            }
            return $grouped;
        });

        // Pluck unique values across multiple columns
        Collection::macro('pluckMultiple', function (array $keys): Collection {
            /** @var Collection $this */
            return $this->map(fn($item) => collect($item)->only($keys)->toArray());
        });

        // Map with keys (preserve keys)
        Collection::macro('mapWithKeys', function (callable $callback): Collection {
            /** @var Collection $this */
            return $this->reduce(function (Collection $carry, $item, $key) use ($callback) {
                return $carry->merge($callback($item, $key));
            }, collect());
        });

        // Get items between two indices
        Collection::macro('between', function (int $start, int $end): Collection {
            /** @var Collection $this */
            return $this->slice($start, $end - $start + 1)->values();
        });

        // Sum a nested key
        Collection::macro('sumNested', function (string $key): float|int {
            /** @var Collection $this */
            return $this->sum(fn($item) => data_get($item, $key, 0));
        });

        // Partition into chunks and map each
        Collection::macro('mapChunks', function (int $size, callable $callback): Collection {
            /** @var Collection $this */
            return $this->chunk($size)->map($callback)->flatten(1);
        });
    }
}

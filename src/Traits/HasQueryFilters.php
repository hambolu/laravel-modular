<?php

namespace LaravelModular\Traits;

use Illuminate\Database\Eloquent\Builder;

/**
 * Adds search, sort, and filter support to repositories.
 * Pair with AbstractRepository to enable query filtering.
 */
trait HasQueryFilters
{
    /**
     * Apply an array of filters to a query builder.
     *
     * Supports:
     *   ['status' => 'active']              → WHERE status = 'active'
     *   ['name' => ['like', '%john%']]       → WHERE name LIKE '%john%'
     *   ['amount' => ['between', [10, 100]]] → WHERE amount BETWEEN 10 AND 100
     *   ['role' => ['in', ['admin','editor']]]→ WHERE role IN (...)
     */
    public function applyFilters(Builder $query, array $filters): Builder
    {
        foreach ($filters as $column => $value) {
            if (is_array($value) && count($value) === 2) {
                [$operator, $operand] = $value;
                $operator = strtolower($operator);

                match ($operator) {
                    'like'     => $query->where($column, 'LIKE', $operand),
                    'in'       => $query->whereIn($column, (array) $operand),
                    'not_in'   => $query->whereNotIn($column, (array) $operand),
                    'between'  => $query->whereBetween($column, $operand),
                    'null'     => $query->whereNull($column),
                    'not_null' => $query->whereNotNull($column),
                    default    => $query->where($column, $operator, $operand),
                };
            } elseif ($value !== null) {
                $query->where($column, $value);
            }
        }
        return $query;
    }

    /**
     * Apply sorting. Accepts:
     *   ['created_at', 'desc']  or  ['created_at' => 'desc']
     */
    public function applySort(Builder $query, array|string $sort, string $direction = 'asc'): Builder
    {
        if (is_string($sort)) {
            return $query->orderBy($sort, $direction);
        }

        foreach ($sort as $column => $dir) {
            if (is_int($column)) {
                $query->orderBy($dir, 'asc');
            } else {
                $query->orderBy($column, $dir);
            }
        }
        return $query;
    }

    /**
     * Apply a search (LIKE) across multiple columns.
     */
    public function applySearch(Builder $query, ?string $search, array $columns): Builder
    {
        if (!$search) {
            return $query;
        }
        return $query->where(function (Builder $q) use ($search, $columns) {
            foreach ($columns as $column) {
                $q->orWhere($column, 'LIKE', "%{$search}%");
            }
        });
    }
}

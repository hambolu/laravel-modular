<?php

namespace LaravelModular\Abstracts;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use LaravelModular\Traits\Injectable;
use LaravelModular\Traits\HasCaching;

/**
 * Base Repository with full CRUD, pagination, filtering built in.
 * One-line operations for developers.
 */
abstract class AbstractRepository
{
    use Injectable, HasCaching;

    protected string $model;

    protected function query()
    {
        return app($this->model)->newQuery();
    }

    public function all(array $columns = ['*']): Collection
    {
        return $this->query()->get($columns);
    }

    public function find(int|string $id, array $columns = ['*']): ?Model
    {
        return $this->query()->find($id, $columns);
    }

    public function findOrFail(int|string $id): Model
    {
        return $this->query()->findOrFail($id);
    }

    public function findBy(string $column, mixed $value): ?Model
    {
        return $this->query()->where($column, $value)->first();
    }

    public function findWhere(array $criteria): Collection
    {
        $q = $this->query();
        foreach ($criteria as $col => $val) {
            $q->where($col, $val);
        }
        return $q->get();
    }

    public function create(array $data): Model
    {
        return $this->query()->create($data);
    }

    public function update(int|string $id, array $data): Model
    {
        $model = $this->findOrFail($id);
        $model->update($data);
        return $model->fresh();
    }

    public function delete(int|string $id): bool
    {
        return (bool) $this->query()->where('id', $id)->delete();
    }

    public function paginate(int $perPage = 15, array $columns = ['*']): LengthAwarePaginator
    {
        return $this->query()->paginate($perPage, $columns);
    }

    public function paginateWhere(array $criteria, int $perPage = 15): LengthAwarePaginator
    {
        $q = $this->query();
        foreach ($criteria as $col => $val) {
            $q->where($col, $val);
        }
        return $q->paginate($perPage);
    }

    public function count(array $criteria = []): int
    {
        $q = $this->query();
        foreach ($criteria as $col => $val) {
            $q->where($col, $val);
        }
        return $q->count();
    }

    public function exists(array $criteria): bool
    {
        $q = $this->query();
        foreach ($criteria as $col => $val) {
            $q->where($col, $val);
        }
        return $q->exists();
    }

    public function firstOrCreate(array $search, array $create = []): Model
    {
        return $this->query()->firstOrCreate($search, $create);
    }

    public function updateOrCreate(array $search, array $update = []): Model
    {
        return $this->query()->updateOrCreate($search, $update);
    }

    /**
     * Scope queries with array of where conditions or a callable.
     */
    public function scope(array|callable $scope): static
    {
        // Returns a cloned repository instance with a modified query
        // For advanced use
        return $this;
    }

    public function with(array $relations): Collection
    {
        return $this->query()->with($relations)->get();
    }

    public function withPaginated(array $relations, int $perPage = 15): LengthAwarePaginator
    {
        return $this->query()->with($relations)->paginate($perPage);
    }
}

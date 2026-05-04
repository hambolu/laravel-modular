<?php

namespace LaravelModular\Abstracts;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use LaravelModular\Traits\Injectable;
use LaravelModular\Traits\HasCaching;
use LaravelModular\Traits\HasQueryFilters;
use LaravelModular\Traits\HasSoftDeleteSupport;

/**
 * Base Repository — full CRUD, filtering, sorting, search, soft-deletes.
 */
abstract class AbstractRepository
{
    use Injectable, HasCaching, HasQueryFilters, HasSoftDeleteSupport;

    protected string $model;

    /** Columns to include in full-text search (override in subclass). */
    protected array $searchable = [];

    /** Default sort column. */
    protected string $defaultSort = 'created_at';

    /** Default sort direction. */
    protected string $defaultSortDirection = 'desc';

    protected function query(): Builder
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

    /**
     * Filter, search, sort and paginate in one call.
     *
     * @param array  $filters   ['column' => 'value'] or ['column' => ['operator', 'value']]
     * @param string|null $search  full-text search term (against $searchable columns)
     * @param array  $sort       ['column' => 'asc|desc'] or ['column', 'direction']
     * @param int    $perPage
     */
    public function filter(
        array $filters = [],
        ?string $search = null,
        array $sort = [],
        int $perPage = 0,
    ): LengthAwarePaginator|Collection {
        $perPage = $perPage ?: config('modular.repository.default_per_page', 15);
        $q = $this->query();

        if ($filters) {
            $q = $this->applyFilters($q, $filters);
        }

        if ($search && $this->searchable) {
            $q = $this->applySearch($q, $search, $this->searchable);
        }

        $sort = $sort ?: [$this->defaultSort => $this->defaultSortDirection];
        $q = $this->applySort($q, $sort);

        return $q->paginate($perPage);
    }

    public function search(string $term, int $perPage = 0): LengthAwarePaginator
    {
        $perPage = $perPage ?: config('modular.repository.default_per_page', 15);
        $q = $this->query();
        if ($this->searchable) {
            $q = $this->applySearch($q, $term, $this->searchable);
        }
        return $q->paginate($perPage);
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

    public function paginate(int $perPage = 0, array $columns = ['*']): LengthAwarePaginator
    {
        $perPage = $perPage ?: config('modular.repository.default_per_page', 15);
        return $this->query()
            ->orderBy($this->defaultSort, $this->defaultSortDirection)
            ->paginate($perPage, $columns);
    }

    public function paginateWhere(array $criteria, int $perPage = 0): LengthAwarePaginator
    {
        $perPage = $perPage ?: config('modular.repository.default_per_page', 15);
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

    public function with(array $relations): Collection
    {
        return $this->query()->with($relations)->get();
    }

    public function withPaginated(array $relations, int $perPage = 0): LengthAwarePaginator
    {
        $perPage = $perPage ?: config('modular.repository.default_per_page', 15);
        return $this->query()->with($relations)->paginate($perPage);
    }

    /** Run a transaction and return its result. */
    public function transaction(callable $callback): mixed
    {
        return \Illuminate\Support\Facades\DB::transaction($callback);
    }

    /** Bulk insert (no events, fast). */
    public function insertBulk(array $rows): bool
    {
        return $this->query()->insert($rows);
    }

    /** Chunk through all records without loading into memory. */
    public function chunk(int $size, callable $callback): void
    {
        $this->query()->chunk($size, $callback);
    }

    /** Get latest N records. */
    public function latest(int $limit = 10): Collection
    {
        return $this->query()->latest()->limit($limit)->get();
    }

    /** Get oldest N records. */
    public function oldest(int $limit = 10): Collection
    {
        return $this->query()->oldest()->limit($limit)->get();
    }
}

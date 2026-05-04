<?php

namespace LaravelModular\Traits;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

/**
 * Adds soft-delete-aware methods to AbstractRepository.
 */
trait HasSoftDeleteSupport
{
    public function withTrashed(): Collection
    {
        return $this->query()->withTrashed()->get();
    }

    public function onlyTrashed(): Collection
    {
        return $this->query()->onlyTrashed()->get();
    }

    public function restore(int|string $id): bool
    {
        $model = $this->query()->withTrashed()->findOrFail($id);
        return (bool) $model->restore();
    }

    public function forceDelete(int|string $id): bool
    {
        return (bool) $this->query()->withTrashed()->where('id', $id)->forceDelete();
    }

    public function paginateTrashed(int $perPage = 15): LengthAwarePaginator
    {
        return $this->query()->onlyTrashed()->paginate($perPage);
    }
}

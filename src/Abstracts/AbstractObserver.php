<?php

namespace LaravelModular\Abstracts;

use Illuminate\Database\Eloquent\Model;
use LaravelModular\Traits\Injectable;

/**
 * Base Observer — observe model lifecycle events.
 *
 * Usage:
 *   class UserObserver extends AbstractObserver
 *   {
 *       public function created(User $model): void
 *       {
 *           // e.g. create a default profile
 *       }
 *   }
 */
abstract class AbstractObserver
{
    use Injectable;

    public function creating(Model $model): void {}
    public function created(Model $model): void {}
    public function updating(Model $model): void {}
    public function updated(Model $model): void {}
    public function saving(Model $model): void {}
    public function saved(Model $model): void {}
    public function deleting(Model $model): void {}
    public function deleted(Model $model): void {}
    public function restoring(Model $model): void {}
    public function restored(Model $model): void {}
    public function forceDeleting(Model $model): void {}
    public function forceDeleted(Model $model): void {}
}

<?php

namespace LaravelModular\Abstracts;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Database\Eloquent\Model;

/**
 * Base Policy with common gates pre-wired.
 * Override only what you need.
 */
abstract class AbstractPolicy
{
    use HandlesAuthorization;

    /**
     * Default: super admins can do everything
     */
    public function before(User $user, string $ability): bool|null
    {
        if (method_exists($user, 'isSuperAdmin') && $user->isSuperAdmin()) {
            return true;
        }
        return null;
    }

    public function viewAny(User $user): bool
    {
        return $this->hasPermission($user, 'viewAny');
    }

    public function view(User $user, Model $model): bool
    {
        return $this->hasPermission($user, 'view') || $this->isOwner($user, $model);
    }

    public function create(User $user): bool
    {
        return $this->hasPermission($user, 'create');
    }

    public function update(User $user, Model $model): bool
    {
        return $this->hasPermission($user, 'update') || $this->isOwner($user, $model);
    }

    public function delete(User $user, Model $model): bool
    {
        return $this->hasPermission($user, 'delete') || $this->isOwner($user, $model);
    }

    public function restore(User $user, Model $model): bool
    {
        return $this->hasPermission($user, 'restore');
    }

    public function forceDelete(User $user, Model $model): bool
    {
        return $this->hasPermission($user, 'forceDelete');
    }

    protected function isOwner(User $user, Model $model): bool
    {
        return isset($model->user_id) && $model->user_id === $user->getKey();
    }

    protected function hasPermission(User $user, string $permission): bool
    {
        if (method_exists($user, 'hasPermissionTo')) {
            $modelName = strtolower(class_basename($this->getModelClass()));
            return $user->hasPermissionTo("{$permission}_{$modelName}");
        }
        return false;
    }

    protected function getModelClass(): string
    {
        // Derived from policy class name: UserPolicy => User
        $name = class_basename(static::class);
        return str_replace('Policy', '', $name);
    }
}

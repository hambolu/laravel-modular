<?php

namespace App\Modules\User\Repositories;

use LaravelModular\Abstracts\AbstractRepository;
use App\Modules\User\Models\User;

class UserRepository extends AbstractRepository
{
    protected string $model = User::class;
    // All CRUD inherited. Add custom queries below.

    public function findByEmail(string $email): ?User
    {
        return $this->findBy('email', $email);
    }
}

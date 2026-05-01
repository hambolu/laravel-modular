<?php

namespace App\Modules\User\Services;

use LaravelModular\Abstracts\AbstractService;
use App\Modules\User\Repositories\UserRepository;
use App\Modules\User\Models\User;
use App\Modules\User\Events\UserCreated;
use App\Modules\User\DTOs\CreateUserDto;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class UserService extends AbstractService
{
    public function __construct(
        protected UserRepository $repository
    ) {}

    public function paginate(int $perPage = 15): LengthAwarePaginator
    {
        // HasCaching trait: one line
        return $this->cached('users:all', fn() => $this->repository->paginate($perPage), 300);
    }

    public function findOrFail(int $id): User
    {
        return $this->cached("user:{$id}", fn() => $this->repository->findOrFail($id));
    }

    public function create(CreateUserDto|array $data): User
    {
        $dto  = is_array($data) ? CreateUserDto::from($data) : $data;
        $user = $this->repository->create($dto->toArray());

        $this->emit(UserCreated::class, $user);      // EmitsEvents trait
        $this->invalidateCache(['users:all']);         // HasCaching trait

        return $user;
    }
}

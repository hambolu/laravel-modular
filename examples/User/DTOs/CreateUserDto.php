<?php

namespace App\Modules\User\DTOs;

use LaravelModular\Abstracts\AbstractDto;

class CreateUserDto extends AbstractDto
{
    public string $name     = '';
    public string $email    = '';
    public string $password = '';
    public string $role     = 'user';
}

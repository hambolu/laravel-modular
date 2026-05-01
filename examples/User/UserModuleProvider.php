<?php

namespace App\Modules\User;

use LaravelModular\Abstracts\AbstractModule;
use App\Modules\User\Models\User;
use App\Modules\User\Policies\UserPolicy;
use App\Modules\User\Events\UserCreated;
use App\Modules\User\Listeners\SendWelcomeEmail;

class UserModuleProvider extends AbstractModule
{
    /**
     * Accessible to other modules via Module::call() or module() helper
     */
    protected array $exports = [
        'UserService',
    ];

    protected array $policies = [
        User::class => UserPolicy::class,
    ];

    protected array $listen = [
        UserCreated::class => [
            SendWelcomeEmail::class,
        ],
    ];
}

# Laravel Modular

> NestJS-inspired modular architecture for Laravel. Write less, do more.

[![PHP](https://img.shields.io/badge/PHP-8.1+-blue)](https://php.net)
[![Laravel](https://img.shields.io/badge/Laravel-10|12-red)](https://laravel.com)

---

## Installation

```bash
composer require laravelmodular/modular
```

Then run setup:

```bash
php artisan modular:setup
composer dump-autoload
```

Add to your `composer.json` autoload:

```json
"autoload": {
    "psr-4": {
        "App\\Modules\\": "app/Modules/"
    }
}
```

---

## Creating a Module

```bash
php artisan module:make User
```

This creates:

```
app/Modules/User/
├── UserModuleProvider.php        ← Module entrypoint (like NestJS @Module)
├── Controllers/
│   └── UserController.php        ← Extends AbstractController
├── Services/
│   └── UserService.php           ← Extends AbstractService
├── Repositories/
│   └── UserRepository.php        ← Extends AbstractRepository (full CRUD built-in)
├── Models/
│   └── User.php
├── Actions/
│   ├── CreateUserAction.php      ← Single-responsibility actions
│   ├── UpdateUserAction.php
│   └── DeleteUserAction.php
├── DTOs/
│   ├── CreateUserDto.php         ← Data Transfer Objects
│   └── UpdateUserDto.php
├── Events/
│   ├── UserCreated.php
│   ├── UserUpdated.php
│   └── UserDeleted.php
├── Policies/
│   └── UserPolicy.php            ← Extends AbstractPolicy (CRUD gates pre-wired)
├── Resources/
│   └── UserResource.php          ← API Resource
├── Requests/
│   ├── CreateUserRequest.php
│   └── UpdateUserRequest.php
├── Routes/
│   ├── api.php
│   └── web.php
├── Database/
│   └── migrations/
│       └── 2024_01_01_create_users_table.php
├── Config/
│   └── user.php
└── Tests/
    └── UserTest.php              ← Full CRUD test scaffold
```

---

## Inter-Module Communication

Call services from **any other module** without touching the source module:

```php
// Option 1: Facade
use LaravelModular\Facades\Module;

$user = Module::call('User@UserService', 'findOrFail', [1]);

// Option 2: Helper function
$user = module('User@UserService')->findOrFail(1);

// Option 3: Get service instance
$userService = module('User@UserService');
$user = $userService->findOrFail(1);
$users = $userService->paginate(20);
```

To allow access, the source module must **export** the service:

```php
// app/Modules/User/UserModuleProvider.php
protected array $exports = [
    'UserService',  // ← This allows other modules to call UserService
];
```

---

## Base Classes

### AbstractController

```php
class UserController extends AbstractController
{
    public function index()
    {
        return $this->paginated($this->service->paginate()); // auto meta
    }

    public function store(CreateUserRequest $request)
    {
        return $this->created(new UserResource($item)); // HTTP 201
    }

    public function destroy(int $id)
    {
        $this->service->delete($id);
        return $this->noContent(); // HTTP 204
    }
}
```

Available response methods:
- `$this->ok($data)`
- `$this->created($data)`
- `$this->noContent()`
- `$this->paginated($paginator)`
- `$this->notFound($message)`
- `$this->forbidden($message)`
- `$this->badRequest($message, $errors)`
- `$this->unprocessable($errors)`

---

### AbstractRepository

Zero boilerplate CRUD — just define the model:

```php
class UserRepository extends AbstractRepository
{
    protected string $model = User::class;
    // That's it. All methods below are inherited.
}
```

Built-in methods:
```php
$repo->all()
$repo->find($id)
$repo->findOrFail($id)
$repo->findBy('email', $email)
$repo->findWhere(['role' => 'admin', 'active' => true])
$repo->create($data)
$repo->update($id, $data)
$repo->delete($id)
$repo->paginate(15)
$repo->paginateWhere(['role' => 'admin'], 15)
$repo->count(['active' => true])
$repo->exists(['email' => $email])
$repo->firstOrCreate(['email' => $email], $data)
$repo->updateOrCreate(['email' => $email], $data)
$repo->with(['posts', 'roles'])
$repo->withPaginated(['posts'], 15)
```

---

### AbstractDto

```php
class CreateUserDto extends AbstractDto
{
    public string $name  = '';
    public string $email = '';
    public string $role  = 'user';
}

// Fill from request
$dto = CreateUserDto::from($request->validated());

// Fill from model
$dto = CreateUserDto::fromModel($user);

// One-line override
$updated = $dto->with(['role' => 'admin']);

// Selective extraction
$dto->only(['name', 'email']);
$dto->except(['password']);

// Collection of DTOs
$dtos = CreateUserDto::collection($request->all());

// Helper function
$dto = dto(CreateUserDto::class, $request->validated());
```

---

### AbstractAction

Single-responsibility classes, perfect for complex business logic:

```php
class CreateUserAction extends AbstractAction
{
    public function execute(mixed ...$args): mixed
    {
        [$dto, $role] = $args;
        // create user, send email, log, etc.
    }
}

// Usage — 3 ways:
app(CreateUserAction::class)->execute($dto, 'admin');
CreateUserAction::make()->execute($dto);
action(CreateUserAction::class, $dto, 'admin');  // helper
```

---

### AbstractPolicy

Pre-wired CRUD gates + admin bypass:

```php
class PostPolicy extends AbstractPolicy
{
    // Admin bypass is handled. Override only custom rules:
    
    public function publish(User $user, Post $post): bool
    {
        return $user->role === 'editor' || $this->isOwner($user, $post);
    }
}
```

---

## Module Provider

```php
class UserModuleProvider extends AbstractModule
{
    protected array $exports = ['UserService'];

    protected array $bindings = [
        UserRepositoryInterface::class => UserRepository::class,
    ];

    protected array $singletons = [
        UserService::class,
    ];

    protected array $middleware = [
        'user.auth' => UserAuthMiddleware::class,
    ];

    protected array $policies = [
        User::class => UserPolicy::class,
    ];

    protected array $listen = [
        UserCreated::class => [
            SendWelcomeEmail::class,
            CreateUserProfile::class,
        ],
    ];
}
```

---

## Traits

### Injectable

```php
// Resolve from container anywhere
UserService::make()
UserService::inject()
```

### EmitsEvents (on services)

```php
$this->emit(UserCreated::class, $user);
$this->emitIf($user->isNew(), UserCreated::class, $user);
```

### HasCaching (on services/repositories)

```php
$this->cached("user:{$id}", fn() => User::find($id), 3600);
$this->cachedForever("settings", fn() => Settings::all());
$this->invalidateCache(["user:{$id}", "users:all"]);
```

### HasPipeline (on services)

```php
$result = $this->pipeline($dto, [
    ValidateUserPipe::class,
    HashPasswordPipe::class,
    AssignRolePipe::class,
]);
```

---

## Additional Commands

```bash
# Add to existing module
php artisan module:service   User ExtraService
php artisan module:action    User SendWelcomeEmail
php artisan module:dto       User UpdateProfile
php artisan module:event     User ProfileUpdated
php artisan module:listener  User HandleProfileUpdate
php artisan module:job       User ProcessUserExport
php artisan module:policy    User Post
php artisan module:middleware User ApiThrottle
php artisan module:resource  User UserProfile

# Module management
php artisan module:list
php artisan module:disable Analytics
php artisan module:enable  Analytics
```

---

## Collection Macros

```php
// Map items to DTOs
$dtos = collect($users)->toDto(UserDto::class);

// Paginate in memory
$page = collect($items)->paginate(15, $currentPage);

// Group by multiple keys
$grouped = collect($orders)->groupByMany(['status', 'region']);
```

---

## Experimental: No-Dollar-Sign Mode

Enable in `config/modular.php`:

```php
'transpiler' => ['enabled' => true]
```

Write `.mod.php` files without `$`:

```php
// UserService.mod.php
name = 'John'
user = repository.findBy('name', name)
result = service.process(user)
return result
```

Transpiles to valid PHP automatically.

---

## Module Structure Convention

| File | Purpose |
|------|---------|
| `*ModuleProvider.php` | Module entrypoint, bindings, exports |
| `Services/` | Business logic, injectable, event-aware |
| `Repositories/` | Data access, full CRUD built-in |
| `Actions/` | Single-purpose operations |
| `DTOs/` | Typed input/output objects |
| `Controllers/` | HTTP layer only, delegates to Service |
| `Events/` | Domain events |
| `Listeners/` | Event handlers |
| `Policies/` | Authorization |
| `Resources/` | API response transformation |

---

## License

MIT

# Laravel Modular

> NestJS-inspired modular architecture for Laravel. Write less, do more.

[![PHP](https://img.shields.io/badge/PHP-8.2+-blue)](https://php.net)
[![Laravel](https://img.shields.io/badge/Laravel-10|11|12-red)](https://laravel.com)
[![Author](https://img.shields.io/badge/Author-ouchestech-orange)](https://github.com/hambolu)

---

## Installation

```bash
composer require ouchestech/laravel-modular
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
│   └── UserController.php
├── Services/
│   └── UserService.php
├── Repositories/
│   └── UserRepository.php
├── Models/
│   └── User.php
├── Actions/
│   ├── CreateUserAction.php
│   ├── UpdateUserAction.php
│   └── DeleteUserAction.php
├── DTOs/
│   ├── CreateUserDto.php
│   └── UpdateUserDto.php
├── Events/
│   ├── UserCreated.php
│   ├── UserUpdated.php
│   └── UserDeleted.php
├── Observers/
│   └── UserObserver.php          ← NEW
├── Notifications/                ← NEW
├── Rules/                        ← NEW
├── Contracts/                    ← NEW (interfaces)
├── Policies/
│   └── UserPolicy.php
├── Resources/
│   └── UserResource.php
├── Requests/
│   ├── CreateUserRequest.php
│   └── UpdateUserRequest.php
├── Routes/
│   ├── api.php
│   └── web.php
├── Database/
│   └── migrations/
├── Config/
│   └── user.php
└── Tests/
    └── UserTest.php
```

---

## Inter-Module Communication

```php
// Facade
use LaravelModular\Facades\Module;

$user = Module::call('User@UserService', 'findOrFail', [1]);

// Helper function
$user = module('User@UserService')->findOrFail(1);

// Check existence before calling
Module::whenEnabled('User', fn($m) => module('User@UserService')->findOrFail(1));
```

To allow access, export the service in the module provider:

```php
protected array $exports = [
    'UserService',
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
        return $this->paginated($this->service->paginate());  // includes links + meta
    }

    public function store(CreateUserRequest $request)
    {
        return $this->created(new UserResource($item));  // HTTP 201
    }

    public function destroy(int $id)
    {
        $this->service->delete($id);
        return $this->noContent();  // HTTP 204
    }
}
```

Full response methods:

| Method | Status |
|--------|--------|
| `$this->ok($data)` | 200 |
| `$this->created($data)` | 201 |
| `$this->accepted($data)` | 202 |
| `$this->noContent()` | 204 |
| `$this->badRequest($msg, $errors)` | 400 |
| `$this->unauthorized($msg)` | 401 |
| `$this->forbidden($msg)` | 403 |
| `$this->notFound($msg)` | 404 |
| `$this->conflict($msg)` | 409 |
| `$this->unprocessable($errors)` | 422 |
| `$this->tooManyRequests($msg)` | 429 |
| `$this->serverError($msg)` | 500 |
| `$this->paginated($paginator)` | 200 + meta + links |
| `$this->collection($items)` | 200 |

---

### AbstractRepository

```php
class UserRepository extends AbstractRepository
{
    protected string $model = User::class;

    // Enable full-text search across these columns
    protected array $searchable = ['name', 'email'];
}
```

Built-in methods:

```php
// Standard CRUD
$repo->all()
$repo->find($id)
$repo->findOrFail($id)
$repo->findBy('email', $email)
$repo->findWhere(['role' => 'admin', 'active' => true])
$repo->create($data)
$repo->update($id, $data)
$repo->delete($id)

// Pagination
$repo->paginate(15)
$repo->paginateWhere(['role' => 'admin'], 15)

// NEW: Filter, sort, and search in one call
$repo->filter(
    filters: ['status' => 'active', 'amount' => ['between', [10, 100]]],
    search: 'john',
    sort: ['created_at' => 'desc'],
    perPage: 20
);

// NEW: Full-text search (against $searchable columns)
$repo->search('john doe', perPage: 20);

// Utilities
$repo->count(['active' => true])
$repo->exists(['email' => $email])
$repo->firstOrCreate(['email' => $email], $data)
$repo->updateOrCreate(['email' => $email], $data)
$repo->with(['posts', 'roles'])
$repo->withPaginated(['posts'], 15)
$repo->latest(10)
$repo->oldest(10)
$repo->chunk(100, fn($users) => ...)
$repo->insertBulk($rows)         // bulk insert (no events)
$repo->transaction(fn() => ...)  // wrap in DB transaction

// Soft deletes (if model uses SoftDeletes)
$repo->withTrashed()
$repo->onlyTrashed()
$repo->restore($id)
$repo->forceDelete($id)
$repo->paginateTrashed(15)
```

---

### AbstractDto

```php
class CreateUserDto extends AbstractDto
{
    public string $name  = '';
    public string $email = '';
    public string $role  = 'user';

    // Optional: validation rules
    public function rules(): array
    {
        return [
            'name'  => ['required', 'string'],
            'email' => ['required', 'email'],
        ];
    }
}

// Fill from various sources
$dto = CreateUserDto::from($request->validated());
$dto = CreateUserDto::fromModel($user);
$dto = CreateUserDto::fromRequest($request);

// Validate and throw on failure
$dto->validate();

// Override values
$updated = $dto->with(['role' => 'admin']);

// Extract
$dto->only(['name', 'email']);
$dto->except(['password']);
$dto->toArray();
$dto->toJson();

// Collection
$dtos = CreateUserDto::collection($request->all());
```

---

### AbstractObserver (NEW)

Register observers in your module provider and define lifecycle methods:

```php
// app/Modules/User/UserModuleProvider.php
protected array $observers = [
    User::class => Observers\UserObserver::class,
];
```

```php
// app/Modules/User/Observers/UserObserver.php
class UserObserver extends AbstractObserver
{
    public function created(User $model): void
    {
        // e.g. create profile, send welcome email
    }

    public function deleted(User $model): void
    {
        // e.g. cleanup
    }
}
```

---

### AbstractRule (NEW)

```php
class UniqueEmailRule extends AbstractRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (User::where('email', $value)->exists()) {
            $fail("The :attribute is already taken.");
        }
    }
}

// Usage in a Request
public function rules(): array
{
    return [
        'email' => ['required', 'email', new UniqueEmailRule],
    ];
}
```

---

### AbstractAction

```php
class CreateUserAction extends AbstractAction
{
    public function execute(mixed ...$args): mixed
    {
        [$dto, $role] = $args;
        // ...
    }
}

// Three ways to invoke:
app(CreateUserAction::class)->execute($dto, 'admin');
CreateUserAction::make()->execute($dto);
action(CreateUserAction::class, $dto, 'admin');  // helper
```

---

### AbstractPolicy

```php
class PostPolicy extends AbstractPolicy
{
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

    protected array $observers = [
        User::class => Observers\UserObserver::class,  // NEW
    ];

    protected array $listen = [
        UserCreated::class => [
            SendWelcomeEmail::class,
        ],
    ];

    // NEW: override API version per module (optional)
    protected ?string $apiVersion = 'v2';
}
```

---

## Traits

### Injectable

```php
UserService::make()   // resolve from container
UserService::inject() // alias for make()
```

### EmitsEvents

```php
$this->emit(UserCreated::class, $user);
$this->emitIf($user->isNew(), UserCreated::class, $user);
```

### HasCaching

```php
$this->cached("user:{$id}", fn() => User::find($id), 3600);
$this->cachedForever("settings", fn() => Settings::all());
$this->invalidateCache(["user:{$id}", "users:all"]);
$this->cacheKey('user', $id, 'profile');  // → 'user:123:profile'
```

Keys are automatically prefixed with `modular:` (configurable).

### HasPipeline

```php
$result = $this->pipeline($dto, [
    ValidateUserPipe::class,
    HashPasswordPipe::class,
    AssignRolePipe::class,
]);

// With destination
$result = $this->pipeThrough($dto, [ValidatePipe::class], fn($dto) => $this->repo->create($dto->toArray()));
```

### HasHooks (NEW — on services)

Override `before*` / `after*` methods to add side effects:

```php
class UserService extends AbstractService
{
    public function create(array $data): User
    {
        return $this->withHooks('create', fn($d) => $this->repository->create($d), $data);
    }

    protected function beforeCreate(array $data): array
    {
        $data['slug'] = Str::slug($data['name']);
        return $data;
    }

    protected function afterCreate(User $user): void
    {
        $this->emit(UserCreated::class, $user);
    }
}
```

### HasQueryFilters (NEW — on repositories)

```php
// Simple equality
$this->applyFilters($query, ['status' => 'active']);

// Operators: like | in | not_in | between | null | not_null | > | < | >= | <=
$this->applyFilters($query, ['name' => ['like', '%john%']]);
$this->applyFilters($query, ['role' => ['in', ['admin', 'editor']]]);

// Sort
$this->applySort($query, ['created_at' => 'desc', 'name' => 'asc']);

// Search across columns
$this->applySearch($query, 'john', ['name', 'email', 'username']);
```

---

## Collection Macros

```php
// Cast to DTOs
collect($users)->toDto(UserDto::class);

// In-memory pagination
collect($items)->paginate(15);

// Group by multiple keys
collect($orders)->groupByMany(['status', 'region']);

// Sum a nested key
collect($orders)->sumNested('product.price');

// NEW: map chunk
collect($data)->mapChunks(100, fn($chunk) => $chunk->map(fn($i) => process($i)));
```

---

## Artisan Commands

```bash
# Setup
php artisan modular:setup

# Scaffolding
php artisan module:make        User
php artisan module:make        User --minimal     # core files only
php artisan module:make        User --no-test

# Add to existing module
php artisan module:service      User ExtraService
php artisan module:action       User SendWelcomeEmail
php artisan module:dto          User UpdateProfile
php artisan module:event        User ProfileUpdated
php artisan module:listener     User HandleProfileUpdate
php artisan module:job          User ProcessUserExport
php artisan module:policy       User Post
php artisan module:middleware   User ApiThrottle
php artisan module:resource     User UserProfile
php artisan module:observer     User User       # NEW
php artisan module:notification User WelcomeEmail  # NEW
php artisan module:rule         User UniqueSlug  # NEW
php artisan module:contract     User UserRepository  # NEW

# Module management
php artisan module:list
php artisan module:info   User    # NEW — shows structure + exports
php artisan module:enable  Analytics
php artisan module:disable Analytics
php artisan module:delete  Analytics  # NEW (with confirmation)

# Migrations
php artisan module:migrate User              # NEW
php artisan module:migrate User --rollback
php artisan module:migrate User --fresh
```

---

## API Versioning (NEW)

Enable in `config/modular.php`:

```php
'versioning' => [
    'enabled' => true,
    'default' => 'v1',
],
```

All module API routes will be prefixed with `/api/v1/...`. Override per module:

```php
class UserModuleProvider extends AbstractModule
{
    protected ?string $apiVersion = 'v2';  // → /api/v2/users
}
```

---

## Health Endpoint (NEW)

Enable in `config/modular.php`:

```php
'health' => [
    'enabled'    => true,
    'route'      => '/modular/health',
    'middleware' => ['api'],
],
```

`GET /modular/health` returns:

```json
{
    "status": "ok",
    "modules": [
        { "name": "User", "exports": ["UserService"], "status": "active" },
        { "name": "Order", "exports": ["OrderService"], "status": "active" }
    ],
    "total": 2
}
```

---

## Helper Functions

```php
module('User@UserService')->findOrFail(1)       // get module service
module('User@UserService', 'findOrFail', [1])   // call method directly
module_path('User', 'Services')                 // → app/Modules/User/Services
module_config('user', 'pagination.per_page', 15)// read module config
is_module_enabled('Analytics')                  // bool check
dto(CreateUserDto::class, $data)                // create DTO
action(CreateUserAction::class, $dto)           // execute action
```

---

## Module Structure Convention

| File | Purpose |
|------|---------|
| `*ModuleProvider.php` | Module entrypoint, bindings, exports, observers |
| `Services/` | Business logic, injectable, event-aware, hooks |
| `Repositories/` | Data access, full CRUD, filter/search built-in |
| `Actions/` | Single-purpose operations |
| `DTOs/` | Typed input/output objects with optional validation |
| `Controllers/` | HTTP layer only, delegates to Service |
| `Events/` | Domain events |
| `Listeners/` | Event handlers |
| `Observers/` | Model lifecycle hooks |
| `Notifications/` | Mail/push/database notifications |
| `Rules/` | Custom validation rules |
| `Contracts/` | Interfaces for DI |
| `Policies/` | Authorization gates |
| `Resources/` | API response transformation |

---

## Requirements

- PHP 8.2+
- Laravel 10 / 11 / 12

---

## License

MIT — [ouchestech](https://github.com/hambolu)

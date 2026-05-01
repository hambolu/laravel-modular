<?php

namespace LaravelModular\Commands;

use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Str;

class MakeModuleCommand extends BaseCommand
{
    protected $signature = 'module:make
        {name : The module name (e.g. User, BlogPost)}
        {--base : Generate as base/Core module}
        {--minimal : Only scaffold the essentials}
        {--api : Include API controller and routes}
        {--no-test : Skip test scaffolding}';

    protected $description = 'Create a new module with full scaffolding';

    public function handle(): int
    {
        $name = Str::studly($this->argument('name'));

        if ($this->files->isDirectory($this->modulePath($name))) {
            $this->components->error("Module [{$name}] already exists!");
            return self::FAILURE;
        }

        $this->components->info("Creating module [{$name}]...");
        $this->newLine();

        $structure = config('modular.structure', []);
        $namespace = $this->moduleNamespace() . '\\' . $name;
        $minimal   = $this->option('minimal');

        // Create directory structure
        $this->createDirectories($name, $structure, $minimal);

        // Generate all files
        $this->generateModuleProvider($name, $namespace);
        $this->generateModel($name, $namespace);
        $this->generateController($name, $namespace);
        $this->generateService($name, $namespace);
        $this->generateRepository($name, $namespace);
        $this->generateRoutes($name);
        $this->generateMigration($name);
        $this->generateConfig($name);
        $this->generateRequests($name, $namespace);

        if (!$minimal) {
            $this->generateAction($name, $namespace, 'Create');
            $this->generateAction($name, $namespace, 'Update');
            $this->generateAction($name, $namespace, 'Delete');
            $this->generateDto($name, $namespace, 'Create');
            $this->generateDto($name, $namespace, 'Update');
            $this->generateEvent($name, $namespace, 'Created');
            $this->generateEvent($name, $namespace, 'Updated');
            $this->generateEvent($name, $namespace, 'Deleted');
            $this->generatePolicy($name, $namespace);
            $this->generateResource($name, $namespace);
        }

        if (!$this->option('no-test')) {
            $this->generateTest($name, $namespace);
        }

        $this->newLine();
        $this->components->info("✅  Module [{$name}] created successfully!");
        $this->newLine();
        $this->printSummary($name, $namespace);

        return self::SUCCESS;
    }

    protected function createDirectories(string $name, array $structure, bool $minimal): void
    {
        $dirs = $minimal
            ? ['Controllers', 'Services', 'Models', 'Routes', 'Database/migrations']
            : [
                'Controllers', 'Services', 'Repositories', 'Models',
                'Actions', 'DTOs', 'Events', 'Listeners', 'Jobs',
                'Policies', 'Middleware', 'Resources', 'Requests',
                'Routes', 'Database/migrations', 'Database/seeders',
                'Config', 'Lang', 'Tests',
              ];

        foreach ($dirs as $dir) {
            $path = $this->modulePath($name, $dir);
            if (!$this->files->isDirectory($path)) {
                $this->files->makeDirectory($path, 0755, true);
            }
        }
    }

    protected function generateModuleProvider(string $name, string $namespace): void
    {
        $contents = $this->makeModuleProviderContent($name, $namespace);
        $path     = $this->modulePath($name, "{$name}ModuleProvider.php");
        $this->writeFile($path, $contents);
        $this->components->twoColumnDetail("<fg=green>CREATED</> Module Provider", $path);
    }

    protected function makeModuleProviderContent(string $name, string $namespace): string
    {
        $lower = strtolower($name);
        return <<<PHP
<?php

namespace {$namespace};

use LaravelModular\Abstracts\AbstractModule;

class {$name}ModuleProvider extends AbstractModule
{
    /**
     * Services this module makes available to other modules.
     * Only listed services can be accessed via Module::call() or module() helper.
     */
    protected array \$exports = [
        '{$name}Service',
    ];

    /**
     * Interface => Implementation bindings.
     */
    protected array \$bindings = [
        // Contracts\\{$name}RepositoryInterface::class => Repositories\\{$name}Repository::class,
    ];

    /**
     * Singleton services.
     */
    protected array \$singletons = [
        // Services\\{$name}Service::class,
    ];

    /**
     * Module-level middleware aliases.
     */
    protected array \$middleware = [
        // '{$lower}.auth' => Middleware\\{$name}AuthMiddleware::class,
    ];

    /**
     * Policies: Model => Policy class.
     */
    protected array \$policies = [
        // Models\\{$name}::class => Policies\\{$name}Policy::class,
    ];

    /**
     * Event => Listener bindings.
     */
    protected array \$listen = [
        // Events\\{$name}Created::class => [
        //     Listeners\\Send{$name}CreatedNotification::class,
        // ],
    ];
}
PHP;
    }

    protected function generateModel(string $name, string $namespace): void
    {
        $path = $this->modulePath($name, "Models/{$name}.php");
        $contents = <<<PHP
<?php

namespace {$namespace}\\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class {$name} extends Model
{
    use HasFactory, SoftDeletes;

    protected \$fillable = [
        'name',
    ];

    protected \$casts = [];
}
PHP;
        $this->writeFile($path, $contents);
        $this->components->twoColumnDetail("<fg=green>CREATED</> Model", $path);
    }

    protected function generateController(string $name, string $namespace): void
    {
        $path  = $this->modulePath($name, "Controllers/{$name}Controller.php");
        $lower = strtolower($name);
        $contents = <<<PHP
<?php

namespace {$namespace}\\Controllers;

use LaravelModular\Abstracts\AbstractController;
use {$namespace}\\Services\\{$name}Service;
use {$namespace}\\Requests\\Create{$name}Request;
use {$namespace}\\Requests\\Update{$name}Request;
use {$namespace}\\Resources\\{$name}Resource;

class {$name}Controller extends AbstractController
{
    public function __construct(
        protected {$name}Service \$service
    ) {}

    public function index()
    {
        \$items = \$this->service->paginate();
        return \$this->paginated(\$items);
    }

    public function show(int \$id)
    {
        \$item = \$this->service->findOrFail(\$id);
        return \$this->ok(new {$name}Resource(\$item));
    }

    public function store(Create{$name}Request \$request)
    {
        \$item = \$this->service->create(\$request->validated());
        return \$this->created(new {$name}Resource(\$item));
    }

    public function update(Update{$name}Request \$request, int \$id)
    {
        \$item = \$this->service->update(\$id, \$request->validated());
        return \$this->ok(new {$name}Resource(\$item));
    }

    public function destroy(int \$id)
    {
        \$this->service->delete(\$id);
        return \$this->noContent();
    }
}
PHP;
        $this->writeFile($path, $contents);
        $this->components->twoColumnDetail("<fg=green>CREATED</> Controller", $path);
    }

    protected function generateService(string $name, string $namespace): void
    {
        $path = $this->modulePath($name, "Services/{$name}Service.php");
        $contents = <<<PHP
<?php

namespace {$namespace}\\Services;

use LaravelModular\Abstracts\AbstractService;
use {$namespace}\\Repositories\\{$name}Repository;
use {$namespace}\\Models\\{$name};
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class {$name}Service extends AbstractService
{
    public function __construct(
        protected {$name}Repository \$repository
    ) {}

    public function paginate(int \$perPage = 15): LengthAwarePaginator
    {
        return \$this->repository->paginate(\$perPage);
    }

    public function findOrFail(int \$id): {$name}
    {
        return \$this->repository->findOrFail(\$id);
    }

    public function create(array \$data): {$name}
    {
        \$item = \$this->repository->create(\$data);

        // \$this->emit(\\{$namespace}\\Events\\{$name}Created::class, \$item);

        return \$item;
    }

    public function update(int \$id, array \$data): {$name}
    {
        return \$this->repository->update(\$id, \$data);
    }

    public function delete(int \$id): bool
    {
        return \$this->repository->delete(\$id);
    }
}
PHP;
        $this->writeFile($path, $contents);
        $this->components->twoColumnDetail("<fg=green>CREATED</> Service", $path);
    }

    protected function generateRepository(string $name, string $namespace): void
    {
        $path = $this->modulePath($name, "Repositories/{$name}Repository.php");
        $contents = <<<PHP
<?php

namespace {$namespace}\\Repositories;

use LaravelModular\Abstracts\AbstractRepository;
use {$namespace}\\Models\\{$name};

class {$name}Repository extends AbstractRepository
{
    protected string \$model = {$name}::class;
}
PHP;
        $this->writeFile($path, $contents);
        $this->components->twoColumnDetail("<fg=green>CREATED</> Repository", $path);
    }

    protected function generateRoutes(string $name): void
    {
        $lower = Str::kebab($name);
        $apiPath = $this->modulePath($name, 'Routes/api.php');
        $apiContents = <<<PHP
<?php

use Illuminate\Support\Facades\Route;
use App\Modules\\{$name}\\Controllers\\{$name}Controller;

Route::apiResource('{$lower}s', {$name}Controller::class);
PHP;
        $this->writeFile($apiPath, $apiContents);
        $this->components->twoColumnDetail("<fg=green>CREATED</> API Routes", $apiPath);

        $webPath = $this->modulePath($name, 'Routes/web.php');
        $this->writeFile($webPath, "<?php\n\n// Web routes for the {$name} module\n");
        $this->components->twoColumnDetail("<fg=green>CREATED</> Web Routes", $webPath);
    }

    protected function generateMigration(string $name): void
    {
        $table = Str::plural(Str::snake($name));
        $ts    = date('Y_m_d_His');
        $path  = $this->modulePath($name, "Database/migrations/{$ts}_create_{$table}_table.php");
        $contents = <<<PHP
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('{$table}', function (Blueprint \$table) {
            \$table->id();
            \$table->string('name');
            \$table->timestamps();
            \$table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('{$table}');
    }
};
PHP;
        $this->writeFile($path, $contents);
        $this->components->twoColumnDetail("<fg=green>CREATED</> Migration", $path);
    }

    protected function generateConfig(string $name): void
    {
        $lower = strtolower($name);
        $path  = $this->modulePath($name, "Config/{$lower}.php");
        $contents = <<<PHP
<?php

return [
    'name'    => '{$name}',
    'enabled' => true,
    // Module-specific configuration
];
PHP;
        $this->writeFile($path, $contents);
        $this->components->twoColumnDetail("<fg=green>CREATED</> Config", $path);
    }

    protected function generateRequests(string $name, string $namespace): void
    {
        foreach (['Create', 'Update'] as $prefix) {
            $path = $this->modulePath($name, "Requests/{$prefix}{$name}Request.php");
            $contents = <<<PHP
<?php

namespace {$namespace}\\Requests;

use Illuminate\Foundation\Http\FormRequest;

class {$prefix}{$name}Request extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['" . ($prefix === 'Create' ? "required" : "sometimes") . "', 'string', 'max:255'],
        ];
    }
}
PHP;
            $this->writeFile($path, $contents);
            $this->components->twoColumnDetail("<fg=green>CREATED</> {$prefix} Request", $path);
        }
    }

    protected function generateAction(string $name, string $namespace, string $verb): void
    {
        $path = $this->modulePath($name, "Actions/{$verb}{$name}Action.php");
        $contents = <<<PHP
<?php

namespace {$namespace}\\Actions;

use LaravelModular\Abstracts\AbstractAction;
use {$namespace}\\Services\\{$name}Service;

class {$verb}{$name}Action extends AbstractAction
{
    public function __construct(
        protected {$name}Service \$service
    ) {}

    public function execute(mixed ...\$args): mixed
    {
        // Implement the {$verb} action
        return null;
    }
}
PHP;
        $this->writeFile($path, $contents);
        $this->components->twoColumnDetail("<fg=green>CREATED</> Action: {$verb}", $path);
    }

    protected function generateDto(string $name, string $namespace, string $prefix): void
    {
        $path = $this->modulePath($name, "DTOs/{$prefix}{$name}Dto.php");
        $contents = <<<PHP
<?php

namespace {$namespace}\\DTOs;

use LaravelModular\Abstracts\AbstractDto;

class {$prefix}{$name}Dto extends AbstractDto
{
    public string \$name = '';

    // Add more properties here
    // They will automatically be filled by static::from(\$data)
}
PHP;
        $this->writeFile($path, $contents);
        $this->components->twoColumnDetail("<fg=green>CREATED</> DTO: {$prefix}", $path);
    }

    protected function generateEvent(string $name, string $namespace, string $suffix): void
    {
        $path = $this->modulePath($name, "Events/{$name}{$suffix}.php");
        $contents = <<<PHP
<?php

namespace {$namespace}\\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use {$namespace}\\Models\\{$name};

class {$name}{$suffix}
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public readonly {$name} \$model
    ) {}
}
PHP;
        $this->writeFile($path, $contents);
        $this->components->twoColumnDetail("<fg=green>CREATED</> Event: {$suffix}", $path);
    }

    protected function generatePolicy(string $name, string $namespace): void
    {
        $path = $this->modulePath($name, "Policies/{$name}Policy.php");
        $contents = <<<PHP
<?php

namespace {$namespace}\\Policies;

use LaravelModular\Abstracts\AbstractPolicy;
use App\Models\User;
use {$namespace}\\Models\\{$name};

class {$name}Policy extends AbstractPolicy
{
    // Override only what you need.
    // AbstractPolicy provides: viewAny, view, create, update, delete, restore, forceDelete
    // Admin bypass is already handled in before().
}
PHP;
        $this->writeFile($path, $contents);
        $this->components->twoColumnDetail("<fg=green>CREATED</> Policy", $path);
    }

    protected function generateResource(string $name, string $namespace): void
    {
        $path = $this->modulePath($name, "Resources/{$name}Resource.php");
        $contents = <<<PHP
<?php

namespace {$namespace}\\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class {$name}Resource extends JsonResource
{
    public function toArray(\$request): array
    {
        return [
            'id'         => \$this->id,
            'name'       => \$this->name,
            'created_at' => \$this->created_at?->toISOString(),
            'updated_at' => \$this->updated_at?->toISOString(),
        ];
    }
}
PHP;
        $this->writeFile($path, $contents);
        $this->components->twoColumnDetail("<fg=green>CREATED</> Resource", $path);
    }

    protected function generateTest(string $name, string $namespace): void
    {
        $path = $this->modulePath($name, "Tests/{$name}Test.php");
        $lower = strtolower($name);
        $contents = <<<PHP
<?php

namespace {$namespace}\\Tests;

use Tests\\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use {$namespace}\\Models\\{$name};

class {$name}Test extends TestCase
{
    use RefreshDatabase;

    public function test_can_list_{$lower}s(): void
    {
        {$name}::factory()->count(3)->create();

        \$this->getJson('/api/{$lower}s')
            ->assertOk()
            ->assertJsonPath('status', 'success');
    }

    public function test_can_create_{$lower}(): void
    {
        \$payload = ['name' => 'Test {$name}'];

        \$this->postJson('/api/{$lower}s', \$payload)
            ->assertCreated()
            ->assertJsonPath('data.name', 'Test {$name}');
    }

    public function test_can_show_{$lower}(): void
    {
        \$item = {$name}::factory()->create();

        \$this->getJson("/api/{$lower}s/{\$item->id}")
            ->assertOk()
            ->assertJsonPath('data.id', \$item->id);
    }

    public function test_can_update_{$lower}(): void
    {
        \$item = {$name}::factory()->create();

        \$this->putJson("/api/{$lower}s/{\$item->id}", ['name' => 'Updated'])
            ->assertOk()
            ->assertJsonPath('data.name', 'Updated');
    }

    public function test_can_delete_{$lower}(): void
    {
        \$item = {$name}::factory()->create();

        \$this->deleteJson("/api/{$lower}s/{\$item->id}")
            ->assertNoContent();
    }
}
PHP;
        $this->writeFile($path, $contents);
        $this->components->twoColumnDetail("<fg=green>CREATED</> Test", $path);
    }

    protected function printSummary(string $name, string $namespace): void
    {
        $this->line("  <fg=cyan>Usage:</>  Access from other modules:");
        $this->line("  <fg=yellow>// In any module, controller, or service:</>");
        $this->line("  <fg=green>module('{$name}@{$name}Service')->findOrFail(1);</>");
        $this->line("  <fg=green>Module::call('{$name}@{$name}Service', 'create', [\$data]);</>");
        $this->newLine();
        $this->line("  <fg=cyan>Run migrations:</> <fg=green>php artisan migrate</>");
    }
}

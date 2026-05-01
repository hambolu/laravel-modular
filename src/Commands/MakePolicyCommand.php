<?php

namespace LaravelModular\Commands;

use Illuminate\Support\Str;

class MakePolicyCommand extends BaseCommand
{
    protected $signature   = 'module:policy {module} {name}';
    protected $description = 'Add a new Policy to an existing module';

    public function handle(): int
    {
        $module    = Str::studly($this->argument('module'));
        $name      = Str::studly($this->argument('name'));
        $namespace = $this->moduleNamespace() . '\\' . $module;
        $path      = $this->modulePath($module, "Policies/{$name}Policy.php");

        $contents = <<<PHP
<?php

namespace {$namespace}\\Policies;

use LaravelModular\Abstracts\AbstractPolicy;
use App\Models\User;
use {$namespace}\\Models\\{$name};

class {$name}Policy extends AbstractPolicy
{
    // All CRUD gates + admin bypass are pre-wired in AbstractPolicy.
    // Override only what you need to customize.
}
PHP;
        $this->writeFile($path, $contents);
        $this->components->info("Policy [{$name}Policy] created in module [{$module}].");
        return self::SUCCESS;
    }
}

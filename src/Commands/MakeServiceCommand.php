<?php

namespace LaravelModular\Commands;

use Illuminate\Support\Str;

class MakeServiceCommand extends BaseCommand
{
    protected $signature   = 'module:service {module} {name}';
    protected $description = 'Add a new Service to an existing module';

    public function handle(): int
    {
        $module    = Str::studly($this->argument('module'));
        $name      = Str::studly($this->argument('name'));
        $namespace = $this->moduleNamespace() . '\\' . $module;
        $path      = $this->modulePath($module, "Services/{$name}Service.php");

        if ($this->files->exists($path)) {
            $this->components->error("Service [{$name}Service] already exists in module [{$module}].");
            return self::FAILURE;
        }

        $contents = <<<PHP
<?php

namespace {$namespace}\\Services;

use LaravelModular\Abstracts\AbstractService;

class {$name}Service extends AbstractService
{
    // Your service methods here
}
PHP;
        $this->writeFile($path, $contents);
        $this->components->info("Service [{$name}Service] created in module [{$module}].");
        return self::SUCCESS;
    }
}

<?php

namespace LaravelModular\Commands;

use Illuminate\Support\Str;

class MakeListenerCommand extends BaseCommand
{
    protected $signature   = 'module:listener {module} {name}';
    protected $description = 'Add a new Listener to an existing module';

    public function handle(): int
    {
        $module    = Str::studly($this->argument('module'));
        $name      = Str::studly($this->argument('name'));
        $namespace = $this->moduleNamespace() . '\\' . $module;
        $path      = $this->modulePath($module, "Listeners/{$name}.php");

        $contents = <<<PHP
<?php

namespace {$namespace}\\Listeners;

class {$name}
{
    public function handle(object \$event): void
    {
        // Handle the event
    }
}
PHP;
        $this->writeFile($path, $contents);
        $this->components->info("Listener [{$name}] created in module [{$module}].");
        return self::SUCCESS;
    }
}

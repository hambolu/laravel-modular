<?php

namespace LaravelModular\Commands;

use Illuminate\Support\Str;

class MakeActionCommand extends BaseCommand
{
    protected $signature   = 'module:action {module} {name}';
    protected $description = 'Add a new Action to an existing module';

    public function handle(): int
    {
        $module    = Str::studly($this->argument('module'));
        $name      = Str::studly($this->argument('name'));
        $namespace = $this->moduleNamespace() . '\\' . $module;
        $path      = $this->modulePath($module, "Actions/{$name}Action.php");

        $contents = <<<PHP
<?php

namespace {$namespace}\\Actions;

use LaravelModular\Abstracts\AbstractAction;

class {$name}Action extends AbstractAction
{
    public function execute(mixed ...\$args): mixed
    {
        // Implement the action
        return null;
    }
}
PHP;
        $this->writeFile($path, $contents);
        $this->components->info("Action [{$name}Action] created in module [{$module}].");
        return self::SUCCESS;
    }
}

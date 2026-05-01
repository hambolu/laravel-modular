<?php

namespace LaravelModular\Commands;

use Illuminate\Support\Str;

class MakeControllerCommand extends BaseCommand
{
    protected $signature   = 'module:controller {module} {name}';
    protected $description = 'Add a new Controller to an existing module';

    public function handle(): int
    {
        $module    = Str::studly($this->argument('module'));
        $name      = Str::studly($this->argument('name'));
        $namespace = $this->moduleNamespace() . '\\' . $module;
        $path      = $this->modulePath($module, "Controllers/{$name}Controller.php");

        if ($this->files->exists($path)) {
            $this->components->error("Controller [{$name}Controller] already exists in module [{$module}].");
            return self::FAILURE;
        }

        $contents = <<<PHP
<?php

namespace {$namespace}\\Controllers;

use LaravelModular\Abstracts\AbstractController;

class {$name}Controller extends AbstractController
{
    public function index()
    {
        return \$this->ok();
    }
}
PHP;
        $this->writeFile($path, $contents);
        $this->components->info("Controller [{$name}Controller] created in module [{$module}].");
        return self::SUCCESS;
    }
}

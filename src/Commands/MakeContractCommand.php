<?php

namespace LaravelModular\Commands;

use Illuminate\Support\Str;

class MakeContractCommand extends BaseCommand
{
    protected $signature = 'module:contract {module : Module name} {name : Interface name}';
    protected $description = 'Create a contract (interface) in a module';

    public function handle(): int
    {
        $module    = Str::studly($this->argument('module'));
        $name      = Str::studly($this->argument('name'));
        $namespace = $this->moduleNamespace() . '\\' . $module;
        $path      = $this->modulePath($module, "Contracts/{$name}Interface.php");

        if ($this->files->exists($path)) {
            $this->components->warn("Contract [{$name}Interface] already exists!");
            return self::FAILURE;
        }

        $contents = <<<PHP
<?php

namespace {$namespace}\\Contracts;

interface {$name}Interface
{
    //
}
PHP;
        $this->writeFile($path, $contents);
        $this->success("Contract [{$name}Interface] created at [{$path}]");
        return self::SUCCESS;
    }
}

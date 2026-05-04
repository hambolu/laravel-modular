<?php

namespace LaravelModular\Commands;

use Illuminate\Support\Str;

class MakeObserverCommand extends BaseCommand
{
    protected $signature = 'module:observer {module : Module name} {model? : Model to observe}';
    protected $description = 'Create a new observer in a module';

    public function handle(): int
    {
        $module    = Str::studly($this->argument('module'));
        $model     = Str::studly($this->argument('model') ?? $module);
        $namespace = $this->moduleNamespace() . '\\' . $module;
        $path      = $this->modulePath($module, "Observers/{$model}Observer.php");

        if ($this->files->exists($path)) {
            $this->components->warn("Observer [{$model}Observer] already exists!");
            return self::FAILURE;
        }

        $contents = <<<PHP
<?php

namespace {$namespace}\\Observers;

use LaravelModular\\Abstracts\\AbstractObserver;
use {$namespace}\\Models\\{$model};

class {$model}Observer extends AbstractObserver
{
    public function created({$model} \$model): void
    {
        //
    }

    public function updated({$model} \$model): void
    {
        //
    }

    public function deleted({$model} \$model): void
    {
        //
    }
}
PHP;
        $this->writeFile($path, $contents);
        $this->success("Observer [{$model}Observer] created at [{$path}]");
        return self::SUCCESS;
    }
}

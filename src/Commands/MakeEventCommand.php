<?php

namespace LaravelModular\Commands;

use Illuminate\Support\Str;

class MakeEventCommand extends BaseCommand
{
    protected $signature   = 'module:event {module} {name}';
    protected $description = 'Add a new Event to an existing module';

    public function handle(): int
    {
        $module    = Str::studly($this->argument('module'));
        $name      = Str::studly($this->argument('name'));
        $namespace = $this->moduleNamespace() . '\\' . $module;
        $path      = $this->modulePath($module, "Events/{$name}.php");

        $contents = <<<PHP
<?php

namespace {$namespace}\\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class {$name}
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public readonly mixed \$payload = null
    ) {}
}
PHP;
        $this->writeFile($path, $contents);
        $this->components->info("Event [{$name}] created in module [{$module}].");
        return self::SUCCESS;
    }
}

<?php

namespace LaravelModular\Commands;

use Illuminate\Support\Str;

class MakeRuleCommand extends BaseCommand
{
    protected $signature = 'module:rule {module : Module name} {name : Rule name}';
    protected $description = 'Create a custom validation rule in a module';

    public function handle(): int
    {
        $module    = Str::studly($this->argument('module'));
        $name      = Str::studly($this->argument('name'));
        $namespace = $this->moduleNamespace() . '\\' . $module;
        $path      = $this->modulePath($module, "Rules/{$name}.php");

        if ($this->files->exists($path)) {
            $this->components->warn("Rule [{$name}] already exists!");
            return self::FAILURE;
        }

        $contents = <<<PHP
<?php

namespace {$namespace}\\Rules;

use Closure;
use LaravelModular\\Abstracts\\AbstractRule;

class {$name} extends AbstractRule
{
    public function validate(string \$attribute, mixed \$value, Closure \$fail): void
    {
        // if (some condition fails) {
        //     \$fail("The :attribute is invalid.");
        // }
    }
}
PHP;
        $this->writeFile($path, $contents);
        $this->success("Rule [{$name}] created at [{$path}]");
        return self::SUCCESS;
    }
}

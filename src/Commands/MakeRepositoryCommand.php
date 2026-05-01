<?php

namespace LaravelModular\Commands;

use Illuminate\Support\Str;

class MakeRepositoryCommand extends BaseCommand
{
    protected $signature   = 'module:repository {module} {name}';
    protected $description = 'Add a new Repository to an existing module';

    public function handle(): int
    {
        $module    = Str::studly($this->argument('module'));
        $name      = Str::studly($this->argument('name'));
        $namespace = $this->moduleNamespace() . '\\' . $module;
        $path      = $this->modulePath($module, "Repositories/{$name}Repository.php");

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
        $this->components->info("Repository [{$name}Repository] created in module [{$module}].");
        return self::SUCCESS;
    }
}

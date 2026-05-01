<?php

namespace LaravelModular\Commands;

use Illuminate\Support\Str;

class MakeDtoCommand extends BaseCommand
{
    protected $signature   = 'module:dto {module} {name}';
    protected $description = 'Add a new DTO to an existing module';

    public function handle(): int
    {
        $module    = Str::studly($this->argument('module'));
        $name      = Str::studly($this->argument('name'));
        $namespace = $this->moduleNamespace() . '\\' . $module;
        $path      = $this->modulePath($module, "DTOs/{$name}Dto.php");

        $contents = <<<PHP
<?php

namespace {$namespace}\\DTOs;

use LaravelModular\Abstracts\AbstractDto;

class {$name}Dto extends AbstractDto
{
    public string \$name = '';
}
PHP;
        $this->writeFile($path, $contents);
        $this->components->info("DTO [{$name}Dto] created in module [{$module}].");
        return self::SUCCESS;
    }
}

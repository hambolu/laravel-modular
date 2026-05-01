<?php

namespace LaravelModular\Commands;

use Illuminate\Support\Str;

class MakeResourceCommand extends BaseCommand
{
    protected $signature   = 'module:resource {module} {name}';
    protected $description = 'Add a new API Resource to an existing module';

    public function handle(): int
    {
        $module    = Str::studly($this->argument('module'));
        $name      = Str::studly($this->argument('name'));
        $namespace = $this->moduleNamespace() . '\\' . $module;
        $path      = $this->modulePath($module, "Resources/{$name}Resource.php");

        $contents = <<<PHP
<?php

namespace {$namespace}\\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class {$name}Resource extends JsonResource
{
    public function toArray(\$request): array
    {
        return [
            'id'         => \$this->id,
            'created_at' => \$this->created_at?->toISOString(),
            'updated_at' => \$this->updated_at?->toISOString(),
        ];
    }
}
PHP;
        $this->writeFile($path, $contents);
        $this->components->info("Resource [{$name}Resource] created in module [{$module}].");
        return self::SUCCESS;
    }
}

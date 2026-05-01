<?php

namespace LaravelModular\Commands;

use Illuminate\Support\Str;

class MakeMiddlewareCommand extends BaseCommand
{
    protected $signature   = 'module:middleware {module} {name}';
    protected $description = 'Add a new Middleware to an existing module';

    public function handle(): int
    {
        $module    = Str::studly($this->argument('module'));
        $name      = Str::studly($this->argument('name'));
        $namespace = $this->moduleNamespace() . '\\' . $module;
        $path      = $this->modulePath($module, "Middleware/{$name}Middleware.php");

        $contents = <<<PHP
<?php

namespace {$namespace}\\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class {$name}Middleware
{
    public function handle(Request \$request, Closure \$next): Response
    {
        return \$next(\$request);
    }
}
PHP;
        $this->writeFile($path, $contents);
        $this->components->info("Middleware [{$name}Middleware] created in module [{$module}].");
        return self::SUCCESS;
    }
}

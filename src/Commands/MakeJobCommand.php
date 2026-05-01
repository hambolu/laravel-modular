<?php

namespace LaravelModular\Commands;

use Illuminate\Support\Str;

class MakeJobCommand extends BaseCommand
{
    protected $signature   = 'module:job {module} {name}';
    protected $description = 'Add a new Job to an existing module';

    public function handle(): int
    {
        $module    = Str::studly($this->argument('module'));
        $name      = Str::studly($this->argument('name'));
        $namespace = $this->moduleNamespace() . '\\' . $module;
        $path      = $this->modulePath($module, "Jobs/{$name}Job.php");

        $contents = <<<PHP
<?php

namespace {$namespace}\\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class {$name}Job implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public readonly mixed \$payload = null
    ) {}

    public function handle(): void
    {
        // Process the job
    }
}
PHP;
        $this->writeFile($path, $contents);
        $this->components->info("Job [{$name}Job] created in module [{$module}].");
        return self::SUCCESS;
    }
}

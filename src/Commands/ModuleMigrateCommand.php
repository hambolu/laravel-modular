<?php

namespace LaravelModular\Commands;

use Illuminate\Support\Str;

class ModuleMigrateCommand extends BaseCommand
{
    protected $signature = 'module:migrate
        {name : Module name}
        {--rollback : Rollback instead of migrate}
        {--fresh : Drop all and re-migrate}
        {--step=1 : Steps for rollback}';
    protected $description = 'Run migrations for a specific module';

    public function handle(): int
    {
        $name = Str::studly($this->argument('name'));
        $path = $this->modulePath($name, 'Database/migrations');

        if (!$this->files->isDirectory($path)) {
            $this->components->error("No migrations directory found for module [{$name}].");
            return self::FAILURE;
        }

        $relativePath = 'app/Modules/' . $name . '/Database/migrations';

        if ($this->option('fresh')) {
            $this->call('migrate:fresh', ['--path' => $relativePath]);
        } elseif ($this->option('rollback')) {
            $this->call('migrate:rollback', [
                '--path' => $relativePath,
                '--step' => $this->option('step'),
            ]);
        } else {
            $this->call('migrate', ['--path' => $relativePath]);
        }

        return self::SUCCESS;
    }
}

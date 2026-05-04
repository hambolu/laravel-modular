<?php

namespace LaravelModular\Commands;

use Illuminate\Support\Str;

class ModuleDeleteCommand extends BaseCommand
{
    protected $signature = 'module:delete {name : Module name} {--force : Skip confirmation}';
    protected $description = 'Delete a module and all its files';

    public function handle(): int
    {
        $name = Str::studly($this->argument('name'));
        $path = $this->modulePath($name);

        if (!$this->files->isDirectory($path)) {
            $this->components->error("Module [{$name}] not found.");
            return self::FAILURE;
        }

        if (!$this->option('force')) {
            if (!$this->components->confirm("Are you sure you want to delete module [{$name}]? This cannot be undone.")) {
                $this->line('Aborted.');
                return self::SUCCESS;
            }
        }

        $this->files->deleteDirectory($path);
        $this->success("Module [{$name}] deleted.");
        return self::SUCCESS;
    }
}

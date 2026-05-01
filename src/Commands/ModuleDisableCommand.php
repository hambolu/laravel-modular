<?php

namespace LaravelModular\Commands;

use LaravelModular\Module\ModuleRegistry;

class ModuleDisableCommand extends BaseCommand
{
    protected $signature   = 'module:disable {name}';
    protected $description = 'Disable a module without removing it';

    public function handle(): int
    {
        $name = $this->argument('name');
        app(ModuleRegistry::class)->disable($name);
        $this->components->info("Module [{$name}] disabled.");
        return self::SUCCESS;
    }
}

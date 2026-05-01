<?php

namespace LaravelModular\Commands;

use LaravelModular\Module\ModuleRegistry;

class ModuleEnableCommand extends BaseCommand
{
    protected $signature   = 'module:enable {name}';
    protected $description = 'Enable a disabled module';

    public function handle(): int
    {
        $name = $this->argument('name');
        app(ModuleRegistry::class)->enable($name);
        $this->components->info("Module [{$name}] enabled.");
        return self::SUCCESS;
    }
}

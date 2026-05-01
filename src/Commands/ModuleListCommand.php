<?php

namespace LaravelModular\Commands;

use LaravelModular\Module\ModuleRegistry;

class ModuleListCommand extends BaseCommand
{
    protected $signature   = 'module:list';
    protected $description = 'List all registered modules';

    public function handle(): int
    {
        $registry = app(ModuleRegistry::class);
        $modules  = $registry->all();

        if (empty($modules)) {
            $this->components->warn('No modules found. Run php artisan module:make YourModule');
            return self::SUCCESS;
        }

        $rows = [];
        foreach ($modules as $name => $module) {
            $rows[] = [
                $name,
                $registry->isEnabled($name) ? '<fg=green>Enabled</>' : '<fg=red>Disabled</>',
                implode(', ', $module->getExports()) ?: '<fg=gray>none</>',
            ];
        }

        $this->table(['Module', 'Status', 'Exported Services'], $rows);
        return self::SUCCESS;
    }
}

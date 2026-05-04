<?php

namespace LaravelModular\Commands;

use LaravelModular\Module\ModuleRegistry;

class ModuleListCommand extends BaseCommand
{
    protected $signature = 'module:list';
    protected $description = 'List all registered modules';

    public function handle(): int
    {
        /** @var ModuleRegistry $registry */
        $registry = app(ModuleRegistry::class);
        $modules  = $registry->all();

        if (empty($modules)) {
            $this->components->warn('No modules are currently loaded.');
            return self::SUCCESS;
        }

        $rows = [];
        foreach ($modules as $name => $module) {
            $exports = $module->getExports();
            $rows[]  = [
                $name,
                $registry->isEnabled($name) ? '<fg=green>enabled</>' : '<fg=red>disabled</>',
                $exports ? implode(', ', $exports) : '<fg=gray>none</>',
            ];
        }

        $this->table(['Module', 'Status', 'Exports'], $rows);
        $this->newLine();
        $this->line("  Total: <fg=cyan>" . count($modules) . "</> modules loaded.");
        $this->newLine();
        return self::SUCCESS;
    }
}

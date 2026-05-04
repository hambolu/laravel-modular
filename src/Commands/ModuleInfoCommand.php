<?php

namespace LaravelModular\Commands;

use Illuminate\Support\Str;
use LaravelModular\Module\ModuleRegistry;

class ModuleInfoCommand extends BaseCommand
{
    protected $signature = 'module:info {name : Module name}';
    protected $description = 'Show detailed info about a module';

    public function handle(): int
    {
        $name = Str::studly($this->argument('name'));

        // Filesystem info
        $path = $this->modulePath($name);
        if (!$this->files->isDirectory($path)) {
            $this->components->error("Module [{$name}] not found at [{$path}].");
            return self::FAILURE;
        }

        /** @var ModuleRegistry $registry */
        $registry = app(ModuleRegistry::class);
        $module   = $registry->get($name);

        $this->newLine();
        $this->line("  <fg=cyan>Module:</> <fg=white>{$name}</>");
        $this->line("  <fg=cyan>Path:</>   {$path}");
        $this->line("  <fg=cyan>Status:</> " . ($registry->isEnabled($name) ? '<fg=green>enabled</>' : '<fg=red>disabled</>'));

        if ($module) {
            $exports = $module->getExports();
            if ($exports) {
                $this->line("  <fg=cyan>Exports:</> " . implode(', ', $exports));
            }
        }

        // Count files per folder
        $this->newLine();
        $this->line("  <fg=cyan>Structure:</>");
        $dirs = glob($path . '/*', GLOB_ONLYDIR);
        foreach ($dirs as $dir) {
            $count = count(glob($dir . '/*.php') ?: []);
            $folder = basename($dir);
            $this->line("    <fg=green>{$folder}</> ({$count} files)");
        }

        $this->newLine();
        return self::SUCCESS;
    }
}

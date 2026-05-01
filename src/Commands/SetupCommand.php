<?php

namespace LaravelModular\Commands;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;

class SetupCommand extends Command
{
    protected $signature   = 'modular:setup {--force : Overwrite existing files}';
    protected $description = 'Set up the modular architecture in your Laravel application';

    public function handle(): int
    {
        $this->displayBanner();

        $path = config('modular.path', app_path('Modules'));
        $files = new Filesystem();

        // Create Modules directory
        if (!$files->isDirectory($path)) {
            $files->makeDirectory($path, 0755, true);
            $this->components->info("Created: Modules directory at [{$path}]");
        }

        // Publish config
        $this->callSilently('vendor:publish', ['--tag' => 'modular-config', '--force' => $this->option('force')]);
        $this->components->info('Published: config/modular.php');

        // Create the Core module
        $this->components->info('Creating base [Core] module...');
        $this->call('module:make', ['name' => 'Core', '--base' => true]);

        // Add Modules autoload to composer.json if needed
        $this->suggestComposerUpdate();

        $this->newLine();
        $this->components->info('✅  Modular setup complete!');
        $this->newLine();
        $this->line('  <fg=cyan>Next steps:</>');
        $this->line('  1. Run <fg=green>composer dump-autoload</>');
        $this->line('  2. Create your first module: <fg=green>php artisan module:make YourModule</>');
        $this->line('  3. Read the docs: <fg=cyan>https://laravelmodular.dev</>');
        $this->newLine();

        return self::SUCCESS;
    }

    protected function displayBanner(): void
    {
        $this->newLine();
        $this->line('<fg=cyan>
  ██╗      █████╗ ██████╗  █████╗ ██╗   ██╗███████╗██╗         
  ██║     ██╔══██╗██╔══██╗██╔══██╗██║   ██║██╔════╝██║         
  ██║     ███████║██████╔╝███████║██║   ██║█████╗  ██║         
  ██║     ██╔══██║██╔══██╗██╔══██║╚██╗ ██╔╝██╔══╝  ██║         
  ███████╗██║  ██║██║  ██║██║  ██║ ╚████╔╝ ███████╗███████╗    
  ╚══════╝╚═╝  ╚═╝╚═╝  ╚═╝╚═╝  ╚═╝  ╚═══╝  ╚══════╝╚══════╝    
  <fg=white>MODULAR</> — NestJS-inspired architecture for Laravel</>');
        $this->newLine();
    }

    protected function suggestComposerUpdate(): void
    {
        $composerPath = base_path('composer.json');
        if (!file_exists($composerPath)) return;

        $composer = json_decode(file_get_contents($composerPath), true);
        $ns       = config('modular.namespace', 'App\\Modules');
        $nsKey    = rtrim($ns, '\\') . '\\';
        $relPath  = 'app/Modules/';

        if (!isset($composer['autoload']['psr-4'][$nsKey])) {
            $this->components->warn("Add this to your composer.json autoload.psr-4:");
            $this->line("  <fg=yellow>\"$nsKey\": \"$relPath\"</>");
            $this->line("  Then run: <fg=green>composer dump-autoload</>");
        }
    }
}

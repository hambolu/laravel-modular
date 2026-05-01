<?php

namespace LaravelModular\Module;

use Illuminate\Contracts\Foundation\Application;
use Symfony\Component\Finder\Finder;

class ModuleLoader
{
    public function __construct(
        protected Application $app,
        protected ModuleRegistry $registry
    ) {}

    public function loadAll(): void
    {
        if (!config('modular.auto_discover', true)) {
            return;
        }

        $path = config('modular.path', app_path('Modules'));

        if (!is_dir($path)) {
            return;
        }

        $providers = $this->discoverProviders($path);

        foreach ($providers as $provider) {
            $moduleName = $this->extractModuleName($provider);
            if ($this->registry->isEnabled($moduleName)) {
                $this->app->register($provider);
            }
        }
    }

    protected function discoverProviders(string $path): array
    {
        $providers = [];
        $namespace = config('modular.namespace', 'App\\Modules');

        try {
            $finder = Finder::create()
                ->in($path)
                ->name('*ModuleProvider.php')
                ->depth('== 1');

            foreach ($finder as $file) {
                $relativePath = str_replace([$path . DIRECTORY_SEPARATOR, '.php'], '', $file->getPathname());
                $class = $namespace . '\\' . str_replace(DIRECTORY_SEPARATOR, '\\', $relativePath);

                if (class_exists($class)) {
                    $providers[] = $class;
                }
            }
        } catch (\Exception $e) {
            // Path issues — silently skip
        }

        return $providers;
    }

    protected function extractModuleName(string $providerClass): string
    {
        $parts = explode('\\', $providerClass);
        return $parts[count($parts) - 2] ?? 'Unknown';
    }
}

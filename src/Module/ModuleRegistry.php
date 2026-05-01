<?php

namespace LaravelModular\Module;

use Illuminate\Contracts\Foundation\Application;
use LaravelModular\Contracts\ModuleInterface;
use RuntimeException;

class ModuleRegistry
{
    protected array $modules = [];

    public function __construct(protected Application $app) {}

    public function register(ModuleInterface $module): void
    {
        $this->modules[$module->getName()] = $module;
    }

    public function get(string $name): ?ModuleInterface
    {
        return $this->modules[$name] ?? null;
    }

    public function all(): array
    {
        return $this->modules;
    }

    public function isEnabled(string $name): bool
    {
        return !in_array($name, config('modular.disabled', []));
    }

    /**
     * Call a service method from another module.
     * Usage: Module::call('User@UserService', 'findById', [1])
     */
    public function call(string $target, string $method, array $args = []): mixed
    {
        return $this->service($target)->{$method}(...$args);
    }

    /**
     * Resolve a service from another module.
     * Usage: Module::service('User@UserService')->findById(1)
     */
    public function service(string $target): object
    {
        [$moduleName, $serviceName] = $this->parseTarget($target);

        $module = $this->get($moduleName);
        if (!$module) {
            throw new RuntimeException("Module [{$moduleName}] not found or not loaded.");
        }

        if (!$module->exports($serviceName)) {
            throw new RuntimeException(
                "Service [{$serviceName}] is not exported by module [{$moduleName}]. " .
                "Add it to the exports() array in your module provider."
            );
        }

        $namespace = config('modular.namespace', 'App\\Modules');
        $candidates = [
            "{$namespace}\\{$moduleName}\\Services\\{$serviceName}",
            "{$namespace}\\{$moduleName}\\{$serviceName}",
        ];

        foreach ($candidates as $class) {
            if (class_exists($class)) {
                return $this->app->make($class);
            }
        }

        throw new RuntimeException("Class for [{$serviceName}] not found in module [{$moduleName}].");
    }

    protected function parseTarget(string $target): array
    {
        if (!str_contains($target, '@')) {
            throw new RuntimeException("Target must be in 'ModuleName@ServiceName' format. Got: [{$target}]");
        }
        return explode('@', $target, 2);
    }
}

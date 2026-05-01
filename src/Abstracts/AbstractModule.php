<?php

namespace LaravelModular\Abstracts;

use Illuminate\Support\ServiceProvider;
use LaravelModular\Contracts\ModuleInterface;
use LaravelModular\Module\ModuleRegistry;
use LaravelModular\Traits\RegistersBindings;
use Illuminate\Support\Facades\Route;

abstract class AbstractModule extends ServiceProvider implements ModuleInterface
{
    use RegistersBindings;

    /**
     * Services this module exports to other modules.
     * Only exported services can be accessed via Module::call()
     */
    protected array $exports = [];

    /**
     * Services to bind in the container (interface => implementation).
     */
    protected array $bindings = [];

    /**
     * Services to register as singletons.
     */
    protected array $singletons = [];

    /**
     * Middleware to register.
     */
    protected array $middleware = [];

    /**
     * Policies to register [Model::class => Policy::class]
     */
    protected array $policies = [];

    /**
     * Event listeners [Event::class => [Listener::class]]
     */
    protected array $listen = [];

    /**
     * Commands to register.
     */
    protected array $commands = [];

    public function getName(): string
    {
        // Derive module name from class: App\Modules\User\UserModuleProvider => User
        $parts = explode('\\', static::class);
        return $parts[count($parts) - 2] ?? 'Unknown';
    }

    public function exports(string $service): bool
    {
        return in_array($service, $this->exports);
    }

    public function getExports(): array
    {
        return $this->exports;
    }

    public function getProviders(): array
    {
        return [];
    }

    public function register(): void
    {
        // Register with the module registry
        $this->app->make(ModuleRegistry::class)->register($this);

        // Register container bindings
        $this->registerBindings($this->bindings);
        $this->registerSingletons($this->singletons);
    }

    public function boot(): void
    {
        $this->loadRoutes();
        $this->loadMigrations();
        $this->loadTranslations();
        $this->loadViews();
        $this->registerPolicies();
        $this->registerListeners();
        $this->registerMiddleware();

        if (!empty($this->commands) && $this->app->runningInConsole()) {
            $this->commands($this->commands);
        }
    }

    protected function modulePath(string $path = ''): string
    {
        $reflection = new \ReflectionClass(static::class);
        $dir = dirname($reflection->getFileName());
        return $path ? $dir . DIRECTORY_SEPARATOR . ltrim($path, '/\\') : $dir;
    }

    protected function loadRoutes(): void
    {
        $web = $this->modulePath('Routes/web.php');
        $api = $this->modulePath('Routes/api.php');

        if (file_exists($web)) {
            Route::middleware('web')->group($web);
        }

        if (file_exists($api)) {
            Route::prefix('api')
                ->middleware('api')
                ->group($api);
        }
    }

    protected function loadMigrations(): void
    {
        $path = $this->modulePath('Database/migrations');
        if (is_dir($path)) {
            $this->loadMigrationsFrom($path);
        }
    }

    protected function loadTranslations(): void
    {
        $path = $this->modulePath('Lang');
        if (is_dir($path)) {
            $this->loadTranslationsFrom($path, strtolower($this->getName()));
        }
    }

    protected function loadViews(): void
    {
        $path = $this->modulePath('Views');
        if (is_dir($path)) {
            $this->loadViewsFrom($path, strtolower($this->getName()));
        }
    }

    protected function registerPolicies(): void
    {
        foreach ($this->policies as $model => $policy) {
            \Illuminate\Support\Facades\Gate::policy($model, $policy);
        }
    }

    protected function registerListeners(): void
    {
        foreach ($this->listen as $event => $listeners) {
            foreach ((array) $listeners as $listener) {
                \Illuminate\Support\Facades\Event::listen($event, $listener);
            }
        }
    }

    protected function registerMiddleware(): void
    {
        $router = $this->app['router'];
        foreach ($this->middleware as $alias => $class) {
            $router->aliasMiddleware($alias, $class);
        }
    }
}

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

    /** Services this module exports to other modules. */
    protected array $exports = [];

    /** Interface => Implementation bindings. */
    protected array $bindings = [];

    /** Services to register as singletons. */
    protected array $singletons = [];

    /** Middleware aliases: ['alias' => MiddlewareClass::class] */
    protected array $middleware = [];

    /** Policies: [Model::class => Policy::class] */
    protected array $policies = [];

    /** Event listeners: [Event::class => [Listener::class]] */
    protected array $listen = [];

    /** Artisan commands to register. */
    protected array $commands = [];

    /** Observers: [Model::class => Observer::class] */
    protected array $observers = [];

    /** API route version prefix (e.g. 'v1'). Falls back to config. */
    protected ?string $apiVersion = null;

    public function getName(): string
    {
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
        $this->app->make(ModuleRegistry::class)->register($this);

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
        $this->registerObservers();

        if (!empty($this->commands) && $this->app->runningInConsole()) {
            $this->commands($this->commands);
        }
    }

    protected function modulePath(string $path = ''): string
    {
        $reflection = new \ReflectionClass(static::class);
        $dir        = dirname($reflection->getFileName());
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
            $prefix = 'api';

            if (config('modular.versioning.enabled', false)) {
                $version = $this->apiVersion ?? config('modular.versioning.default', 'v1');
                $prefix  = "api/{$version}";
            }

            Route::prefix($prefix)
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

    protected function registerObservers(): void
    {
        foreach ($this->observers as $model => $observer) {
            $model::observe($observer);
        }
    }
}

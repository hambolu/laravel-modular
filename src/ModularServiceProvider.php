<?php

namespace LaravelModular;

use Illuminate\Support\ServiceProvider;
use LaravelModular\Module\ModuleRegistry;
use LaravelModular\Module\ModuleLoader;
use LaravelModular\Macros\CollectionMacros;
use LaravelModular\Commands\SetupCommand;
use LaravelModular\Commands\MakeModuleCommand;
use LaravelModular\Commands\MakeControllerCommand;
use LaravelModular\Commands\MakeServiceCommand;
use LaravelModular\Commands\MakeRepositoryCommand;
use LaravelModular\Commands\MakeActionCommand;
use LaravelModular\Commands\MakeDtoCommand;
use LaravelModular\Commands\MakeEventCommand;
use LaravelModular\Commands\MakeListenerCommand;
use LaravelModular\Commands\MakeJobCommand;
use LaravelModular\Commands\MakePolicyCommand;
use LaravelModular\Commands\MakeMiddlewareCommand;
use LaravelModular\Commands\MakeResourceCommand;
use LaravelModular\Commands\MakeObserverCommand;
use LaravelModular\Commands\MakeNotificationCommand;
use LaravelModular\Commands\MakeRuleCommand;
use LaravelModular\Commands\MakeContractCommand;
use LaravelModular\Commands\ModuleListCommand;
use LaravelModular\Commands\ModuleEnableCommand;
use LaravelModular\Commands\ModuleDisableCommand;
use LaravelModular\Commands\ModuleInfoCommand;
use LaravelModular\Commands\ModuleDeleteCommand;
use LaravelModular\Commands\ModuleMigrateCommand;

class ModularServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/modular.php', 'modular');

        $this->app->singleton(ModuleRegistry::class, fn($app) => new ModuleRegistry($app));
        $this->app->singleton(ModuleLoader::class, fn($app) => new ModuleLoader($app, $app->make(ModuleRegistry::class)));
        $this->app->alias(ModuleRegistry::class, 'module');
        $this->app->alias(ModuleRegistry::class, 'module.registry');
    }

    public function boot(): void
    {
        $this->publishConfig();
        $this->registerCommands();
        $this->registerMacros();
        $this->loadModules();
        $this->registerHealthRoute();
    }

    protected function publishConfig(): void
    {
        $this->publishes([
            __DIR__.'/../config/modular.php' => config_path('modular.php'),
        ], 'modular-config');

        $this->publishes([
            __DIR__.'/../stubs' => base_path('stubs/modular'),
        ], 'modular-stubs');
    }

    protected function registerCommands(): void
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                SetupCommand::class,
                MakeModuleCommand::class,
                MakeControllerCommand::class,
                MakeServiceCommand::class,
                MakeRepositoryCommand::class,
                MakeActionCommand::class,
                MakeDtoCommand::class,
                MakeEventCommand::class,
                MakeListenerCommand::class,
                MakeJobCommand::class,
                MakePolicyCommand::class,
                MakeMiddlewareCommand::class,
                MakeResourceCommand::class,
                MakeObserverCommand::class,
                MakeNotificationCommand::class,
                MakeRuleCommand::class,
                MakeContractCommand::class,
                ModuleListCommand::class,
                ModuleEnableCommand::class,
                ModuleDisableCommand::class,
                ModuleInfoCommand::class,
                ModuleDeleteCommand::class,
                ModuleMigrateCommand::class,
            ]);
        }
    }

    protected function registerMacros(): void
    {
        CollectionMacros::register();
    }

    protected function loadModules(): void
    {
        $this->app->make(ModuleLoader::class)->loadAll();
    }

    protected function registerHealthRoute(): void
    {
        if (!config('modular.health.enabled', false)) {
            return;
        }

        $route      = config('modular.health.route', '/modular/health');
        $middleware = config('modular.health.middleware', ['api']);

        $this->app['router']
            ->middleware($middleware)
            ->get($route, function () {
                /** @var ModuleRegistry $registry */
                $registry = $this->app->make(ModuleRegistry::class);
                $modules  = collect($registry->all())->map(fn($m) => [
                    'name'    => $m->getName(),
                    'exports' => $m->getExports(),
                    'status'  => 'active',
                ])->values();

                return response()->json([
                    'status'  => 'ok',
                    'modules' => $modules,
                    'total'   => $modules->count(),
                ]);
            });
    }
}

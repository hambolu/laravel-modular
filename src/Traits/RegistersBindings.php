<?php

namespace LaravelModular\Traits;

trait RegistersBindings
{
    protected function registerBindings(array $bindings): void
    {
        foreach ($bindings as $abstract => $concrete) {
            $this->app->bind($abstract, $concrete);
        }
    }

    protected function registerSingletons(array $singletons): void
    {
        foreach ($singletons as $abstract => $concrete) {
            if (is_int($abstract)) {
                // Numeric key — just register the concrete as its own singleton
                $this->app->singleton($concrete);
            } else {
                $this->app->singleton($abstract, $concrete);
            }
        }
    }
}

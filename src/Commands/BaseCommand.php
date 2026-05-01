<?php

namespace LaravelModular\Commands;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use LaravelModular\Support\StubGenerator;

abstract class BaseCommand extends Command
{
    protected Filesystem $files;
    protected StubGenerator $generator;

    public function __construct()
    {
        parent::__construct();
        $this->files     = new Filesystem();
        $this->generator = new StubGenerator($this->files);
    }

    protected function modulesPath(): string
    {
        return config('modular.path', app_path('Modules'));
    }

    protected function moduleNamespace(): string
    {
        return config('modular.namespace', 'App\\Modules');
    }

    protected function modulePath(string $module, string $sub = ''): string
    {
        $base = $this->modulesPath() . DIRECTORY_SEPARATOR . $module;
        return $sub ? $base . DIRECTORY_SEPARATOR . $sub : $base;
    }

    protected function writeFile(string $path, string $contents): void
    {
        $dir = dirname($path);
        if (!$this->files->isDirectory($dir)) {
            $this->files->makeDirectory($dir, 0755, true);
        }
        $this->files->put($path, $contents);
    }

    protected function qualifyStub(string $name): string
    {
        return $this->generator->stubPath($name);
    }

    protected function success(string $message): void
    {
        $this->components->info($message);
    }

    protected function skip(string $message): void
    {
        $this->components->warn($message);
    }
}

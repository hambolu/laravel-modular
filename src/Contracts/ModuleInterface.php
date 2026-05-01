<?php

namespace LaravelModular\Contracts;

interface ModuleInterface
{
    public function getName(): string;
    public function exports(string $service): bool;
    public function getExports(): array;
    public function getProviders(): array;
}

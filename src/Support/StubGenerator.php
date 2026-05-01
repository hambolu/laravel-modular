<?php

namespace LaravelModular\Support;

use Illuminate\Filesystem\Filesystem;

class StubGenerator
{
    public function __construct(protected Filesystem $files) {}

    public function generate(string $stubPath, string $destination, array $replacements): bool
    {
        if (!$this->files->exists($stubPath)) {
            throw new \RuntimeException("Stub not found: {$stubPath}");
        }

        $contents = $this->files->get($stubPath);
        $contents = $this->replace($contents, $replacements);

        $dir = dirname($destination);
        if (!$this->files->isDirectory($dir)) {
            $this->files->makeDirectory($dir, 0755, true);
        }

        $this->files->put($destination, $contents);
        return true;
    }

    protected function replace(string $contents, array $replacements): string
    {
        foreach ($replacements as $key => $value) {
            $contents = str_replace(
                ['{{ ' . $key . ' }}', '{{' . $key . '}}', '{{' . $key . ' }}', '{{ ' . $key . '}}'],
                $value,
                $contents
            );
        }
        return $contents;
    }

    public function stubPath(string $name): string
    {
        // Check for published stubs first
        $published = base_path("stubs/modular/{$name}");
        if (file_exists($published)) {
            return $published;
        }

        return __DIR__ . "/../../stubs/{$name}";
    }
}

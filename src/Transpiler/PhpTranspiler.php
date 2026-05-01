<?php

namespace LaravelModular\Transpiler;

/**
 * EXPERIMENTAL: JS/TS-like PHP Transpiler
 *
 * Allows writing PHP without the $ variable prefix.
 * Files must use the .mod.php extension (configurable).
 *
 * Input  (.mod.php):
 *   name = 'John'
 *   result = service.call(name)
 *
 * Output (.php):
 *   $name = 'John';
 *   $result = $service->call($name);
 *
 * NOTE: This is opt-in and experimental. It transpiles before require.
 */
class PhpTranspiler
{
    protected array $keywords = [
        'if', 'else', 'elseif', 'for', 'foreach', 'while', 'do', 'switch',
        'case', 'break', 'continue', 'return', 'function', 'class', 'new',
        'echo', 'print', 'die', 'exit', 'null', 'true', 'false', 'and',
        'or', 'not', 'instanceof', 'array', 'list', 'match', 'throw',
        'try', 'catch', 'finally', 'use', 'namespace', 'public', 'private',
        'protected', 'static', 'abstract', 'interface', 'extends', 'implements',
        'readonly', 'enum', 'yield', 'fn',
    ];

    public function transpile(string $source): string
    {
        $lines  = explode("\n", $source);
        $result = [];

        foreach ($lines as $line) {
            $result[] = $this->transformLine($line);
        }

        return implode("\n", $result);
    }

    protected function transformLine(string $line): string
    {
        // Skip PHP open/close tags, comments, strings
        $trimmed = ltrim($line);
        if (str_starts_with($trimmed, '//') || str_starts_with($trimmed, '*') || str_starts_with($trimmed, '#')) {
            return $line;
        }

        // Replace dot method calls with arrow syntax: obj.method() => $obj->method()
        $line = preg_replace_callback(
            '/\b([a-z_][a-zA-Z0-9_]*)\.([a-zA-Z_][a-zA-Z0-9_]*)\(/',
            fn($m) => !in_array($m[1], $this->keywords) ? '$' . $m[1] . '->' . $m[2] . '(' : $m[0],
            $line
        );

        // Replace dot property access: obj.prop => $obj->prop
        $line = preg_replace_callback(
            '/\b([a-z_][a-zA-Z0-9_]*)\.([a-zA-Z_][a-zA-Z0-9_]*)\b(?!\()/',
            fn($m) => !in_array($m[1], $this->keywords) ? '$' . $m[1] . '->' . $m[2] : $m[0],
            $line
        );

        // Add $ to variable assignments: name = 'foo' => $name = 'foo'
        $line = preg_replace_callback(
            '/^(\s*)([a-z_][a-zA-Z0-9_]*)\s*=(?!=)/',
            function ($m) {
                if (in_array($m[2], $this->keywords)) return $m[0];
                return $m[1] . '$' . $m[2] . ' =';
            },
            $line
        );

        // Add $ to variable references in expressions (RHS of assignments, function args)
        // This is simplified — a full transpiler would use a proper AST parser
        $line = preg_replace_callback(
            '/(?<![\'"\$\->])(?<![a-zA-Z0-9_\\\\])\b([a-z_][a-zA-Z0-9_]*)\b(?!\s*\()(?!\s*[\'"])(?![a-zA-Z0-9_\'\"\(\\\\])/',
            function ($m) {
                if (in_array($m[1], $this->keywords)) return $m[0];
                if (preg_match('/^\d/', $m[1])) return $m[0];
                return '$' . $m[1];
            },
            $line
        );

        return $line;
    }

    /**
     * Transpile and require a .mod.php file
     */
    public function requireFile(string $path): mixed
    {
        $source      = file_get_contents($path);
        $transpiled  = $this->transpile($source);
        $tmpFile     = sys_get_temp_dir() . '/modular_' . md5($path) . '.php';

        file_put_contents($tmpFile, $transpiled);

        try {
            return require $tmpFile;
        } finally {
            @unlink($tmpFile);
        }
    }
}

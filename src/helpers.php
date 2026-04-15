<?php

use Illuminate\Filesystem\Filesystem;
use Symfony\Component\VarDumper\VarDumper;

if (! function_exists('dev_dump')) {
    function dev_dump(mixed ...$vars): void
    {
        if (! request()->boolean('dev')) {
            return;
        }

        foreach ($vars as $var) {
            VarDumper::dump($var);
        }
    }
}

if (! function_exists('dev_dd')) {
    function dev_dd(mixed ...$vars): void
    {
        if (! request()->boolean('dev')) {
            return;
        }

        dd(...$vars);
    }
}

if (! function_exists('dev_create_cursor_project_rule')) {
    /**
     * Erstellt (oder überschreibt) die Datei .cursor/rules/project.mdc im Projekt-Root aus dem Stub.
     */
    function dev_create_cursor_project_rule(): bool
    {
        $files = new Filesystem();

        $targetPath = base_path('.cursor/rules/project.mdc');
        $directory = \dirname($targetPath);

        if (! $files->isDirectory($directory)) {
            $files->makeDirectory($directory, 0755, true);
        }

        $stubPath = __DIR__.'/../stubs/project.mdc.stub';

        if ($files->exists($stubPath)) {
            $files->put($targetPath, $files->get($stubPath));
        } else {
            $files->put($targetPath, "// Cursor Project Rules\n");
        }

        return true;
    }
}


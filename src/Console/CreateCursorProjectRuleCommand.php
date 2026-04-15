<?php

namespace Componist\DeveloperTools\Console;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;

class CreateCursorProjectRuleCommand extends Command
{
    protected $signature = 'devtools:cursor-project-rule';

    protected $description = 'Erstellt die Datei .cursor/rules/project.mdc aus dem Stub.';

    public function handle(Filesystem $files): int
    {
        $targetPath = base_path('.cursor/rules/project.mdc');
        $directory = \dirname($targetPath);

        if (! $files->isDirectory($directory)) {
            $files->makeDirectory($directory, 0755, true);
        }

        if ($files->exists($targetPath)) {
            if (! $this->confirm('.cursor/rules/project.mdc existiert bereits. Überschreiben?', false)) {
                $this->info('Abgebrochen – bestehende Datei wurde nicht verändert.');

                return self::SUCCESS;
            }
        }

        $stubPath = __DIR__ . '/../../stubs/project.mdc.stub';

        if (! $files->exists($stubPath)) {
            $this->error('Stub-Datei für project.mdc wurde nicht gefunden.');

            return self::FAILURE;
        }

        $files->put($targetPath, $files->get($stubPath));

        $this->info('Die Datei .cursor/rules/project.mdc wurde erstellt.');

        return self::SUCCESS;
    }
}


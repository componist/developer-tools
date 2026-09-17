<?php

namespace Componist\DeveloperTools\Livewire\Jobs;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Componist\DeveloperTools\Infrastructure\AuthorizesDeveloperTools;
use Livewire\Component;

class Liste extends Component
{
    use AuthorizesDeveloperTools;

    public function render()
    {
        $jobClasses = [];

        $jobsPath = app_path('Jobs');
        $files = is_dir($jobsPath) ? File::allFiles($jobsPath) : [];

        foreach ($files as $file) {
            $path = $file->getRealPath();
            $contents = File::get($path);

            if (Str::contains($contents, 'ShouldQueue')) {
                // Extrahiere Namespace + Klassenname grob
                preg_match('/namespace (.*);/', $contents, $namespaceMatch);
                preg_match('/class (\w+)/', $contents, $classMatch);

                if ($namespaceMatch && $classMatch) {
                    $namespace = $namespaceMatch[1];
                    $class = $classMatch[1];
                    $jobClasses[] = [
                        'namespace' => $namespace.'\\'.$class,
                        'class' => $class,
                    ];
                }
            }
        }

        // dd($jobClasses);

        $content = $jobClasses;

        return view('developerTools::livewire.jobs.liste', compact('content'))->layout('developerTools::layouts.package');
    }

    public function runJob(array $job)
    {
        // $jobClass = 'App\\Jobs\\MyDynamicJob';
        $jobClass = $job['namespace'];

        dispatch(new $jobClass);
    }
}

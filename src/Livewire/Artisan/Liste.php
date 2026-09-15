<?php

namespace Componist\DeveloperTools\Livewire\Artisan;

use Illuminate\Support\Facades\Artisan;
use Componist\DeveloperTools\Infrastructure\AuthorizesDeveloperTools;
use Livewire\Component;

class Liste extends Component
{
    use AuthorizesDeveloperTools;

    public function render()
    {
        $commandListe = [];
        $commands = Artisan::all();

        foreach ($commands as $name => $command) {
            $group = explode(':', $name);

            $commandListe[] = [
                'group' => $group[0],
                'name' => 'php artisan '.$name,
                'description' => $command->getDescription(),
            ];
        }

        $commandListe = collect($commandListe)
            ->groupBy('group')
            ->toArray();

        $content = $commandListe;
        // dump($content);

        return view('developerTools::livewire.artisan.liste', compact('content'))->layout('developerTools::layouts.package');
    }
}

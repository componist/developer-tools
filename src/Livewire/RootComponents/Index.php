<?php

namespace Componist\DeveloperTools\Livewire\RootComponents;

use Livewire\Component;

class Index extends Component
{
    public string $path = '';

    public string $prefix = '';

    public array $iconListe = [];

    public function mount()
    {
        $this->path = base_path('resources/views/components/icon');

        // dev
        // $this->path = base_path('packages/heco/core/resources/views/components/icon');
        // $this->prefix = 'core::';

        if ($this->path) {
            if (file_exists($this->path) && is_dir($this->path)) {
                if (count(scandir($this->path)) > 3) {
                    $this->scanIconPath();
                }
            }
        }

    }

    public function render()
    {
        $content = $this->iconListe;

        // dump($this->iconListe);

        return view('developerTools::livewire.root-components.index', compact('content'))->layout('developerTools::layouts.package');
    }

    public function scanIconPath(): void
    {
        $this->iconListe = [];
        foreach (scandir($this->path) as $icon) {

            if ($icon != '..' && $icon != '.') {
                $icon = str_replace('.blade.php', '', $icon);

                if ($this->prefix) {
                    $this->iconListe[] = $this->prefix.'icon.'.$icon;
                } else {
                    $this->iconListe[] = 'icon.'.$icon;
                }

                // dump($icon);
            }
        }
    }
}

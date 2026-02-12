<?php

namespace Componist\DeveloperTools\Livewire\Setting;

use Illuminate\Support\Facades\File;
use Livewire\Component;

class EnvFile extends Component
{
    public string $path = '';

    public string $content = '';

    public function mount()
    {
        $this->path = base_path('.env');

        if (File::exists($this->path)) {
            $this->content = File::get($this->path);
        }
    }

    public function render()
    {
        return view('developerTools::livewire.setting.env-file')->layout('developerTools::layouts.package');
    }
}

<?php

declare(strict_types=1);

namespace Componist\DeveloperTools\Livewire\Setting;

use Componist\DeveloperTools\Infrastructure\AuthorizesDeveloperTools;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\File;
use Livewire\Component;

class EnvFile extends Component
{
    use AuthorizesDeveloperTools;

    public string $path = '';

    public string $content = '';

    public function mount(): void
    {
        $this->path = '.env';

        $full = base_path('.env');
        if (File::exists($full)) {
            $this->content = File::get($full);
        }
    }

    public function render(): View
    {
        return view('developerTools::livewire.setting.env-file')->layout('developerTools::layouts.package');
    }
}

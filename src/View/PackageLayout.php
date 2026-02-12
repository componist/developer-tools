<?php

namespace Componist\DeveloperTools\View;

use Illuminate\View\Component;
use Illuminate\View\View;

class PackageLayout extends Component
{
    /**
     * Get the view / contents that represents the component.
     */
    public function render(): View
    {
        return view('developerTools::layouts.package');
    }
}

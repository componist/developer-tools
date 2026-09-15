<?php

namespace Componist\DeveloperTools\Livewire\Search;

use Exception;
use Illuminate\View\View;
use Componist\DeveloperTools\Infrastructure\AuthorizesDeveloperTools;
use Livewire\Component;

class StringInFiles extends Component
{
    use AuthorizesDeveloperTools;

    public string $path = '';

    public ?string $search = null;

    public array $base_path = [];

    public array $founds = [];

    public function mount(): void
    {
        $this->path = base_path();

        $this->getFolderChilds($this->path);

    }

    private function getFolderChilds($path): void
    {
        foreach (scandir($path) as $value) {
            if ($value != '..' && $value != '.' && $value != '.vscode' && $value != 'bootstrap' && $value != 'node_modules' && $value != 'vendor' && $value != '.git' && $value != '.github' && $value != 'storage') {

                if (is_file($path.'/'.$value)) {

                    $mine = mime_content_type($path.'/'.$value);

                    if ($mine != 'image/gif' && $mine != 'image/jpeg' && $mine != 'image/png' && $mine != 'image/svg+xml') {
                        $this->base_path[] = [
                            'type' => $mine,
                            'path' => $path.'/'.$value,
                        ];
                    }

                } else {
                    $this->getFolderChilds($path.'/'.$value);
                    // $this->base_path[] = [
                    //     'type' => 'folder',
                    //     'path' => $path.'/'.$value,
                    //     'childs' => $this->getFolderChilds($path.'/'.$value),
                    // ];
                }

            }
        }
    }

    public function render(): View
    {
        return view('developerTools::livewire.search.string-in-files')->layout('developerTools::layouts.package');
    }

    public function searchInFiles(): void
    {
        if (! empty($this->search)) {
            $this->founds = [];
            foreach ($this->base_path as $value) {

                if ($value['type'] === 'text/plain' or $value['type'] === 'application/json' or $value['type'] === 'text/xml' or $value['type'] === 'text/x-php') {
                    if ($fileContent = $this->getFileContent($value['path'])) {
                        if (preg_match('/'.$this->search.'/', $fileContent)) {
                            $this->founds[] = [
                                'path' => $value['path'],
                                'content' => preg_replace('#'.preg_quote($this->search).'#i', '<span class="text-white bg-teal-500">\\0</span>', $fileContent),
                            ];
                        }
                    }
                }
            }
        }
    }

    /**
     * @param  mixed  $filepath
     */
    public function getFileContent($filepath): ?string
    {
        try {
            return file_get_contents($filepath);
        } catch (Exception $e) {
            // dd($e);
        }

        return null;
    }
}

<?php

namespace Componist\DeveloperTools\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class ClearStoragePublic extends Command
{
    protected $signature = 'componist:storage-public-clear';

    protected $description = 'Löscht alle Dateien und Ordner im storage/app/public-Verzeichnis rekursiv';

    public function handle()
    {
        $path = storage_path('app/public');

        $this->info("Lösche Inhalte aus: $path");

        foreach (File::directories($path) as $dir) {
            File::deleteDirectory($dir);
        }

        foreach (File::files($path) as $file) {
            File::delete($file);
        }

        $this->info('storage/app/public wurde erfolgreich geleert.');
    }
}
